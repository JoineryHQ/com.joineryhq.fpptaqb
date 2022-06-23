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
      throw new CRM_Fpptaqb_Exception('No live QB sync object has been created yet.');
    }
  }

}
