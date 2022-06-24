<?php

class CRM_Fpptaqb_Utils_Payment {

  /**
   * Get a list of IDs for paymeents which are ready to be synced.
   *
   * @return Array
   */
  public static function getReadyToSyncIds() {
    static $ids;
    if (!isset($ids)) {
      // FIXME: STUB
      return array (1,2,3);
      
      // FIXME: following code copied from CRM_Fpptaqb_Utils_Invoice::getReadyToSyncIds().
      
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
  public static function getReadyToSyncIdNext() {
    $ids = self::getReadyToSyncIds();
    return $ids[0];
  }

  /**
   * For a given contribution ID, get an array of all relevant properties for syncing.
   *
   * @return Array
   */
  public static function getReadyToSync(int $contributionId) {
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
        $qbItemDetails = CRM_Fpptaqb_Utils_Quickbooks::getItemDetails($financialTypeId);
        $lineItem['qbGlCode'] = $qbItemDetails['code'];
        $lineItem['qbGlDescription'] = $qbItemDetails['description'];
      }
      $contribution = civicrm_api3('Contribution', 'getSingle', [
        'id' => $contributionId,
      ]);
      $organizationCid = self::getContactId($contributionId);
      $qbCustomerId = CRM_Fpptaqb_Utils_Quickbooks::getCustomerIdForContact($organizationCid);
      $qbCustomerDetails = CRM_Fpptaqb_Utils_Quickbooks::getCustomerDetails($qbCustomerId);
      $contribution += [
        'organizationCid' => $organizationCid,
        'organizationName' => civicrm_api3('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'qbCustomerName' => $qbCustomerDetails['name'],
        'qbCustomerId' => $qbCustomerId,
        'qbInvNumber' => preg_replace('/^' . Civi::settings()->get('invoice_prefix') . '/', '', $contribution['invoice_number']),
        'lineItems' => $lineItems,
        'qbNote' => self::composeQbNote($contributionId),
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
  public static function getHeldIds() {
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
  public static function validateId($id) {
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
  public static function hold(int $contributionId) {
    // Log the contribution-invoice connection
    $result = civicrm_api3('FpptaquickbooksContributionInvoice', 'create', [
      'contribution_id' => $contributionId,
      'quickbooks_id' => 'null',
    ]);
  }

  /**
   * For a given contribution id, compose a formatted note for the QuickBooks invoice.
   */
  public static function composeQbNote(int $id) {
    // FIXME: STUB
    return "CiviCRM Contribution ID: {$id}
FIXME:CONTACT-NAMES
";
  }

  public static function getHash($id) {
    $contribution = self::getReadyToSync($id);
    return sha1(json_encode($contribution));
  }

  public static function getContactId($contributionId) {
    // FIXME: STUB
    return 184; // Bay Health School
  }

  public static function sync($contributionId) {
    $contribution = self::getReadyToSync($contributionId);
    $sync = CRM_Fpptaqb_Util::getSyncObject();
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
      'countReady' => count(self::getReadyToSyncIds()),
      'countHeld' => count(self::getHeldIds()),
    ];
  }

}
