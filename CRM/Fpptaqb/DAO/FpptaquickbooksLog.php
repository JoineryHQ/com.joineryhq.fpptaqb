<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from com.joineryhq.fpptaqb/xml/schema/CRM/Fpptaqb/FpptaquickbooksLog.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:7242044167179707ae4f1114949019fd)
 */
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Database access object for the FpptaquickbooksLog entity.
 */
class CRM_Fpptaqb_DAO_FpptaquickbooksLog extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_fpptaquickbooks_log';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique FpptaquickbooksLog ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * When was the log entry created.
   *
   * @var string|null
   *   (SQL type: datetime)
   *   Note that values will be retrieved from the database as a string.
   */
  public $created;

  /**
   * Contact who created this log entry; FK to civicrm_contact
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $contact_id;

  /**
   * Unique identifier for a single php invocation.
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $unique_request_id;

  /**
   * Unique ID per sync session, e.g. Step-thru sync page load or Scheduled Job run.
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $sync_session_id;

  /**
   * Name of api parameter identifying the relevant entity.
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $entity_id_param;

  /**
   * Foreign key to the referenced item.
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $entity_id;

  /**
   * API entity for the api call which triggered this log entry
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $api_entity;

  /**
   * API action for the api call which triggered this log entry
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $api_action;

  /**
   * API parameters, as JSON, for the api call which triggered this log entry
   *
   * @var string|null
   *   (SQL type: varchar(2550))
   *   Note that values will be retrieved from the database as a string.
   */
  public $api_params;

  /**
   * API call ouptut, as JSON, for the api call which triggered this log entry
   *
   * @var string|null
   *   (SQL type: text)
   *   Note that values will be retrieved from the database as a string.
   */
  public $api_output;

  /**
   * Text or error message extracted from API call output
   *
   * @var string|null
   *   (SQL type: varchar(2550))
   *   Note that values will be retrieved from the database as a string.
   */
  public $api_output_text;

  /**
   * Error code, if any, extracted from API call output
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $api_output_error_code;

  /**
   * Reason for this action, as given by the API caller
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $reason;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_fpptaquickbooks_log';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Fpptaquickbooks Logs') : E::ts('Fpptaquickbooks Log');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'contact_id', 'civicrm_contact', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('Unique FpptaquickbooksLog ID'),
          'required' => TRUE,
          'where' => 'civicrm_fpptaquickbooks_log.id',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'created' => [
          'name' => 'created',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => E::ts('Created Date'),
          'description' => E::ts('When was the log entry created.'),
          'where' => 'civicrm_fpptaquickbooks_log.created',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'contact_id' => [
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Contact ID'),
          'description' => E::ts('Contact who created this log entry; FK to civicrm_contact'),
          'where' => 'civicrm_fpptaquickbooks_log.contact_id',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'html' => [
            'label' => E::ts("Contact"),
          ],
          'add' => NULL,
        ],
        'unique_request_id' => [
          'name' => 'unique_request_id',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Unique Request ID'),
          'description' => E::ts('Unique identifier for a single php invocation.'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_fpptaquickbooks_log.unique_request_id',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'sync_session_id' => [
          'name' => 'sync_session_id',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Unique ID per sync session'),
          'description' => E::ts('Unique ID per sync session, e.g. Step-thru sync page load or Scheduled Job run.'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_fpptaquickbooks_log.sync_session_id',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'entity_id_param' => [
          'name' => 'entity_id_param',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Entity ID Param'),
          'description' => E::ts('Name of api parameter identifying the relevant entity.'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_fpptaquickbooks_log.entity_id_param',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'entity_id' => [
          'name' => 'entity_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Entity ID '),
          'description' => E::ts('Foreign key to the referenced item.'),
          'where' => 'civicrm_fpptaquickbooks_log.entity_id',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'api_entity' => [
          'name' => 'api_entity',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('API entity'),
          'description' => E::ts('API entity for the api call which triggered this log entry'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_fpptaquickbooks_log.api_entity',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'api_action' => [
          'name' => 'api_action',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('API action'),
          'description' => E::ts('API action for the api call which triggered this log entry'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_fpptaquickbooks_log.api_action',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'api_params' => [
          'name' => 'api_params',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('API parameters'),
          'description' => E::ts('API parameters, as JSON, for the api call which triggered this log entry'),
          'maxlength' => 2550,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_fpptaquickbooks_log.api_params',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'api_output' => [
          'name' => 'api_output',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => E::ts('API output'),
          'description' => E::ts('API call ouptut, as JSON, for the api call which triggered this log entry'),
          'rows' => 4,
          'cols' => 60,
          'where' => 'civicrm_fpptaquickbooks_log.api_output',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'html' => [
            'type' => 'TextArea',
          ],
          'add' => NULL,
        ],
        'api_output_text' => [
          'name' => 'api_output_text',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Text from API output'),
          'description' => E::ts('Text or error message extracted from API call output'),
          'maxlength' => 2550,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_fpptaquickbooks_log.api_output_text',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'api_output_error_code' => [
          'name' => 'api_output_error_code',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Error code from API output'),
          'description' => E::ts('Error code, if any, extracted from API call output'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_fpptaquickbooks_log.api_output_error_code',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'reason' => [
          'name' => 'reason',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Reason'),
          'description' => E::ts('Reason for this action, as given by the API caller'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_fpptaquickbooks_log.reason',
          'table_name' => 'civicrm_fpptaquickbooks_log',
          'entity' => 'FpptaquickbooksLog',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
          'localizable' => 0,
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'fpptaquickbooks_log', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'fpptaquickbooks_log', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
