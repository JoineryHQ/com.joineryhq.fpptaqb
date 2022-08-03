<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_ItemAction extends CRM_Core_Page {

  public function run() {
    $type = CRM_Utils_Request::retrieveValue('type', 'String');
    $id = CRM_Utils_Request::retrieveValue('id', 'Int');
    $itemaction = CRM_Utils_Request::retrieveValue('itemaction', 'String');
    switch ($type) {
      case 'inv':
        switch ($itemaction) {
          case 'load':
            CRM_Utils_System::setTitle(E::ts('Load Invoice'));
            try {
              $invoiceLoad = _fpptaqb_civicrmapi('FpptaqbStepthruInvoice', 'load', ['id' => $id]);
              $content = ($invoiceLoad['values']['text'] ?? NULL);
              $this->assign('content', $content);
            }
            catch (API_Exception | CiviCRM_API3_Exception $e) {
              $this->assign('apiError', $e->getMessage());
            }
            break;
          case 'unhold':
            $contributionInvId = _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'getValue', [
              'contribution_id' => $id,
              'quickbooks_id' => ['IS NULL' => 1],
              'return' => 'id'
            ]);
            _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'delete', [
              'id' => $contributionInvId,
            ]);
            $msg = E::ts('Contribution %1 has been un-held.', [1 => $id]);
            CRM_Core_Session::setStatus($msg, 'Success', 'success');
            CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/fpptaqb/helditems/inv', NULL, NULL, NULL, NULL, NULL, TRUE));
            break;
          case 'unsync':
            // Get the contributionInvoice, if any.
            $contributionInvGet = _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'get', [
              'sequential' => 1,
              'contribution_id' => $id,
            ]);
              
            if ($contributionInvGet['count'] != 1) {
              // If there is no sync record, bounce with an error.
              $msg = E::ts('No action taken, because this contribution (id=%1) has not yet been synced.', [
                '%1' => $id
              ]);
              CRM_Core_Error::statusBounce($msg);              
            }

            $contributionInv = $contributionInvGet['values'][0];

            if ($contributionInv['is_mock'] != 1) {
              // We can only un-sync mock syncs. If this is not a mock sync, bounce with an error.
              $msg = E::ts('This contribution (id=%1) was synced to a live QuickBooks account. The sync cannot be undone.', [
                '%1' => $id
              ]);
              CRM_Core_Error::statusBounce($msg);              
            }
            
            // Get all paymentIds for payments made on this contribution.
            $financialTrxnIds = CRM_Fpptaqb_Utils_Invoice::getPaymentFinancialTrxnIds($id);
            // Shorthand array for all trxnPayment sync record ids.
            $trxnPaymentIds = [];
            if (!empty($financialTrxnIds)) {
              // Get the trxnPaymnt sync records, if any.
              $trxnPaymentGet = _fpptaqb_civicrmapi('FpptaquickbooksTrxnPayment', 'get', [
                'sequential' => 1,
                'financial_trxn_id' => ['IN' => $financialTrxnIds],
              ]);
              foreach ($trxnPaymentGet['values'] as $trxnPaymentValue) {
                if ($trxnPaymentValue['is_mock'] != 1) {
                  // We can only un-sync mock syncs if all the synced payments were also mocks. 
                  // If this is not a mock sync payment, bounce with an error.
                  $msg = E::ts('This contribution (id=%1) has payments which were synced to a live QuickBooks account. The sync cannot be undone.', [
                    '%1' => $id
                  ]);
                  CRM_Core_Error::statusBounce($msg);              
                }
                $trxnPaymentIds[] = $trxnPaymentValue['id'];
              }
            }
            // If we're here, we know that all sync records are mocks, and we know
            // the ids of those records. Delete them via api: payment syncs first, then
            // the contribution sync.
            foreach ($trxnPaymentIds as $trxnPaymentId) {
              _fpptaqb_civicrmapi('FpptaquickbooksTrxnPayment', 'delete', ['id' => $trxnPaymentId]);
            }
            _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'delete', [
              'id' => $contributionInv['id'],
            ]);

            // Inform the user of un-sync success.
            $msg = E::ts('Contribution %1 has been un-synced.', [1 => $id]);
            CRM_Core_Session::setStatus($msg, 'Success', 'success');            
            CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/fpptaqb/syncstatus', "reset=1&id=$id", NULL, NULL, NULL, NULL, TRUE));
            break;
            
          default:
            CRM_Core_Error::statusBounce('Invalid action '. $itemaction .'for type "inv"; must be "load", "unhold", or "unsync".');
        }
        break;
      case 'pmt':
        switch ($itemaction) {
          case 'load':
            CRM_Utils_System::setTitle(E::ts('Load Payment'));
            try {
              $paymentLoad = _fpptaqb_civicrmapi('FpptaqbStepthruPayment', 'load', ['id' => $id]);
              $content = ($paymentLoad['values']['text'] ?? NULL);
              $this->assign('content', $content);
            }
            catch (API_Exception | CiviCRM_API3_Exception $e) {
              $this->assign('apiError', $e->getMessage());
            }
            break;
          case 'unhold':
            $trxnPaymentId = _fpptaqb_civicrmapi('FpptaquickbooksTrxnPayment', 'getValue', [
              'financial_trxn_id' => $id,
              'quickbooks_id' => ['IS NULL' => 1],
              'return' => 'id'
            ]);
            _fpptaqb_civicrmapi('FpptaquickbooksTrxnPayment', 'delete', [
              'id' => $trxnPaymentId,
            ]);
            $msg = E::ts('Payment %1 has been un-held.', [1 => $id]);
            CRM_Core_Session::setStatus($msg, 'Success', 'success');
            CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/fpptaqb/helditems/pmt', NULL, NULL, NULL, NULL, NULL, TRUE));
            break;
          default:
            CRM_Core_Error::statusBounce('Invalid action for type "pmt"; must be "load" or "unhold"');
        }
        break;
      default:
        CRM_Core_Error::statusBounce('Invalid type; must be "inv" or "pmt"');
    }

    parent::run();
  }

}
