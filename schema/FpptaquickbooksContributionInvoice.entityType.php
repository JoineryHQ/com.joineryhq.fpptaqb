<?php
use CRM_Fpptaqb_ExtensionUtil as E;

return [
  'name' => 'FpptaquickbooksContributionInvoice',
  'table' => 'civicrm_fpptaquickbooks_contribution_invoice',
  'class' => 'CRM_Fpptaqb_DAO_FpptaquickbooksContributionInvoice',
  'getInfo' => fn() => [
    'title' => E::ts('Fpptaquickbooks Contribution Invoice'),
    'title_plural' => E::ts('Fpptaquickbooks Contribution Invoices'),
    'description' => E::ts('Link civicrm contributions to quickbooks invoices'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_fpptaquickbooks_contribution_invoice_contribution_id' => [
      'fields' => [
        'contribution_id' => TRUE,
      ],
      'unique' => TRUE,
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FpptaquickbooksContributionInvoice ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'contribution_id' => [
      'title' => E::ts('Contribution ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contribution'),
      'entity_reference' => [
        'entity' => 'Contribution',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'quickbooks_id' => [
      'title' => E::ts('Quickbooks ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => E::ts('Quickbooks invoice ID'),
    ],
    'is_mock' => [
      'title' => E::ts('Is this a mock sync?'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'default' => FALSE,
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
  ],
];
