<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from com.joineryhq.fpptaqb/xml/schema/CRM/Fpptaqb/FpptaquickbooksTrxnPayment.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:780b9d3eef37b59ad5d47afb4fb14e57)
 */
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Database access object for the FpptaquickbooksTrxnPayment entity.
 */
class CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_fpptaquickbooks_trxn_payment';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique FpptaquickbooksTrxnPayment ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * FK to civicrm_financial_trxn
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $financial_trxn_id;

  /**
   * Quickbooks payment ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $quickbooks_id;

  /**
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_mock;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_fpptaquickbooks_trxn_payment';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Fpptaquickbooks Trxn Payments') : E::ts('Fpptaquickbooks Trxn Payment');
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
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'financial_trxn_id', 'civicrm_financial_trxn', 'id');
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
          'description' => E::ts('Unique FpptaquickbooksTrxnPayment ID'),
          'required' => TRUE,
          'where' => 'civicrm_fpptaquickbooks_trxn_payment.id',
          'table_name' => 'civicrm_fpptaquickbooks_trxn_payment',
          'entity' => 'FpptaquickbooksTrxnPayment',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'financial_trxn_id' => [
          'name' => 'financial_trxn_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('FK to civicrm_financial_trxn'),
          'where' => 'civicrm_fpptaquickbooks_trxn_payment.financial_trxn_id',
          'table_name' => 'civicrm_fpptaquickbooks_trxn_payment',
          'entity' => 'FpptaquickbooksTrxnPayment',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment',
          'localizable' => 0,
          'FKClassName' => 'CRM_Financial_DAO_FinancialTrxn',
          'add' => NULL,
        ],
        'quickbooks_id' => [
          'name' => 'quickbooks_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('Quickbooks payment ID'),
          'where' => 'civicrm_fpptaquickbooks_trxn_payment.quickbooks_id',
          'table_name' => 'civicrm_fpptaquickbooks_trxn_payment',
          'entity' => 'FpptaquickbooksTrxnPayment',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment',
          'localizable' => 0,
          'add' => NULL,
        ],
        'is_mock' => [
          'name' => 'is_mock',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Is this a mock sync?'),
          'required' => TRUE,
          'import' => TRUE,
          'where' => 'civicrm_fpptaquickbooks_trxn_payment.is_mock',
          'export' => TRUE,
          'default' => '0',
          'table_name' => 'civicrm_fpptaquickbooks_trxn_payment',
          'entity' => 'FpptaquickbooksTrxnPayment',
          'bao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment',
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'fpptaquickbooks_trxn_payment', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'fpptaquickbooks_trxn_payment', $prefix, []);
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
    $indices = [
      'UI_fpptaquickbooks_trxn_payment_financial_trxn_id' => [
        'name' => 'UI_fpptaquickbooks_trxn_payment_financial_trxn_id',
        'field' => [
          0 => 'financial_trxn_id',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_fpptaquickbooks_trxn_payment::1::financial_trxn_id',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
