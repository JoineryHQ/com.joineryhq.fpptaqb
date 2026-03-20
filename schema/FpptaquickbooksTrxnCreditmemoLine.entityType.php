<?php
use CRM_Fpptaqb_ExtensionUtil as E;

return [
  'name' => 'FpptaquickbooksTrxnCreditmemoLine',
  'table' => 'civicrm_fpptaquickbooks_trxn_creditmemo_line',
  'class' => 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnCreditmemoLine',
  'getInfo' => fn() => [
    'title' => E::ts('Fpptaquickbooks Trxn Creditmemo Line'),
    'title_plural' => E::ts('Fpptaquickbooks Trxn Creditmemo Lines'),
    'description' => E::ts('Allotment of credit memos total into separate financial types'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FpptaquickbooksTrxnCreditmemoLine ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'creditmemo_id' => [
      'title' => E::ts('Creditmemo ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Credit Memo'),
      'entity_reference' => [
        'entity' => 'FpptaquickbooksTrxnCreditmemo',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'ft_id' => [
      'title' => E::ts('Ft ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Financial Type'),
      'entity_reference' => [
        'entity' => 'FinancialType',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'total_amount' => [
      'title' => E::ts('Total Amount'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => NULL,
      'required' => TRUE,
      'description' => E::ts('Total amount of to be applied to a line item for the given financial type.'),
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
  ],
];
