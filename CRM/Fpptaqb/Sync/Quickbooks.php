<?php

/**
 * Mock QB sync class.
 */
class CRM_Fpptaqb_Sync_Quickbooks {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   */
  private static $_singleton = NULL;

  /**
   * List of QuickBooks items (products/services)
   * @var Array $id => $item
   */
  private static $items = NULL;

  /**
   * The constructor. Use self::singleton() to create an instance.
   */
  private function __construct() {

  }

  /**
   * Singleton function used to manage this object.
   *
   * @return Object
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Fpptaqb_Sync_Quickbooks();
    }
    return self::$_singleton;
  }

  /**
   * Is this a mock sync? (It's either that, or it's live.)
   */
  public function isMock() {
    return FALSE;
  }

  /**
   * For a given contact ID, get the QB customer number.
   * FIXME: MOCK
   *
   * @return int
   */
  public function fetchCustomerIdForContact($contactId) {
    $contactGet = _fpptaqb_civicrmapi('Contact', 'get', [
      'sequential' => 1,
      'id' => $contactId,
    ]);
    $contactName = $contactGet['values'][0]['display_name'];

    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $queryName = addslashes($contactName);
      $customers = $dataService->Query("select * from Customer Where DisplayName = '$queryName'");
    }
    catch (Exception $e) {
      throw new CRM_Core_Exception('Could not get DataService Object: ' . $e->getMessage());
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"');
    }
    if (count($customers) != 1) {
      throw new CRM_Fpptaqb_Exception('Could not find valid QuickBooks customer', 503);
    }

    $quickbooksId = $customers[0]->Id;
    return $quickbooksId;
  }

  /**
   * For a given QuickBooks customer ID, get relevant customer details.
   * FIXME: MOCK
   *
   * @return Array
   */
  public function fetchCustomerDetails($customerId) {
    return [
      'DisplayName' => "MOCK: Random Customer $customerId",
    ];
  }

  /**
   * Given a contribution, push it to QB via api.
   * FIXME: MOCK
   *
   * @param Array $contribution
   *   Contribution details as built by CRM_Fpptaqb_Utils_Invoice::getInvToSync().
   */
  public function pushInv($contribution) {
    // Sometimes, fail with an error.
    if (self::failRandom(20)) {
      throw new CRM_Fpptaqb_Exception('MOCK sync: this error happens around 20% of the time.', 503);
    }

    return rand(1000, 9999);
  }

  /**
   * Given a contribution, push it to QB via api.
   * FIXME: MOCK
   *
   * @param Array $payment
   *   Payment details as built by CRM_Fpptaqb_Utils_Pmt::getReadyToSync().
   */
  public function pushPmt($payment) {
    // Sometimes, fail with an error.
    if (self::failRandom(20)) {
      throw new CRM_Fpptaqb_Exception('MOCK sync: this error happens around 20% of the time.', 503);
    }

    return rand(1000, 9999);
  }

  public function fetchItemById($id) {
    if (!isset($this->items)) {
      $this->items = $this->fetchActiveItemsList();
    }
    return $this->items[$id];
  }

  public function fetchActiveItemsList() {
    $ret = [];
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $items = $dataService->Query("select * from Item");
    }
    catch (Exception $e) {
      throw new CRM_Core_Exception('Could not get DataService Object: ' . $e->getMessage());
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"');
    }

    foreach ($items as $item) {
      $ret[$item->Id] = (array)$item;
    }
    return $ret;
  }

}

