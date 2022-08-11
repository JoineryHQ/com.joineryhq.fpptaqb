<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Utility methods for fpptaqb extension
 */
  class CRM_Fpptaqb_Util {

  public static function getSyncObject() {
    $classname = Civi::settings()->get('fpptaqb_use_sync_class');
    if (!array_key_exists($classname, self::getSyncClassOptions())) {
      throw new CRM_Fpptaqb_Exception('Invalid sync object; check configuration for FPPTA QuickBooks Sync.');
    }
    return $classname::singleton();
  }

  public static function getSyncClassOptions() {
    return [
      'CRM_Fpptaqb_Sync_Mock' => E::ts('Development mock for all actions'),
      'CRM_Fpptaqb_Sync_QuickbooksReadonly' => E::ts('Live QuickBooks connection READ actions; Develpoment mock for WRITE actions'),
      'CRM_Fpptaqb_Sync_Quickbooks' => E::ts('Live QuickBooks connection for all actions'),
    ];
  }
  
  public static function getLogCallerId() {
    if (!isset(Civi::$statics[__METHOD__]['logCallerId'])) {
      $maxId = CRM_Core_DAO::singleValueQuery("select ifnull(max(id), 0) from civicrm_fpptaquickbooks_log");
      Civi::$statics[__METHOD__]['logCallerId'] = uniqid() . $maxId;
    }
    return Civi::$statics[__METHOD__]['logCallerId'];
  }

}
