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
            $contribution = CRM_Fpptaqb_Utils_Invoice::getReadyToSync($id);
            $smarty = CRM_Core_Smarty::singleton();
            $smarty->assign('contribution', $contribution);
            CRM_Utils_System::setTitle(E::ts('Load Invoice'));
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
            CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/fpptaqb/helditems/inv'));
            break;
          default:
            CRM_Core_Error::statusBounce('Invalid action for type "inv"; must be "load" or "unhold"');
        }
        break;
      case 'pmt':
        switch ($itemaction) {
          case 'load':
            $payment = CRM_Fpptaqb_Utils_Payment::getReadyToSync($id);
            $smarty = CRM_Core_Smarty::singleton();
            $smarty->assign('payment', $payment);
            CRM_Utils_System::setTitle(E::ts('Load Payment'));
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
            CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/fpptaqb/helditems/pmt'));
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
