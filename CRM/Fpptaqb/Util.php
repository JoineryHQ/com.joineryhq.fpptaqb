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
  public static function getInvToSync(int $contributionId) {
    static $cache = [];
    if (!isset($cache[$contributionId])) {
      $contributionCount = civicrm_api3('Contribution', 'getCount', [
        'id' => $contributionId,
      ]);
      
      if (!$contributionCount) {
        throw new CRM_Fpptaqb_Exception('Contribution not found', 404);        
      }
        
      $lineItemsGet = civicrm_api3('LineItem', 'get', [
        'sequential' => 1,
        'contribution_id' => $contributionId,
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
      $contribution = civicrm_api3('Contribution', 'getSingle', [
        'id' => $contributionId,
      ]);
      $organizationCid = self::getContributionContactId($contributionId);
      $qbCustomerId = self::getQbCustomerIdForContact($organizationCid);
      $qbCustomerDetails = self::getQbCustomerDetails($qbCustomerId);
      $contribution += [
        'organizationCid' => $organizationCid,
        'organizationName' => civicrm_api3('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'qbCustomerName' => $qbCustomerDetails['name'],
        'qbCustomerId' => $qbCustomerId,
        'lineItems' => $lineItems,
        'qbNote' => self::composeInvQbNote($contributionId),
      ];
      
      $cache[$contributionId] = $contribution;
    }
    return $cache[$contributionId];
  }

  /**
   * Get a list of IDs for all contributions marked to be held out from syncing.
   *
   * @return Array
   */
  public static function getInvHeldIds() {
    static $ids;
    if (!isset($ids)) {
      // FIXME: hard-coded day-zero cutoff date
      $dayZero = '20220501';

      $ids = [];
      $query = "
        SELECT ctrb.id
        FROM civicrm_contribution ctrb
          INNER JOIN civicrm_fpptaquickbooks_contribution_invoice fci ON fci.contribution_id = ctrb.id
        WHERE
          fci.quickbooks_id IS NULL
        ORDER BY
          ctrb.receive_date, ctrb.id
      ";
      $dao = CRM_Core_DAO::executeQuery($query);
      $ids = CRM_Utils_Array::collect('id', $dao->fetchAll());
    }
    return $ids;
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
   * @param int $contributionId
   *
   * @return void
   */
  public static function holdInv(int $contributionId) {
    // Log the contribution-invoice connection 
    $result = civicrm_api3('FpptaquickbooksContributionInvoice', 'create', [
      'contribution_id' => $contributionId,
      'quickbooks_id' => 'null',
    ]);    

  }

  public static function getQbItemDetails(int $financialTypeId) {
    // FIXME: STUB
    return [
      'code' => "FIXME:qb{$financialTypeId}",
      'description' => "FIXME:qbDescription-{$financialTypeId}",
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

  public static function getSyncObject() {
    if (Civi::settings()->get('fpptaqb_use_sync_mock')) {
      return CRM_Fpptaqb_Sync_Mock::singleton();
    }
    else {
      throw new CRM_Fpptaqb_Exception('No live QB sync object has been created yet.');
    }
  }

  public static function getQbCustomerIdForContact($id) {
    $qbCustomer = civicrm_api3('FpptaquickbooksContactCustomer', 'get', [
      'sequential' => 1,
      'contact_id' => 1,
    ]);
    if (!empty($qbCustomer['values'][0]['qb_id'])) {
      $customerId = $qbCustomer['values'][0]['qb_id'];
    }
    else {
      $sync = self::getSyncObject();
      $customerId = $sync->fetchCustomerIdForContact($id);
      civicrm_api3('FpptaquickbooksContactCustomer', 'create', [
        'contact_id' => 1,
        'qb_id' => $customerId,
      ]);
    }
    return $customerId;
  }

  public static function getQbCustomerDetails($customerId) {
    $sync = self::getSyncObject();
    return $sync->fetchCustomerDetails($customerId);
  }

  public static function getContributionContactId($contributionId) {
    // FIXME: STUB
    return 184; // Bay Health School
  }
  
  public static function syncInv($contributionId) {
    $contribution = CRM_Fpptaqb_Util::getInvToSync($contributionId);    
    $sync = self::getSyncObject();
    $qbInvId = $sync->pushInv($contribution);
    
    // Log the contribution-invoice connection 
    $result = civicrm_api3('FpptaquickbooksContributionInvoice', 'create', [
      'contribution_id' => $contributionId,
      'quickbooks_id' => $qbInvId,
    ]);    
    
    return $qbInvId;
  }
  
  public static function getStepthruStatistics() {
    return [
      'countReady' => count(self::getInvToSyncIds()),
      'countHeld' => count(self::getInvHeldIds()),
    ];
  }
}
