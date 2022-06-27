<?php

/**
 * Mock QB sync class.
 */
class CRM_Fpptaqb_Sync_Mock {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var CRM_Core_Config
   */
  private static $_singleton = NULL;

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
      self::$_singleton = new CRM_Fpptaqb_Sync_Mock();
    }
    return self::$_singleton;
  }

  /**
   * For a given contact ID, get the QB customer number.
   * 
   * @return int
   */
  public function fetchCustomerIdForContact($contactId) {
    $dummyData = [
      // Bay Health School
      184 => '234h79fwh79',
    ];
    return $dummyData[$contactId];
  }

  /**
   * For a given QuickBooks customer ID, get relevant customer details.
   * 
   * @return Array
   */
  public function fetchCustomerDetails($customerId) {
    $dummyData = [
      '234h79fwh79' => [
        'name' => 'Bay Health School',
      ]
    ];
    return $dummyData[$customerId];
  }

  /**
   * For a given QuickBooks invoice ID, get relevant invoice details.
   * 
   * @return Array
   */
  public function fetchInvoiceDetails($invoiceId) {
    // Return identical data for any invoiceId
    return [
      'docNumber' => '1234',
    ];
  }

  /**
   * Given a contribution, push it to QB via api.
   * 
   * @param Array $contribution
   *   Contribution details as built by CRM_Fpptaqb_Utils_Invoice::getInvToSync().
   */
  public function pushInv($contribution) {
    // Half the time, fail with an error.
    $oddOrEven = rand(0, 9);
    // Error on even integers.
    if ($oddOrEven % 2 == 0) {
      throw new CRM_Fpptaqb_Exception('Placeholder sync: this error happens around half the time.', 503);
    }

    return rand(1000, 9999);
  }

}
