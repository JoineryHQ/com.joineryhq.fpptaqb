<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_SyncStatus extends CRM_Core_Page {

  /**
   * 
   * @throws Exception@throws Exception if the relevant contribution has been placed on hold.
   */
  public function run() {
    $id = CRM_Utils_Request::retrieveValue('id', 'Int');
    $this->assign('id', $id);
    CRM_Core_Session::singleton()->pushUserContext(CRM_Utils_System::url('civicrm/fpptaqb/syncstatus', "reset=1&id=$id", NULL, NULL, NULL, NULL, TRUE));

    // Error handling:
    // This page is likely to run as a pop-up, which will not properly display
    // civicrm fatal errors;
    // There are several legitimate reasons (typically lack of configuration)
    // that exceptions may be thrown in building this page, and we need to communicate
    // those to the user.
    // Therefore, we wrap the whole thing in a try/catch and display any exception
    // messages to the user.
    //
    try {
      // Get all paymentIds for payments made on this contribution.
      $financialTrxnIds = CRM_Fpptaqb_Utils_Invoice::getPaymentFinancialTrxnIds($id);
      // Create rows for a table of payments, if any.
      $paymentRows = [];

      if (!empty($financialTrxnIds)) {
        foreach ($financialTrxnIds as $financialTrxnId) {
          $financialTrxn = _fpptaqb_civicrmapi('FinancialTrxn', 'getSingle', [
            'id' => $financialTrxnId,
          ]);
          $paymentRow = $financialTrxn;

          // Note whether this payment has been synced.
          $trxnPaymentGet = _fpptaqb_civicrmapi('FpptaquickbooksTrxnPayment', 'get', [
            'sequential' => 1,
            'financial_trxn_id' => $financialTrxnId,
            'quickbooks_id' => ['IS NOT NULL' => 1],
          ]);
          $paymentRow['qbPmtId'] = ($trxnPaymentGet['values'][0]['quickbooks_id'] ?? NULL);
          $paymentRows[] = $paymentRow;
        }
      }
      $this->assign('paymentRows', $paymentRows);

      // Get the contribution sync record. We'll process differently depending
      // on the existence of this record.
      $contributionInvoiceGet = _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'get', [
        'sequential' => 1,
        'contribution_id' => $id,
      ]);

      if ($contributionInvoiceGet['count']) {
        if (is_null($contributionInvoiceGet['values'][0]['quickbooks_id'])) {
          // FIXME: thrown a bare exception here is pretty sloppy; it works because we're in a try/catch, so this prints an error for the user.
          throw new Exception(E::ts('This contribution has been held from sync. You may wish to review held invoices.'));
        }

        // Get some basic identifying info which we'll display for easy reference.
        $contribution = _fpptaqb_civicrmapi('Contribution', 'getSingle', [
          'id' => $id,
        ]);
        $this->assign('contribution', $contribution);
        $this->assign('qbInvId', $contributionInvoiceGet['values'][0]['quickbooks_id']);
        $this->assign('isMock', $contributionInvoiceGet['values'][0]['is_mock']);
      }
      else {
        // Get the 'load' text for this contribution
        // FIXME: because the 'load' apis use smarty, they will assign a lot of
        // variables to the page that aren't used in our template.
        $invLoad = _fpptaqb_civicrmapi('FpptaqbStepthruInvoice', 'load', ['id' => $id]);
        $this->assign('invLoadText', $invLoad['values']['text']);
        // Get the 'load' text for all of those payments.
        $pmtLoadTexts = [];
        foreach ($financialTrxnIds as $paymentTrxnId) {
          $pmtLoad = _fpptaqb_civicrmapi('FpptaqbStepthruPayment', 'load', [
            'id' => $paymentTrxnId,
          ]);
          $pmtLoadTexts[] = $pmtLoad['values']['text'];
        }
        $this->assign('pmtLoadTexts', $pmtLoadTexts);
      }
    }
    catch (Exception $e) {
      $this->assign('apiError', $e->getMessage());
    }

    parent::run();
  }

}
