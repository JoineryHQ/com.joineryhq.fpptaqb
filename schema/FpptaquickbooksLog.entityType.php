<?php
use CRM_Fpptaqb_ExtensionUtil as E;

return [
  'name' => 'FpptaquickbooksLog',
  'table' => 'civicrm_fpptaquickbooks_log',
  'class' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
  'getInfo' => fn() => [
    'title' => E::ts('Fpptaquickbooks Log'),
    'title_plural' => E::ts('Fpptaquickbooks Logs'),
    'description' => E::ts('Log relevant api calls for fpptaqb'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FpptaquickbooksLog ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'created' => [
      'title' => E::ts('Created Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'description' => E::ts('When was the log entry created.'),
    ],
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('Contact who created this log entry; FK to civicrm_contact'),
      'input_attrs' => [
        'label' => E::ts('Contact'),
      ],
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'unique_request_id' => [
      'title' => E::ts('Unique Request ID'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'description' => E::ts('Unique identifier for a single php invocation.'),
    ],
    'sync_session_id' => [
      'title' => E::ts('Unique ID per sync session'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'description' => E::ts('Unique ID per sync session, e.g. Step-thru sync page load or Scheduled Job run.'),
    ],
    'entity_id_param' => [
      'title' => E::ts('Entity ID Param'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'description' => E::ts('Name of api parameter identifying the relevant entity.'),
    ],
    'entity_id' => [
      'title' => E::ts('Entity ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => E::ts('Foreign key to the referenced item.'),
    ],
    'api_entity' => [
      'title' => E::ts('API entity'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'description' => E::ts('API entity for the api call which triggered this log entry'),
    ],
    'api_action' => [
      'title' => E::ts('API action'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'description' => E::ts('API action for the api call which triggered this log entry'),
    ],
    'api_params' => [
      'title' => E::ts('API parameters'),
      'sql_type' => 'varchar(2550)',
      'input_type' => 'Text',
      'description' => E::ts('API parameters, as JSON, for the api call which triggered this log entry'),
    ],
    'api_output' => [
      'title' => E::ts('API output'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'description' => E::ts('API call ouptut, as JSON, for the api call which triggered this log entry'),
      'input_attrs' => [
        'rows' => 4,
        'cols' => 60,
      ],
    ],
    'api_output_text' => [
      'title' => E::ts('Text from API output'),
      'sql_type' => 'varchar(2550)',
      'input_type' => 'Text',
      'description' => E::ts('Text or error message extracted from API call output'),
    ],
    'api_output_error_code' => [
      'title' => E::ts('Error code from API output'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'description' => E::ts('Error code, if any, extracted from API call output'),
    ],
    'reason' => [
      'title' => E::ts('Reason'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Reason for this action, as given by the API caller'),
    ],
  ],
];
