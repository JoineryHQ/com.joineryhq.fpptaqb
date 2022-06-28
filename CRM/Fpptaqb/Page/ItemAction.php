<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_ItemAction extends CRM_Core_Page {

  public function run() {
    $type = CRM_Utils_Request::retrieve('type', 'String');
    $id = CRM_Utils_Request::retrieve('id', 'Int');
    $action = CRM_Utils_Request::retrieve('action', 'String');
    switch ($type) {
      case 'inv':
        switch ($action) {
          case 'load':
            $contribution = CRM_Fpptaqb_Utils_Invoice::getReadyToSync($id);
            $smarty = CRM_Core_Smarty::singleton();
            $smarty->assign('contribution', $contribution);
            CRM_Utils_System::setTitle(E::ts('Load Invoice'));
            break;
          default:
            CRM_Core_Error::statusBounce('Invalid action for type "inv"; must be "load" or "unhold"');
        }
        break;
      case 'pmt':
        break;
      default:
        CRM_Core_Error::statusBounce('Invalid type; must be "inv" or "pmt"');
    }

    parent::run();
  }

}
