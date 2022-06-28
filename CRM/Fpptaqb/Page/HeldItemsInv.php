<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_HeldItemsInv extends CRM_Core_Page {

  public function run() {
    $heldContributionIds = CRM_Fpptaqb_Utils_Invoice::getHeldIds();
    
    $rows = [];
    foreach ($heldContributionIds as $heldContributionId) {
      $rows[] = CRM_Fpptaqb_Utils_Invoice::getReadyToSync($heldContributionId);
    }
    
    $this->assign('rows', $rows);

    parent::run();
  }

}
