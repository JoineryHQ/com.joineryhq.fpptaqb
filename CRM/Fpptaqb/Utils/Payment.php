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
      // FIXME: hard-coded day-zero cutoff date
      $dayZero = '20220501';
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
          left join civicrm_fpptaquickbooks_trxn_payment tp on tp.financial_trxn_id = ft.id
        where
          ft.trxn_date >= %1
          and ft.is_payment
          and eft.entity_table = 'civicrm_contribution'
          and tp.id is null
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
  public static function getReadyToSync(int $financialTrxnId) {
    static $cache = [];
    if (!isset($cache[$financialTrxnId])) {
      $financialTrxnCount = civicrm_api3('FinancialTrxn', 'getCount', [
        'id' => $financialTrxnId,
      ]);

      if (!$financialTrxnCount) {
        throw new CRM_Fpptaqb_Exception('Payment not found', 404);
      }

      $financialTrxn = civicrm_api3('FinancialTrxn', 'getSingle', [
        'id' => $financialTrxnId,
      ]);
      $contributionId = civicrm_api3('EntityFinancialTrxn', 'getValue', [
        'entity_table' => "civicrm_contribution", 
        'financial_trxn_id' => $financialTrxnId,
        'return' => 'entity_id'
      ]);
      
      $contribution = civicrm_api3('Contribution', 'getSingle', ['id' => $contributionId]);
      
      $organizationCid = CRM_Fpptaqb_Utils_Invoice::getContactId($contributionId);
      $qbCustomerId = CRM_Fpptaqb_Utils_Quickbooks::getCustomerIdForContact($organizationCid);
      $qbCustomerDetails = CRM_Fpptaqb_Utils_Quickbooks::getCustomerDetails($qbCustomerId);
      $qbInvId = civicrm_api3('FpptaquickbooksContributionInvoice', 'getValue', [
        'contribution_id' => $contributionId,
        'return' => 'quickbooks_id',
      ]);
      $qbInvDetails = CRM_Fpptaqb_Utils_Quickbooks::getInoiceDetails($qbInvId);
      
      $financialTrxn += [
        'contributionCid' => $contribution['contact_id'],
        'organizationCid' => $organizationCid,
        'organizationName' => civicrm_api3('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'contributionId' => $contributionId,
        'qbCustomerName' => $qbCustomerDetails['name'],
        'qbCustomerId' => $qbCustomerId,
        'qbInvNumber' => $qbInvDetails['docNumber'],
        'qbInvId' => $qbInvId,
        'paymentInstrumentLabel' => CRM_Core_PseudoConstant::getLabel('CRM_Core_BAO_FinancialTrxn', 'payment_instrument_id', $financialTrxn['payment_instrument_id']),
      ];
//var_dump($financialTrxn); die();
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
      // FIXME: hard-coded day-zero cutoff date
      $dayZero = '20220501';
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
   * @param int $trxnId
   *
   * @return void
   */
  public static function hold(int $trxnId) {
    // Log the contribution-invoice connection
    $result = civicrm_api3('FpptaquickbooksTrxnPayment', 'create', [
      'financial_trxn_id' => $trxnId,
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

  public static function getHash($trxnId) {
    $payment = self::getReadyToSync($trxnId);
    return sha1(json_encode($payment));
  }

  // FIXME: Delete this unused method?
  public static function getContactId($contributionId) {
    // FIXME: STUB
    return 184; // Bay Health School
  }

  public static function sync($trxnId) {
    $payment = self::getReadyToSync($trxnId);
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $qbPmtId = $sync->pushPmt($payment);

    // Log the trxn-payment connection
    $result = civicrm_api3('FpptaquickbooksTrxnPayment', 'create', [
      'financial_trxn_id' => $trxnId,
      'quickbooks_id' => $qbPmtId,
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
