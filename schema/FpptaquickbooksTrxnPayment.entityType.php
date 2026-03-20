<?php
use CRM_Fpptaqb_ExtensionUtil as E;

return [
  'name' => 'FpptaquickbooksTrxnPayment',
  'table' => 'civicrm_fpptaquickbooks_trxn_payment',
  'class' => 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment',
  'getInfo' => fn() => [
    'title' => E::ts('Fpptaquickbooks Trxn Payment'),
    'title_plural' => E::ts('Fpptaquickbooks Trxn Payments'),
    'description' => E::ts('Link civicrm financial transaction payments to quickbooks payments'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_fpptaquickbooks_trxn_payment_financial_trxn_id' => [
      'fields' => [
        'financial_trxn_id' => TRUE,
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
      'description' => E::ts('Unique FpptaquickbooksTrxnPayment ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'financial_trxn_id' => [
      'title' => E::ts('Financial Trxn ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to civicrm_financial_trxn'),
      'entity_reference' => [
        'entity' => 'FinancialTrxn',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'quickbooks_id' => [
      'title' => E::ts('Quickbooks ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => E::ts('Quickbooks payment ID'),
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
