<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_HeldItemsPmt extends CRM_Core_Page {

  public function run() {
    $heldPaymentIds = CRM_Fpptaqb_Utils_Payment::getHeldIds();;
    
    $rows = [];
    foreach ($heldPaymentIds as $heldPaymentId) {
      $rows[] = CRM_Fpptaqb_Utils_Payment::getHeldItem($heldPaymentId);
    }
    $this->assign('rows', $rows);

    parent::run();
  }

}
