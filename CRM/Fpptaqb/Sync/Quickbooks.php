<?php

/**
 * Live QB sync class.
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
      throw new CRM_Core_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage());
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"');
    }
    if (count($customers) != 1) {
      throw new CRM_Fpptaqb_Exception('Could not find valid QuickBooks customer with name "'. $contactName .'".', 503);
    }

    $quickbooksId = $customers[0]->Id;
    return $quickbooksId;
  }

  /**
   * For a given QuickBooks customer ID, get relevant customer details.
   *
   * @return Array
   */
  public function fetchCustomerDetails($customerId) {
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $customer = $dataService->FindById('Customer', $customerId);
    }
    catch (Exception $e) {
      throw new CRM_Core_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage());
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"');
    }
    if (empty($customer)) {
      throw new CRM_Fpptaqb_Exception('Could not find valid QuickBooks customer with id '. $customerId, 503);
    }
    return (array)$customer;
  }

  /**
   * Given a contribution, push it to QB via api.
   *
   * @param Array $contribution
   *   Contribution details as built by CRM_Fpptaqb_Utils_Invoice::getInvToSync().
   *
   * @return Int QuickBooks internal invoice id.
   */
  public function pushInv($contribution) {
    // Construct the array of relevant invoice data for QB invoice creation.
    // Reference: https://developer.intuit.com/app/developer/qbo/docs/api/accounting/all-entities/invoice#create-an-invoice
    $invParams = [
      'DocNumber' => $contribution['qbInvNumber'],
      'TxnDate' => CRM_Utils_Date::customFormat($contribution['receive_date'], '%Y-%m-%d'),
      'CustomerMemo' => $contribution['qbNote'],
      'CustomerRef' => [
        'value' => $contribution['qbCustomerId'],
      ],
      'Line' => [],
    ];
    foreach ($contribution['qbLineItems'] as $qbLineItem) {
      $invParams['Line'][] = [
        'DetailType' => 'SalesItemLineDetail',
        'Description' => $qbLineItem['label'],
        'Amount' => ($qbLineItem['unit_price'] * $qbLineItem['qty']),
        'SalesItemLineDetail' => [
          'Qty' => $qbLineItem['qty'],
          'UnitPrice' => $qbLineItem['unit_price'],
          'ItemRef' => [
            'value' => $qbLineItem['qbItemDetails']['Id'],
          ],
        ],
      ];
    }

    // Set up the data service for QB connection.
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
    }
    catch (Exception $e) {
      throw new CRM_Fpptaqb_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage(), 503);
    }
    // Prep the invoice object and then create invoice in QuickBooks.
    // Reference: https://intuit.github.io/QuickBooks-V3-PHP-SDK/quickstart.html#create-new-resources-post

    // Create the invoice entity object based on params.
    $invEntity = \QuickBooksOnline\API\Facades\Invoice::create($invParams);
    // Send the invoice entity to the data service 'add' medthod.
    $invAdded = $dataService->Add($invEntity);

    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new CRM_Fpptaqb_Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"', 503);
    }
    // Return the QB invoice ID.
    return $invAdded->Id;

  }

  /**
   * Given a contribution, push it to QB via api.
   *
   * @param Array $payment
   *   Payment details as built by CRM_Fpptaqb_Utils_Pmt::getReadyToSync().
   *
   * @return Int QuickBooks internal payment id.
   */
  public function pushPmt($payment) {

    // Construct the array of relevant payment data for QB payment creation.
    // Reference: https://developer.intuit.com/app/developer/qbo/docs/api/accounting/all-entities/payment#create-a-payment
    $pmtParams = [
      'TotalAmt' => $payment['total_amount'],
      "CustomerRef" => [
        "value" => $payment['qbCustomerId'],
      ],
      "Line" => [
        [
          "Amount" => $payment['total_amount'],
          "LinkedTxn" => [
           [
            "TxnId" => $payment['qbInvId'],
            "TxnType" => "Invoice"
           ],
          ],
        ]
      ],
      'TxnDate' => CRM_Utils_Date::customFormat($payment['trxn_date'], '%Y-%m-%d'),
      'PaymentRefNum' => ($payment['qbReferenceNo'] ?? NULL),
    ];
    // Set PaymentMethodRef if one has been determined.
    if ($payment['qbPaymentMethodId']) {
      $pmtParams["PaymentMethodRef"] = [
        "value" => $payment['qbPaymentMethodId'],
      ];
    }
    // Set DepositToAccountRef if one has been determined.
    if ($payment['qbDepositToAccountId']) {
      $pmtParams["DepositToAccountRef"] = [
        "value" => $payment['qbDepositToAccountId'],
      ];
    }

    // Set up the data service for QB connection.
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
    }
    catch (Exception $e) {
      throw new CRM_Fpptaqb_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage(), 503);
    }
    // Prep the payment object and then create payment in QuickBooks.
    // Reference: https://intuit.github.io/QuickBooks-V3-PHP-SDK/quickstart.html#create-new-resources-post

    // Create the payment entity object based on params.
    $pmtEntity = \QuickBooksOnline\API\Facades\Payment::create($pmtParams);
    // Send the payment entity to the data service 'add' medthod.
    $pmtAdded = $dataService->Add($pmtEntity);

    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new CRM_Fpptaqb_Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"', 503);
    }
    // Return the QB payment ID.
    return $pmtAdded->Id;
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
      throw new CRM_Core_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage());
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

  public function fetchAccountById($id) {
    if (!isset($this->items)) {
      $this->items = $this->fetchActiveItemsList();
    }
    return $this->items[$id];
  }

  public function fetchActiveAccountsList() {
    $ret = [];
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $accounts = $dataService->Query("select * from Account where AccountType = 'Bank' and Active");
    }
    catch (Exception $e) {
      throw new CRM_Core_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage());
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"');
    }

    foreach ($accounts as $account) {
      $ret[$account->Id] = (array)$account;
    }
    return $ret;
  }

  public function fetchPaymentMethodById($id) {
    $paymentMethods = $this->fetchActivePaymentMethodsList();
    return $paymentMethods[$id];
  }

  public function fetchActivePaymentMethodsList() {
    $ret = [];
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $paymentMethods = $dataService->Query("select * from PaymentMethod where Active");
    }
    catch (Exception $e) {
      throw new CRM_Core_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage());
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"');
    }

    foreach ($paymentMethods as $paymentMethod) {
      $ret[$paymentMethod->Id] = (array)$paymentMethod;
    }
    return $ret;
  }

}
