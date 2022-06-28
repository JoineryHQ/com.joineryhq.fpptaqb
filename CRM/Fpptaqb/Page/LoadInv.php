<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_LoadInv extends CRM_Core_Page {

  public function run() {
    $id = CRM_Utils_Request::retrieve('id', 'Int');
    $contribution = CRM_Fpptaqb_Utils_Invoice::getReadyToSync($id);

    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('contribution', $contribution);

    parent::run();
  }

}
