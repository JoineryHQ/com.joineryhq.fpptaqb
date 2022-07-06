<?php

// phpcs:disable
use CRM_Fpptaqb_ExtensionUtil as E;
// phpcs:enable

class CRM_Fpptaqb_Utils_Invoice {

  /**
   * Get a list of IDs for contributions which are ready to be synced.
   *
   * @return Array
   */
  public static function getReadyToSyncIds() {
    static $ids;
    if (!isset($ids)) {
      $ids = [];
      $query = "
        SELECT ctrb.id
        FROM civicrm_contribution ctrb
          LEFT JOIN civicrm_fpptaquickbooks_contribution_invoice fci ON fci.contribution_id = ctrb.id
        WHERE
          ctrb.receive_date >= %1
          AND ctrb.receive_date <= (NOW() - INTERVAL %2 DAY)
          AND fci.id IS NULL
        ORDER BY
          ctrb.receive_date, ctrb.id
      ";
      $queryParams = [
        '1' => [CRM_Utils_Date::isoToMysql(Civi::settings()->get('fpptaqb_minimum_date')), 'Int'],
        '2' => [CRM_Utils_Date::isoToMysql(Civi::settings()->get('fpptaqb_sync_wait_days')), 'Int'],
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
        if (empty($qbItemDetails)) {
          throw new CRM_Fpptaqb_Exception(E::ts('QuickBooks item not found for financial type: %1; is the Financial Account properly configured?', ['%1' => $lineItem['financialType']]), 503);
        }
        $lineItem['qbItemDetails'] = $qbItemDetails;
      }
      $contribution = civicrm_api3('Contribution', 'getSingle', [
        'id' => $contributionId,
      ]);
      $organizationCid = self::getAttributedContactId($contributionId);
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
    $count = civicrm_api3('Contribution', 'getCount', [
      'id' => $id,
    ]);
    if ($count) {
      return $id;
    }
    else {
      return FALSE;
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

  /**
   * For a given contributionId, return the contact ID of the contact to whom
   * the contribution should be attributed (per the field specified in 
   * fpptaqbhelper settings fpptaqbhelper_cf_id_contribution or fpptaqbhelper_cf_id_participant)
   * 
   * @param int $contributionId
   * 
   * @return int
   */
  public static function getAttributedContactId($contributionId) {
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
