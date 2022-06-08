<?php

/**
 * Utility methods for fpptaqb extension
 */
class CRM_Fpptaqb_Util {
  /**
   * Get a list of IDs for contributions which are ready to be synced.
   * 
   * @return Array
   */
  public static function getInvToSyncIds() {
    // FIXME: STUB.
    return [1,2];
  }
  
  /**
   * For all contributions which are ready to be synced, get the first available one.
   * 
   * @return Int
   */
  public static function getInvToSyncIdNext() {
    // FIXME: STUB.
    return 1;
  }
  
  
  /**
   * For a given contribution ID, get an array of all relevant properties for syncing.
   * 
   * @return Array
   */
  public static function getInvToSync(int $id) {
    // FIXME: STUB.
    $ret = [
      'id' => $id,
      'payor_contact_id' => '1',
      'payor_name' => 'Stub Organization',
      'payor_qb_customer_id' => '1234',
    ];
    switch ($id) {
      case -1:
        throw new CRM_Fpptaqb_Exception('Contribution not found', 404);
        break;
      case -2:
        throw new CRM_Fpptaqb_Exception('Unknown error');
        break;
      default:
        return $ret;
        break;
    }
  }
  
  /**
   * Get a list of IDs for all contributions marked to be held out from syncing.
   * 
   * @return Array
   */
  public static function getInvHeldIds() {
    // FIXME: STUB.
    return [3,4];
  }
}
