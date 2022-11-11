<?php
use CRM_Fpptaqb_ExtensionUtil as E;


class CRM_Fpptaqb_Utils_Creditmemo {

  /**
   * Get a list of IDs for creditmemos which are ready to be synced.
   *
   * @return Array
   */
  public static function getReadyToSyncIds() {
    $ids = [];
    $query = "
      select cm.id
      from
        civicrm_fpptaquickbooks_trxn_creditmemo cm
        inner join civicrm_financial_trxn ft on cm.financial_trxn_id = ft.id
        inner join civicrm_entity_financial_trxn eft on eft.financial_trxn_id = cm.financial_trxn_id
        inner join civicrm_fpptaquickbooks_contribution_invoice fci on fci.contribution_id = eft.entity_id
          and fci.quickbooks_id is not null
      where 
        cm.quickbooks_id = -1
        and eft.entity_table = 'civicrm_contribution'
      order by
        ft.trxn_date
    ";
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $ids = CRM_Utils_Array::collect('id', $dao->fetchAll());
    return $ids;
  }

  /**
   * For all creditmemos which are ready to be synced, get the first available one.
   *
   * @return Int
   */
  public static function getReadyToSyncIdNext() {
    $ids = self::getReadyToSyncIds();
    return $ids[0];
  }

  /**
   * For a given creditmemo, get an array of all relevant properties for syncing.
   *
   * @return Array
   * @throws CRM_Fpptaqb_Exception with code 404 if creditmemo can't be found
   */
  public static function getReadyToSync(int $creditmemoId) {
    static $cache = [];
    if (!isset($cache[$creditmemoId])) {

      if (!self::validateId($creditmemoId)) {
        throw new CRM_Fpptaqb_Exception('Credit Memo not found', 404);
      }

      $creditmemo = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'getSingle', [
        'id' => $creditmemoId,
      ]);
      
      $contributionId = _fpptaqb_civicrmapi('EntityFinancialTrxn', 'getValue', [
        'entity_table' => "civicrm_contribution",
        'financial_trxn_id' => $creditmemo['financial_trxn_id'],
        'return' => 'entity_id'
      ]);

      $organizationCid = CRM_Fpptaqb_Utils_Invoice::getAttributedContactId($contributionId);
      $qbCustomerId = CRM_Fpptaqb_Utils_Quickbooks::getCustomerIdForContact($organizationCid);
      $qbCustomerDetails = CRM_Fpptaqb_Utils_Quickbooks::getCustomerDetails($qbCustomerId);
      
      $financialTrxnDate = _fpptaqb_civicrmapi('FinancialTrxn', 'getValue', [
        'id' => $creditmemo['financial_trxn_id'],
        'return' => 'trxn_date'
      ]);
      
      $lineItemsGet = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemoLine', 'get', [
        'sequential' => 1,
        'creditmemo_id' => $creditmemoId,
        'api.FinancialType.get' => ['return' => ["name"]],
      ]);
      $lineItems = $lineItemsGet['values'];
      $qbLineItems = [];
      foreach ($lineItems as &$lineItem) {
        $lineItem['financialType'] = $lineItem['api.FinancialType.get']['values'][0]['name'];
        $financialTypeId = $lineItem['api.FinancialType.get']['values'][0]['id'];
        if ((float)$lineItem['total_amount'] > 0) {
          $lineItem['qbItemDetails'] = CRM_Fpptaqb_Utils_Quickbooks::getItemDetails($financialTypeId);
          $qbLineItems[] = $lineItem;
        }
        else {
          $lineItem['qbItemDetails'] = CRM_Fpptaqb_Utils_Quickbooks::getNullItem();
        }

        // If we have no qbItem, it means this is a non-zero line item, and that
        // no corresponding active qbItem was found, so we can't proceed. Throw
        // an exception.
        if (empty($lineItem['qbItemDetails'])) {
          throw new CRM_Fpptaqb_Exception(E::ts('QuickBooks item not found for financial type: %1; is the Financial Type properly configured?', ['%1' => $lineItem['financialType']]), 503);
        }
      }
      $creditmemo += [
        'qbLineItems' => $qbLineItems,
        'organizationCid' => $organizationCid,
        'organizationName' => _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'contributionId' => $contributionId,
        'qbCustomerName' => $qbCustomerDetails['DisplayName'],
        'qbCustomerId' => $qbCustomerId,
        'trxn_date' => $financialTrxnDate,
      ];
      
      $cache[$creditmemoId] = $creditmemo;
    }
    return $cache[$creditmemoId];
  }

  /**
   * For a given financialTrxn ID, get an array of all relevant properties for listing
   * in "Review Held Items".
   *
   * @return Array
   * @throws CRM_Fpptaqb_Exception with code 404 if contribution payment can't be found
   */
  public static function getHeldItem(int $financialTrxnId) {
    throw new CRM_Fpptaqb_Exception('Method '. __METHOD__ . ' not yet ready.');
    static $cache = [];
    if (!isset($cache[$financialTrxnId])) {
      $financialTrxnCount = _fpptaqb_civicrmapi('FinancialTrxn', 'getCount', [
        'id' => $financialTrxnId,
      ]);

      if (!$financialTrxnCount) {
        throw new CRM_Fpptaqb_Exception('Credit Memo not found', 404);
      }

      $financialTrxn = _fpptaqb_civicrmapi('FinancialTrxn', 'getSingle', [
        'id' => $financialTrxnId,
      ]);
      $contributionId = _fpptaqb_civicrmapi('EntityFinancialTrxn', 'getValue', [
        'entity_table' => "civicrm_contribution",
        'financial_trxn_id' => $financialTrxnId,
        'return' => 'entity_id'
      ]);

      $organizationCid = CRM_Fpptaqb_Utils_Invoice::getAttributedContactId($contributionId);

      $financialTrxn += [
        'organizationName' => _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'organizationCid' => $organizationCid,
        'contributionId' => $contributionId,
        'paymentInstrumentLabel' => CRM_Core_PseudoConstant::getLabel('CRM_Core_BAO_FinancialTrxn', 'payment_instrument_id', $financialTrxn['payment_instrument_id']),
      ];
      $cache[$financialTrxnId] = $financialTrxn;
    }
    return $cache[$financialTrxnId];
  }

  /**
   * Get a list of IDs for all contributions marked to be held out from syncing.
   *
   * @return Array
   */
  public static function getHeldIds() {
    throw new CRM_Fpptaqb_Exception('Method '. __METHOD__ . ' not yet ready.');
    static $ids;
    if (!isset($ids)) {
      $ids = [];
      $query = "
        SELECT ft.id
        FROM civicrm_financial_trxn ft
          INNER JOIN civicrm_fpptaquickbooks_trxn_payment tp ON tp.financial_trxn_id = ft.id
        WHERE
          tp.quickbooks_id IS NULL
        ORDER BY
          ft.trxn_date, ft.id
      ";
      $dao = CRM_Core_DAO::executeQuery($query);
      $ids = CRM_Utils_Array::collect('id', $dao->fetchAll());
    }
    return $ids;
  }

  /**
   * For a given creditmemo id, check that the creditmemo record exists and is
   * pending sync.
   *
   * @param int $creditmemoId
   *
   * @return boolean|int FALSE if not valid; otherwise the given $creditmemoId.
   */
  public static function validateId($creditmemoId) {

    $creditmemoCount = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'getCount', [
      'id' => $creditmemoId,
      'quickbooks_id' => 0,
    ]);

    if ($creditmemoCount) {
      return $creditmemoId;
    }
    else {
      return FALSE;
    }
  }

  /**
   * For a given creditmemo id, mark it on hold.
   *
   * @param int $creditmemoId
   *
   * @return void
   */
  public static function hold(int $creditmemoId) {
    // Log the contribution-invoice connection
    $result = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'create', [
      'id' => $creditmemoId,
      'quickbooks_id' => 'null',
    ]);
  }

  public static function getHash($creditmemoId) {
    $creditmemo = self::getReadyToSync($creditmemoId);
    return sha1(json_encode($creditmemo));
  }

  public static function sync($creditmemoId) {
    $creditmemo = self::getReadyToSync($creditmemoId);
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $qbCreditmemoId = $sync->pushCreditmemo($creditmemo);

    // Log the trxn-payment connection
    $result = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'create', [
      'id' => $creditmemoId,
      'quickbooks_id' => $qbCreditmemoId,
      'is_mock' => $sync->isMock(),
    ]);

    return $qbCreditmemoId;
  }

  public static function getStepthruStatistics() {
    throw new CRM_Fpptaqb_Exception('Method '. __METHOD__ . ' not yet ready.');
    return [
      'countReady' => count(self::getReadyToSyncIds()),
      'countHeld' => count(self::getHeldIds()),
    ];
  }

}