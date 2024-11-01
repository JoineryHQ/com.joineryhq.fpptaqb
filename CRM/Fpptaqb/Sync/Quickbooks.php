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
   * @throws CRM_Fpptaqb_Exception with code 503, if quickbooks dataservice object creation throws an exception.
   * @throws Exception if there's an error in getting customers from quickbooks.
   * @throws CRM_Fpptaqb_Exception with code 503, if no matching customer can be found.
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
      throw new CRM_Fpptaqb_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage(), 503);
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
   * @throws CRM_Fpptaqb_Exception with code 503, if quickbooks dataservice object creation throws an exception.
   * @throws Exception if there's an error in getting customers from quickbooks.
   * @throws CRM_Fpptaqb_Exception with code 503, if no matching customer can be found.
   */
  public function fetchCustomerDetails($customerId) {
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $customer = $dataService->FindById('Customer', $customerId);
    }
    catch (Exception $e) {
      throw new CRM_Fpptaqb_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage(), 503);
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
   * @return Int QuickBooks internal invoice id.]
   * 
   * @throws CRM_Fpptaqb_Exception with code 503, if quickbooks dataservice object creation throws an exception.
   * @throws CRM_Fpptaqb_Exception with code 503, if there's an error adding the invoice in quickbooks.
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
   * 
   * @throws CRM_Fpptaqb_Exception with code 503, if quickbooks dataservice object creation throws an exception.
   * @throws CRM_Fpptaqb_Exception with code 503, if there's an error adding the payment in quickbooks.
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

  /**
   * Given a creditmemo, push it to QB via api.
   *
   * @param Array $creditmemo
   *   Creditmemo details as built by CRM_Fpptaqb_Utils_Creditmemo::getReadyToSync().
   *
   * @return Int QuickBooks internal creditmemo id.
   * 
   * @throws CRM_Fpptaqb_Exception with code 503, if quickbooks dataservice object creation throws an exception.
   * @throws CRM_Fpptaqb_Exception with code 503, if there's an error adding the creditmemo in quickbooks.
   */
  public function pushCm($creditmemo) {

    // Construct the array of relevant creditmemo data for QB payment creation.
    // Reference: https://developer.intuit.com/app/developer/qbo/docs/api/accounting/all-entities/creditmemo#create-a-credit-memo
    $creditmemoParams = [
      "CustomerRef" => [
        "value" => $creditmemo['qbCustomerId'],
      ],
      "CustomerMemo" => [
       "value" => $creditmemo['quickbooks_customer_memo'],
      ],
      "DocNumber" => $creditmemo['quickbooks_doc_number'],
      "TxnDate" => $creditmemo['trxn_date'],
      'Line' => [],
    ];
    foreach ($creditmemo['lineItems'] as $lineItem) {
      $creditmemoParams['Line'][] = [
        'DetailType' => 'SalesItemLineDetail',
        'Amount' => $lineItem['total_amount'],
        'SalesItemLineDetail' => [
          "ServiceDate" => $creditmemo['trxn_date'],
          'Qty' => 1,
          'UnitPrice' => $lineItem['total_amount'],
          'ItemRef' => [
            'value' => $lineItem['qbItemDetails']['Id'],
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
    // Prep the creditmemo object and then create creditmemo in QuickBooks.
    // Reference: https://intuit.github.io/QuickBooks-V3-PHP-SDK/quickstart.html#create-new-resources-post

    // Create the creditmemo  entity object based on params.
    $creditmemoEntity = \QuickBooksOnline\API\Facades\CreditMemo::create($creditmemoParams);
    // Send the payment entity to the data service 'add' medthod.
    $creditmemoAdded = $dataService->Add($creditmemoEntity);

    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new CRM_Fpptaqb_Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"', 503);
    }
    // Return the QB payment ID.
    return $creditmemoAdded->Id;
  }

  public function fetchItemById($id) {
    if (!isset($this->items)) {
      $this->items = $this->fetchActiveItemsList();
    }
    return $this->items[$id];
  }

  /**
   * 
   * @return Array
   * 
   * @throws CRM_Fpptaqb_Exception with code 503, if quickbooks dataservice object creation throws an exception.
   * @throws CRM_Fpptaqb_Exception with code 503, if there's an error fetching items from quickbooks.
   */
  public function fetchActiveItemsList() {
    $ret = [];
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $items = $dataService->Query("select * from Item maxresults 1000");
    }
    catch (Exception $e) {
      throw new CRM_Fpptaqb_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage(), 503);
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new CRM_Fpptaqb_Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"', 503);
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

  /**
   * 
   * @return Array
   * @throws CRM_Fpptaqb_Exception with code 503, if quickbooks dataservice object creation throws an exception.
   * @throws CRM_Fpptaqb_Exception with code 503, if there's an error fetching accounts from quickbooks.
   */
  public function fetchActiveAccountsList() {
    $ret = [];
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $accounts = $dataService->Query("select * from Account where AccountType = 'Bank' and Active");
    }
    catch (Exception $e) {
      throw new CRM_Fpptaqb_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage(), 503);
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new CRM_Fpptaqb_Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"', 503);
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

  /**
   * 
   * @return Array
   * @throws CRM_Fpptaqb_Exception with code 503, if quickbooks dataservice object creation throws an exception.
   * @throws CRM_Fpptaqb_Exception with code 503, if there's an error fetching payment methods from quickbooks.
   */
  public function fetchActivePaymentMethodsList() {
    $ret = [];
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $paymentMethods = $dataService->Query("select * from PaymentMethod where Active");
    }
    catch (Exception $e) {
     throw new CRM_Fpptaqb_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage(), 503);
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new CRM_Fpptaqb_Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"', 503);
    }

    foreach ($paymentMethods as $paymentMethod) {
      $ret[$paymentMethod->Id] = (array)$paymentMethod;
    }
    return $ret;
  }

  /**
   * Fetch a creditmemo from quickbooks for a given creditmemo number (docNumber).
   *
   * @param String $docNumber
   * @return Obj|Bool Fully loaded quickbooks CreditMemo object if found, or FALSE.
   * @throws CRM_Fpptaqb_Exception
   * @throws Exception
   */
  public function fetchCmByDocNumber($docNumber) {
    try {
      $dataService = CRM_Fpptaqb_APIHelper::getAccountingDataServiceObject();
      $dataService->throwExceptionOnError(FALSE);
      $queryDocNumber = addslashes($docNumber);
      $creditmemos = $dataService->Query("select * from CreditMemo Where DocNumber = '$queryDocNumber'");
    }
    catch (Exception $e) {
      throw new CRM_Fpptaqb_Exception('Could not get QuickBooks DataService Object: ' . $e->getMessage(), 503);
    }
    if ($lastError = $dataService->getLastError()) {
      $errorMessage = CRM_Fpptaqb_APIHelper::parseErrorResponse($lastError);
      throw new Exception('QuickBooks error: "' . implode("\n", $errorMessage) . '"');
    }
    return ($creditmemos[0] ?? FALSE);
  }
}
