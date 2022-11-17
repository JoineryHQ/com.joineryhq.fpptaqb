<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_HeldItemsCm extends CRM_Core_Page {

  /**
   * 
   * @throws CRM_Fpptaqb_Exception if any such exception is caught, appending 
   *    an explanation to the error message.
   */
  public function run() {
    $heldCreditmemoIds = CRM_Fpptaqb_Utils_Creditmemo::getHeldIds();
    
    $rows = [];
    foreach ($heldCreditmemoIds as $heldCreditmemoId) {
      try {
        $rows[] = CRM_Fpptaqb_Utils_Creditmemo::getHeldItem($heldCreditmemoId);
      }
      catch (CRM_Fpptaqb_Exception $e) {
        $caughtMessage = $e->getMessage();
        $thrownMessage = E::ts('Error in processing credit memo id=%1: ', ['%1' => $heldCreditmemoId]) . $caughtMessage;
        throw new CRM_Fpptaqb_Exception($thrownMessage);
      }
    }
    $this->assign('rows', $rows);

    parent::run();
  }

}
