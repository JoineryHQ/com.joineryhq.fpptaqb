<?php
use CRM_Fpptaqb_ExtensionUtil as E;

return [
  'name' => 'FpptaquickbooksTrxnCreditmemo',
  'table' => 'civicrm_fpptaquickbooks_trxn_creditmemo',
  'class' => 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnCreditmemo',
  'getInfo' => fn() => [
    'title' => E::ts('Fpptaquickbooks Trxn Creditmemo'),
    'title_plural' => E::ts('Fpptaquickbooks Trxn Creditmemos'),
    'description' => E::ts('Credit memos for sync with QuickBooks, per refunds in CiviCRM'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_fpptaquickbooks_trxn_payment_financial_trxn_id' => [
      'fields' => [
        'financial_trxn_id' => TRUE,
      ],
      'unique' => TRUE,
    ],
    'UI_quickbooks_credit_memo_doc_number' => [
      'fields' => [
        'quickbooks_doc_number' => TRUE,
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
      'description' => E::ts('Unique FpptaquickbooksTrxnCreditmemo ID'),
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
    'quickbooks_doc_number' => [
      'title' => E::ts('Credit Memo No.'),
      'sql_type' => 'varchar(21)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Unique Credit Memo number for QuickBooks'),
    ],
    'quickbooks_customer_memo' => [
      'title' => E::ts('Message displayed on credit memo'),
      'sql_type' => 'varchar(1000)',
      'input_type' => 'Text',
      'description' => E::ts('Message or comment on QuickBooks credit memo'),
    ],
    'quickbooks_id' => [
      'title' => E::ts('Quickbooks ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => E::ts('Quickbooks credit memo trxn ID (0=pending sync; null=held)'),
      'default' => 0,
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
