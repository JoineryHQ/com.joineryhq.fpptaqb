<?php
use CRM_Fpptaqb_ExtensionUtil as E;

return [
  'name' => 'FpptaquickbooksContactCustomer',
  'table' => 'civicrm_fpptaquickbooks_contact_customer',
  'class' => 'CRM_Fpptaqb_DAO_FpptaquickbooksContactCustomer',
  'getInfo' => fn() => [
    'title' => E::ts('Fpptaquickbooks Contact Customer'),
    'title_plural' => E::ts('Fpptaquickbooks Contact Customers'),
    'description' => E::ts('Link civicrm contact to quickbooks customer'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_fpptaquickbooks_contact_customer_contact_id' => [
      'fields' => [
        'contact_id' => TRUE,
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
      'description' => E::ts('Unique FpptaquickbooksContactCustomer ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'quickbooks_id' => [
      'title' => E::ts('Quickbooks ID'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Quickbooks customer ID'),
    ],
  ],
];
