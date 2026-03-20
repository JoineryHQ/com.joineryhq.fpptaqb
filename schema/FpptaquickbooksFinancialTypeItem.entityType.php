<?php
use CRM_Fpptaqb_ExtensionUtil as E;

return [
  'name' => 'FpptaquickbooksFinancialTypeItem',
  'table' => 'civicrm_fpptaquickbooks_financialtype_item',
  'class' => 'CRM_Fpptaqb_DAO_FpptaquickbooksFinancialTypeItem',
  'getInfo' => fn() => [
    'title' => E::ts('Fpptaquickbooks Financial Type Item'),
    'title_plural' => E::ts('Fpptaquickbooks Financial Type Items'),
    'description' => E::ts('Link civicrm financial type to quickbooks item/product'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_civicrm_fpptaquickbooks_financialtype_item_financial_type_id' => [
      'fields' => [
        'financial_type_id' => TRUE,
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
      'description' => E::ts('Unique FpptaquickbooksFinancialTypeItem ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'financial_type_id' => [
      'title' => E::ts('Financial Type ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Financial Type'),
      'entity_reference' => [
        'entity' => 'FinancialType',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'quickbooks_id' => [
      'title' => E::ts('Quickbooks ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => E::ts('Quickbooks item ID'),
    ],
  ],
];
