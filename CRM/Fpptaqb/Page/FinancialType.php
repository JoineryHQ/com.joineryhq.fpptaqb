<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_FinancialType extends CRM_Core_Page {
  /**
   * crm.livePage.js should be added to the page.
   * @var bool
   */
  var $useLivePageJS = TRUE;
  
  public function run() {
    
    // Build one row per financial type, with linked QB Item data.
    $financialTypeGet = _fpptaqb_civicrmapi('FinancialType', 'get', [
      'sequential' => 1,
      'api.FpptaquickbooksFinancialTypeItem.get' => [],
      'options' => ['limit' => 0, 'sort' => "name"],
    ]);
    $rows = [];
    foreach ($financialTypeGet['values'] as $financialTypeValue) {
      $qbItemDetails = CRM_Fpptaqb_Utils_Quickbooks::getItemDetails($financialTypeValue['id']);
      $rows[] = [
        'id' => $financialTypeValue['id'],
        'name' => $financialTypeValue['name'],
        'qbItemId' => $qbItemDetails['Id'],
        'qbItemName' => $qbItemDetails['Name'],
      ];
    }
    $this->assign('rows', $rows);

    parent::run();
  }

}
