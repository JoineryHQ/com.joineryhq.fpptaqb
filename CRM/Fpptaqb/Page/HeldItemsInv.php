<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_HeldItemsInv extends CRM_Core_Page {

  public function run() {
    $heldContributionIds = CRM_Fpptaqb_Utils_Invoice::getHeldIds();
    
    $rows = [];
    foreach ($heldContributionIds as $heldContributionId) {
      try {
        $rows[] = CRM_Fpptaqb_Utils_Invoice::getHeldItem($heldContributionId);
      }
      catch (CRM_Fpptaqb_Exception $e) {
        $caughtMessage = $e->getMessage();
        $thrownMessage = E::ts('Error in processing contriubution id=%1: ', ['%1' => $heldContributionId]) . $caughtMessage;
        throw new CRM_Fpptaqb_Exception($thrownMessage);
      }
    }
    
    $this->assign('rows', $rows);

    parent::run();
  }

}
