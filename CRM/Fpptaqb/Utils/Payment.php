<?php
use CRM_Fpptaqb_ExtensionUtil as E;


class CRM_Fpptaqb_Utils_Payment {

  /**
   * Get a list of IDs for paymeents which are ready to be synced.
   *
   * @return Array
   */
  public static function getReadyToSyncIds() {
    $ids = [];
    $query = "
      select
        t.*
      from
        (
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
            and ft.total_amount > 0
        ) t
        -- left-join this to a list of transactions that appear to be corresponding
        -- 'audit' entries in civicrm, e.g. where the financial type has been changed.
        -- Such entries are recorded in pairs, have ids within 1 of each other,
        -- have the same timestamp, and have a total_amount negative of each other.
        -- If the potential payment record we're examining has such a matching record,
        -- we'll treat it as an 'audit' entry and not process it as an actual payment.
        left join civicrm_financial_trxn txn on (
          txn.id = (t.id + 1)
          or txn.id = (t.id - 1)
        )
        and txn.trxn_date = t.trxn_date
        and txn.total_amount = (t.total_amount * -1)
      where
        -- We want only those transactions that are not matched.
        txn.id is null
      order by
        t.trxn_date
    ";
    $queryParams = [
      '1' => [CRM_Utils_Date::isoToMysql(Civi::settings()->get('fpptaqb_minimum_date')), 'Int'],
      '2' => [CRM_Utils_Date::isoToMysql(Civi::settings()->get('fpptaqb_sync_wait_days')), 'Int'],
    ];
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $ids = CRM_Utils_Array::collect('id', $dao->fetchAll());
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
   * For a given financialTrxn ID, get an array of all relevant properties for syncing.
   *
   * @return Array
   * @throws CRM_Fpptaqb_Exception with code 404 if contribution payment can't be found
   */
  public static function getReadyToSync(int $financialTrxnId) {
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
      if (!$organizationCid) {
        // It's odd that we would not have a known organization on a Payment, because
        // this payment will only be ready-to-sync if the related Contribution was synced,
        // and that would have required a known organization. If we're in this situation,
        // it must be that the contribution/participant records was changed somenow --
        // perhaps the related organization was deleted instead of duplicate-merged,
        // or something else. In any case, this is an oddity that the user should address.
        throw new CRM_Fpptaqb_Exception(E::ts('Could not identify an attributed organization for contribution id=%1', ['%1' => $contributionId]), 500);
      }
      $qbCustomerId = CRM_Fpptaqb_Utils_Quickbooks::getCustomerIdForContact($organizationCid);
      $qbCustomerDetails = CRM_Fpptaqb_Utils_Quickbooks::getCustomerDetails($qbCustomerId);

      // Record the synced QB invoice ID, if any.
      $qbInvGet = _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'get', [
        'sequential' => TRUE,
        'contribution_id' => $contributionId,
        'return' => 'quickbooks_id',
      ]);
      if ($qbInvGet['count']) {
        $qbInvId = $qbInvGet['values'][0]['quickbooks_id'];
      }
      else {
        $qbInvId = E::ts('(No synced QuickBooks invoice found)');
      }

      // Define a value for QuickBooks "Reference No." field on this payment.
      switch($financialTrxn['payment_instrument_id']) {
        // EFT
        case '5';
          $financialTrxn['qbReferenceNo'] = ($financialTrxn['trxn_id'] ?? '');
          break;
        // Check
        case '4';
          $financialTrxn['qbReferenceNo'] = ($financialTrxn['check_number'] ?? '');
          break;
        // Credit card or Debit card:
        case '1';
        case '2';
          $financialTrxn['qbReferenceNo'] = ($financialTrxn['pan_truncation'] ?? '');
          break;
      }

      $qbDepositToAccountId = (Civi::settings()->get('fpptaqb_pmt_deposit_to_account_id') ?? NULL);

      $financialTrxn += [
        'contributionCid' => $contribution['contact_id'],
        'organizationCid' => $organizationCid,
        'organizationName' => _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'contributionId' => $contributionId,
        'qbCustomerName' => $qbCustomerDetails['DisplayName'],
        'qbCustomerId' => $qbCustomerId,
        'qbInvNumber' => CRM_Fpptaqb_Utils_Quickbooks::prepInvNumber($contribution['invoice_number']),
        'qbInvId' => $qbInvId,
        'qbDepositToAccountId' => $qbDepositToAccountId,
        'qbDepositToAccountLabel' => CRM_Fpptaqb_Utils_Quickbooks::getAccountOptions()[$qbDepositToAccountId],
        'paymentInstrumentLabel' => CRM_Core_PseudoConstant::getLabel('CRM_Core_BAO_FinancialTrxn', 'payment_instrument_id', $financialTrxn['payment_instrument_id']),
        'cardTypeLabel' => CRM_Core_PseudoConstant::getLabel('CRM_Financial_DAO_FinancialTrxn', 'card_type_id', $financialTrxn['card_type_id']),
      ];
      self::appendQbPaymentMethod($financialTrxn);
      $cache[$financialTrxnId] = $financialTrxn;
    }
    return $cache[$financialTrxnId];
  }

  public static function appendQbPaymentMethod(&$financialTrxn) {
    $cardType = ($financialTrxn['card_type_id'] ?? NULL);
    $crmPaymentMethodId = ($financialTrxn['payment_instrument_id'] ?? NULL);
    $paymentMethodRules = (json_decode(Civi::settings()->get('fpptaqb_qb_payment_method_rules'), TRUE) ?? []);

    foreach ($paymentMethodRules as $paymentMethodRule) {
      if (
        (
          $paymentMethodRule['crmPaymentMethod'] == 0
          || $paymentMethodRule['crmPaymentMethod'] == $crmPaymentMethodId
        )
        && (
          $paymentMethodRule['cardType'] == 0
          || $paymentMethodRule['cardType'] == $cardType
        )
      ) {
        $qbPaymentMethodId = $paymentMethodRule['qbPaymentMethod'];
        $sync = CRM_Fpptaqb_Util::getSyncObject();
        $qbPaymentMethod = $sync->fetchPaymentMethodById($qbPaymentMethodId);
        if ($qbPaymentMethod) {
          $financialTrxn['qbPaymentMethodId'] = $qbPaymentMethodId;
          $financialTrxn['qbPaymentMethodLabel'] = $qbPaymentMethod['Name'];
        }
        break;
      }
    }
  }

  /**
   * For a given financialTrxn ID, get an array of all relevant properties for listing
   * in "Review Held Items".
   *
   * @return Array
   * @throws CRM_Fpptaqb_Exception with code 404 if contribution payment can't be found
   */
  public static function getHeldItem(int $financialTrxnId) {
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

      $organizationCid = CRM_Fpptaqb_Utils_Invoice::getAttributedContactId($contributionId);
      if ($organizationCid) {
        $organizationName = _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]);
      }
      else {
        // It's odd that we would not have a known organization on a Payment, because
        // this payment will only be ready-to-sync if the related Contribution was synced,
        // and that would have required a known organization. If we're in this situation,
        // it must be that the contribution/participant records was changed somenow --
        // perhaps the related organization was deleted instead of duplicate-merged,
        // or something else. In any case, this is an oddity that the user should address.
        $organizationName = E::ts('ERROR: NONE FOUND');
      }

      $financialTrxn += [
        'organizationName' => $organizationName,
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
    static $ids;
    if (!isset($ids)) {
      $ids = [];
      $query = "
        SELECT ft.id
        FROM civicrm_financial_trxn ft
          INNER JOIN civicrm_fpptaquickbooks_trxn_payment tp ON tp.financial_trxn_id = ft.id
        WHERE
          tp.quickbooks_id IS NULL
          AND ft.trxn_date >= %1
        ORDER BY
          ft.trxn_date, ft.id
      ";
      $queryParams = [
        '1' => [CRM_Utils_Date::isoToMysql(Civi::settings()->get('fpptaqb_minimum_date')), 'Int'],
      ];
      $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
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
