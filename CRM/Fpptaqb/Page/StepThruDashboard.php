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
    // Example: Assign a variable for use in a template
    $this->assign('rows', $rows);

    parent::run();
  }

}
