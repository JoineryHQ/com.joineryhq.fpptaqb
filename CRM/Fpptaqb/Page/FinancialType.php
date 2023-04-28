<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_FinancialType extends CRM_Core_Page {
  /**
   * crm.livePage.js should be added to the page.
   * @var bool
   */
  var $useLivePageJS = TRUE;
  
  public function run() {
    $url = CRM_Utils_System::url('civicrm/admin/fpptaqb/settings', "reset=1");
    $breadCrumb = [['title' => ts('FPPTA QuickBooks Sync: Settings'), 'url' => $url]];
    CRM_Utils_System::appendBreadCrumb($breadCrumb);

    // Build one row per financial type, with linked QB Item data.
    $financialTypeGet = _fpptaqb_civicrmapi('FinancialType', 'get', [
      'sequential' => 1,
      'api.FpptaquickbooksFinancialTypeItem.get' => [],
      'options' => ['limit' => 0, 'sort' => "name"],
    ]);
    $rows = [];
    foreach ($financialTypeGet['values'] as $financialTypeValue) {
      try {
        $qbItemDetails = CRM_Fpptaqb_Utils_Quickbooks::getItemDetails($financialTypeValue['id']);
      }
      catch (CRM_Fpptaqb_Exception $e) {
        CRM_Core_Session::setStatus('Error fetching QuickBooks item. QuickBooks error: ' . $e->getMessage(), E::ts('Error'), 'no-popup');
        break;
      }
      $rows[] = [
        'id' => $financialTypeValue['id'],
        'name' => $financialTypeValue['name'],
        'qbItemId' => ($qbItemDetails['Id'] ?? NULL),
        'qbItemName' => ($qbItemDetails['Name'] ?? NULL),
      ];
    }
    $this->assign('rows', $rows);

    CRM_Fpptaqb_Util::assignSettingsLocalNavigationItems($this);

    parent::run();
  }

}
