<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'Cron:FpptaqbBatchSyncPayments.Process',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call FpptaqbBatchSyncPayments.Process API',
      'description' => 'Call FpptaqbBatchSyncPayments.Process API',
      'run_frequency' => 'Hourly',
      'api_entity' => 'FpptaqbBatchSyncPayments',
      'api_action' => 'Process',
      'parameters' => '',
    ],
  ],
];
