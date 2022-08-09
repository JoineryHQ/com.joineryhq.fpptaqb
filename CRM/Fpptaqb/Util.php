<?php

/**
 * Utility methods for fpptaqb extension
 */
class CRM_Fpptaqb_Util {

  public static function getSyncObject() {
    if (Civi::settings()->get('fpptaqb_use_sync_mock')) {
      return CRM_Fpptaqb_Sync_Mock::singleton();
    }
    else {
      return CRM_Fpptaqb_Sync_Quickbooks::singleton();
    }
  }
  
  public static function getLogCallerId() {
    if (!isset(Civi::$statics[__METHOD__]['logCallerId'])) {
      $maxId = CRM_Core_DAO::singleValueQuery("select ifnull(max(id), 0) from civicrm_fpptaquickbooks_log");
      Civi::$statics[__METHOD__]['logCallerId'] = uniqid() . $maxId;
    }
    return Civi::$statics[__METHOD__]['logCallerId'];
  }

}
