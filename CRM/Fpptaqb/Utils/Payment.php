<?php
use CRM_Fpptaqb_ExtensionUtil as E;


class CRM_Fpptaqb_Utils_Payment {

  /**
   * Get a list of IDs for paymeents which are ready to be synced.
   *
   * @return Array
   */
  public static function getReadyToSyncIds() {
    static $ids;
    if (!isset($ids)) {    
      $ids = [];
      $query = "
        select
          ft.id,
          ft.trxn_date,
          ft.total_amount
        from
          civicrm_entity_financial_trxn eft
          inner join civicrm_financial_trxn ft on eft.financial_trxn_id = ft.id
          inner join civicrm_financial_account fa on ft.to_financial_account_id = fa.id
          inner join civicrm_fpptaquickbooks_contribution_invoice fci on fci.contribution_id = eft.entity_id
             and fci.quickbooks_id is not null
          left join civicrm_fpptaquickbooks_trxn_payment tp on tp.financial_trxn_id = ft.id
        where
          ft.trxn_date >= %1
          AND ft.trxn_date <= (NOW() - INTERVAL %2 DAY)
          and ft.is_payment
          and eft.entity_table = 'civicrm_contribution'
          and tp.id is null
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
  public static function getReadyToSync(int $financialTrxnId, $ignoreQbInvoice = FALSE) {
    static $cache = [];
    if (!isset($cache[$financialTrxnId])) {
      $financialTrxnCount = _fpptaqb_civicrmapi('FinancialTrxn', 'getCount', [
        'id' => $financialTrxnId,
      ]);

      if (!$financialTrxnCount) {
        throw new CRM_Fpptaqb_Exception('Payment not found', 404);
      }

      $financialTrxn = _fpptaqb_civicrmapi('FinancialTrxn', 'getSingle', [
        'id' => $financialTrxnId,
      ]);
      $contributionId = _fpptaqb_civicrmapi('EntityFinancialTrxn', 'getValue', [
        'entity_table' => "civicrm_contribution", 
        'financial_trxn_id' => $financialTrxnId,
        'return' => 'entity_id'
      ]);
      
      $contribution = _fpptaqb_civicrmapi('Contribution', 'getSingle', ['id' => $contributionId]);

      $organizationCid = CRM_Fpptaqb_Utils_Invoice::getAttributedContactId($contributionId);
      $qbCustomerId = CRM_Fpptaqb_Utils_Quickbooks::getCustomerIdForContact($organizationCid);
      $qbCustomerDetails = CRM_Fpptaqb_Utils_Quickbooks::getCustomerDetails($qbCustomerId);
      if (!$ignoreQbInvoice) {
        $qbInvId = _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'getValue', [
          'contribution_id' => $contributionId,
          'return' => 'quickbooks_id',
        ]);
      }

      $financialTrxn += [
        'contributionCid' => $contribution['contact_id'],
        'organizationCid' => $organizationCid,
        'organizationName' => _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'contributionId' => $contributionId,
        'qbCustomerName' => $qbCustomerDetails['name'],
        'qbCustomerId' => $qbCustomerId,
        'qbInvNumber' => CRM_Fpptaqb_Utils_Quickbooks::prepInvNumber($contribution['invoice_number']),
        'qbInvId' => $qbInvId,
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
   * For a given contribution id, check that the contribution exists.
   *
   * @param int $financialTrxnId
   *
   * @return boolean|int FALSE if not valid; otherwise the given $financialTrxnId.
   */
  public static function validateId($financialTrxnId) {
    $count = _fpptaqb_civicrmapi('FinancialTrxn', 'getCount', [
      'id' => $financialTrxnId,
    ]);
    if ($count) {
      return $financialTrxnId;
    }
    else {
      return FALSE;
    }
  }

  /**
   * For a given contribution id, mark it on hold.
   *
   * @param int $trxnId
   *
   * @return void
   */
  public static function hold(int $trxnId) {
    // Log the contribution-invoice connection
    $result = _fpptaqb_civicrmapi('FpptaquickbooksTrxnPayment', 'create', [
      'financial_trxn_id' => $trxnId,
      'quickbooks_id' => 'null',
    ]);
  }

  public static function getHash($trxnId) {
    $payment = self::getReadyToSync($trxnId);
    return sha1(json_encode($payment));
  }

  public static function sync($trxnId) {
    $payment = self::getReadyToSync($trxnId);
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $qbPmtId = $sync->pushPmt($payment);

    // Log the trxn-payment connection
    $result = _fpptaqb_civicrmapi('FpptaquickbooksTrxnPayment', 'create', [
      'financial_trxn_id' => $trxnId,
      'quickbooks_id' => $qbPmtId,
      'is_mock' => $sync->isMock(),
    ]);

    return $qbPmtId;
  }

  public static function getStepthruStatistics() {
    return [
      'countReady' => count(self::getReadyToSyncIds()),
      'countHeld' => count(self::getHeldIds()),
    ];
  }

}
