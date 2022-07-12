<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_StepThruDashboard extends CRM_Core_Page {

  public function run() {
    $rows = [
      'inv' => [
        'label' => E::ts('Invoices'),
        'statistics' => CRM_Fpptaqb_Utils_Invoice::getStepthruStatistics(),
      ],
      'pmt' => [
        'label' => E::ts('Payments'),
        'statistics' => CRM_Fpptaqb_Utils_Payment::getStepthruStatistics(),
      ],
    ];
    $this->assign('rows', $rows);
    $this->assign('isMock', Civi::settings()->get('fpptaqb_minimum_date'));
    $this->assign('fpptaqb_use_sync_mock', Civi::settings()->get('fpptaqb_use_sync_mock'));
    $this->assign('fpptaqb_sync_wait_days', Civi::settings()->get('fpptaqb_sync_wait_days'));
    $this->assign('is_admin', CRM_Core_Permission::check('administer CiviCRM'));

    parent::run();
  }

}
