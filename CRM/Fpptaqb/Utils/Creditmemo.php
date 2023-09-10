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
        ft.trxn_date >= %1
        AND ft.trxn_date <= (NOW() - INTERVAL %2 DAY)
        AND cm.quickbooks_id = 0
        AND eft.entity_table = 'civicrm_contribution'
      order by
        ft.trxn_date
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

      $financialTrxn = _fpptaqb_civicrmapi('FinancialTrxn', 'getSingle', [
        'id' => $creditmemo['financial_trxn_id'],
        'return' => [
          'trxn_date',
          'total_amount',
         ],
      ]);

      $creditmemoLineGet = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemoLine', 'get', [
        'sequential' => 1,
        'creditmemo_id' => $creditmemoId,
        'total_amount' => ['>' => 0],
        'api.FinancialType.get' => [
          'id' => '$value.ft_id',
          'return' => 'name',
        ],
      ]);
      $creditmemoLineValues = $creditmemoLineGet['values'];
      $lineItems = [];
      foreach ($creditmemoLineValues as $creditmemoLineValue) {
        $lineItem = [
          'total_amount' => $creditmemoLineValue['total_amount'],
          'label' => $creditmemoLineValue['api.FinancialType.get']['values'][0]['name'],
          'qbItemDetails' => CRM_Fpptaqb_Utils_Quickbooks::getItemDetails($creditmemoLineValue['ft_id']),
        ];
        // If we have no qbItem, it means this is a non-zero line item, and that
        // no corresponding active qbItem was found, so we can't proceed. Throw
        // an exception.
        if (empty($lineItem['qbItemDetails'])) {
          throw new CRM_Fpptaqb_Exception(E::ts('QuickBooks item not found for financial type: %1; is the Financial Type properly configured?', ['%1' => $creditmemoLineValue['financialType']]), 503);
        }

        $lineItems[] = $lineItem;
      }
      $creditmemo += [
        'lineItems' => $lineItems,
        'organizationCid' => $organizationCid,
        'organizationName' => _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'contributionId' => $contributionId,
        'qbCustomerName' => $qbCustomerDetails['DisplayName'],
        'qbCustomerId' => $qbCustomerId,
        'trxn_date' => $financialTrxn['trxn_date'],
        'total_amount' => ($financialTrxn['total_amount'] * -1),
      ];
      
      $cache[$creditmemoId] = $creditmemo;
    }
    return $cache[$creditmemoId];
  }

  /**
   * For a given creditmemo ID, get an array of all relevant properties for listing
   * in "Review Held Items".
   *
   * @return Array
   * @throws CRM_Fpptaqb_Exception with code 404 if contribution payment can't be found
   */
  public static function getHeldItem(int $creditmemoId) {
    static $cache = [];
    if (!isset($cache[$creditmemoId])) {
      $creditmemoCount = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'getCount', [
        'id' => $creditmemoId,
      ]);

      if (!$creditmemoCount) {
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

      $financialTrxn = _fpptaqb_civicrmapi('FinancialTrxn', 'getSingle', [
        'id' => $creditmemo['financial_trxn_id'],
        'return' => [
          'trxn_date',
          'total_amount',
         ],
      ]);

      $creditmemo += [
        'organizationName' => _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'organizationCid' => $organizationCid,
        'contributionId' => $contributionId,
        'trxn_date' => $financialTrxn['trxn_date'],
        'total_amount' => ($financialTrxn['total_amount'] * -1),
      ];
      $cache[$creditmemoId] = $creditmemo;
    }
    return $cache[$creditmemoId];
  }

  /**
   * Get a list of IDs for all creditmemos marked to be held out from syncing.
   *
   * @return Array
   */
  public static function getHeldIds() {
    static $ids;
    if (!isset($ids)) {
      $ids = [];
      $query = "
        SELECT cm.id
        FROM civicrm_financial_trxn ft
          INNER JOIN civicrm_fpptaquickbooks_trxn_creditmemo cm ON cm.financial_trxn_id = ft.id
        WHERE
          cm.quickbooks_id IS NULL
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
   * For a given creditmemo id, check that the creditmemo record exists and is
   * (optionally) pending sync.
   *
   * @param int $creditmemoId
   * @param bool $requireIsPending If true, require that quickbooks_id = 0 (not synced and not on hold).
   *
   * @return boolean|int FALSE if not valid; otherwise the given $creditmemoId.
   */
  public static function validateId($creditmemoId, $requireIsPending = FALSE) {
    $apiParams = [
      'id' => $creditmemoId,
    ];
    if ($requireIsPending) {
      $apiParams['quickbooks_id'] = 0;
    }
    $creditmemoCount = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'getCount', $apiParams);

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
    $qbCreditmemoId = $sync->pushCm($creditmemo);

    // Log the trxn-payment connection
    $result = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'create', [
      'id' => $creditmemoId,
      'quickbooks_id' => $qbCreditmemoId,
      'is_mock' => $sync->isMock(),
    ]);

    return $qbCreditmemoId;
  }

  public static function getStepthruStatistics() {
    return [
      'countReady' => count(self::getReadyToSyncIds()),
      'countHeld' => count(self::getHeldIds()),
    ];
  }

  /**
   * Create a credtimemo and creditmemolines based on the given data.
   *
   * @param Array $creditmemoParams array of api parameters for FpptaquickbooksTrxnCreditmemoLine.create
   * @param Array $lineFinancialtypeAmounts Array of FinancialTypes with dollar
   *    values for each, in the form [$ftId => $amount], to be stored as lines
   *    on this creditmemo.
   */
  public static function createCreditmemoWithLines($creditmemoParams, $lineFinancialtypeAmounts) {
    // Create the creditmemo.
    $creditmemo = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'create', $creditmemoParams);

    foreach ($lineFinancialtypeAmounts as $ftId => $amount) {
      $creditmemoLine = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemoLine', 'create', [
        'creditmemo_id' => $creditmemo['id'],
        'ft_id' => $ftId,
        'total_amount' => $amount,
      ]);
    }
  }

  /**
   * Convert form values (or similar array of values) to an array of creditmemolines.
   * Only values in the form /^fpptaqb_line_ft_([0-9]+)$/ are used, with the trailing
   * integer(s) used as the financial type id.
   *
   * @param Array $formValues as from a form (see fpptaqb_civicrm_buildForm() for CRM_Contribute_Form_AdditionalPayment, for example)
   * @return Array suitable for passing as the $lineFinancialtypeAmounts argument in self::createCreditmemoWithLines().
   */
  public static function composeLinesFromFormValues($formValues) {
    $lines = [];
    foreach($formValues as $key => $value) {
      $matches = NULL;
      if (preg_match('/^fpptaqb_line_ft_([0-9]+)$/', $key, $matches) && ($value > 0)) {
        $lines[$matches[1]] = $value;
      }
    }
    return $lines;
  }
}
