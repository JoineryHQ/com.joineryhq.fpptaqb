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
    static $ids;
    if (!isset($ids)) {
      // FIXME: hard-coded day-zero cutoff date
      $dayZero = '20220501';

      $ids = [];
      $query = "
        SELECT ctrb.id 
        FROM civicrm_contribution ctrb
          LEFT JOIN civicrm_fpptaquickbooks_contribution_invoice fci ON fci.contribution_id = ctrb.id
        WHERE
          ctrb.receive_date >= %1
          AND fci.id IS NULL
          AND ctrb.contribution_status_id = 1 -- completed
        ORDER BY
          ctrb.receive_date, ctrb.id
      ";
      $queryParams = [
        '1' => [$dayZero, 'Int']
      ];
      $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
      $ids = CRM_Utils_Array::collect('id', $dao->fetchAll());
    }
    return $ids;
  }
  
  /**
   * For all contributions which are ready to be synced, get the first available one.
   * 
   * @return Int
   */
  public static function getInvToSyncIdNext() {
    $ids = self::getInvToSyncIds();
    return $ids[0];
  }
  
  
  /**
   * For a given contribution ID, get an array of all relevant properties for syncing.
   * 
   * @return Array
   */
  public static function getInvToSync(int $id) {
    $lineItemsGet = civicrm_api3('LineItem', 'get', [
      'sequential' => 1,
      'contribution_id' => $id,
      'api.FinancialType.get' => ['return' => ["name"]],
    ]);
    $lineItems = $lineItemsGet['values'];
    foreach ($lineItems as &$lineItem) {
      $lineItem['financialType'] = $lineItem['api.FinancialType.get']['values'][0]['name'];
      $financialTypeId = $lineItem['api.FinancialType.get']['values'][0]['id'];
      $qbItemDetails = self::getQbItemDetails($financialTypeId);
      $lineItem['qbGlCode'] = $qbItemDetails['code'];
      $lineItem['qbGlDescription'] = $qbItemDetails['description'];
    }
    $ret = civicrm_api3('Contribution', 'getSingle', [
      'id' => $id,
    ]);
    $ret += [
      'organizationCid' => '1',
      'organizationName' => 'FIXME: Stub Organization',
      'qbCustomerName' => 'FIXME: Stub Organization',
      'qbCustomerId' => 'FIXME:1123',
      'lineItems' => $lineItems,
      'qbNote' => self::composeInvQbNote($id),
    ];
    
//    var_export($ret); die();
    
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
  
  /**
   * For a given contribution id, check that the contribution exists.
   * 
   * @param int $id
   * 
   * @return boolean|int FALSE if not valid; otherwise the given $id.
   */
  public static function validateInvId($id) {
    // FIXME: STUB.
    if ($id == -1) {
      return FALSE;
    }
    else {
      return $id;
    }
  }
  
  /**
   * For a given contribution id, mark it on hold.
   * 
   * @param int $id
   * 
   * @return void
   */
  public static function holdInv(int $id) {
    // FIXME: STUB.
    if ($id == -200) {
      throw new CRM_Fpptaqb_Exception('Unknown error');
    }
    // FIXME: actually mark this on hold.
  }
  
  public static function getQbItemDetails(int $financialTypeId) {
    // FIXME: STUB
    return [
      'code' => "qb{$financialTypeId}",
      'description' => "qbDescription-{$financialTypeId}",
    ];
  }
  
  /**
   * For a given contribution id, compose a formatted note for the QuickBooks invoice.
   */
  public static function composeInvQbNote(int $id) {
    // FIXME: STUB
    return "CiviCRM Contribution ID: {$id} 
FIXME:CONTACT-NAMES
";
  }
  
  public static function getContributionHash($id) {
    $contribution = self::getInvToSync($id);
    return sha1(json_encode($contribution));
  }
}
