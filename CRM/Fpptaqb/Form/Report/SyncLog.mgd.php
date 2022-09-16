<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_Fpptaqb_Form_Report_SyncLog',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'QuickBooks Sync Log',
      'description' => 'Report on various actions performed during sync of invoices and payments to QuickBooks',
      'class_name' => 'CRM_Fpptaqb_Form_Report_SyncLog',
      'report_url' => 'fpptaqb/synclog',
      'component' => 'CiviContribute',
    ],
  ],
];
