<?php

/**
 * Readonly QB sync class.
 * Uses only CRM_Fpptaqb_Sync_Mock methods for write actions,
 * and only CRM_Fpptaqb_Sync_Quickbooks for read actions.
 */
class CRM_Fpptaqb_Sync_QuickbooksReadonly {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   */
  private static $_singleton = NULL;

  private $liveSync;
  private $mockSync;

  /**
   * The constructor. Use self::singleton() to create an instance.
   */
  private function __construct() {
    $this->liveSync = CRM_Fpptaqb_Sync_Quickbooks::singleton();
    $this->mockSync = CRM_Fpptaqb_Sync_Mock::singleton();
  }

  /**
   * Singleton function used to manage this object.
   *
   * @return Object
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Fpptaqb_Sync_QuickbooksReadonly();
    }
    return self::$_singleton;
  }

  /**
   * Is this a mock sync? (It's either that, or it's live.)
   */
  public function isMock() {
    return $this->mockSync->isMock();
  }  

  /**
   * For a given contact ID, get the QB customer number.
   * 
   * @return int
   */
  public function fetchCustomerIdForContact($contactId) {
    return $this->liveSync->fetchCustomerIdForContact($contactId);
  }

  /**
   * For a given QuickBooks customer ID, get relevant customer details.
   * 
   * @return Array
   */
  public function fetchCustomerDetails($customerId) {
    return $this->liveSync->fetchCustomerDetails($customerId);
  }

  /**
   * Given a contribution, push it to QB via api.
   * 
   * @param Array $contribution
   *   Contribution details as built by CRM_Fpptaqb_Utils_Invoice::getInvToSync().
   */
  public function pushInv($contribution) {
    return $this->mockSync->pushInv($contribution);
  }
  
  /**
   * Given a contribution, push it to QB via api.
   * 
   * @param Array $payment
   *   Payment details as built by CRM_Fpptaqb_Utils_Pmt::getReadyToSync().
   */
  public function pushPmt($payment) {
    return $this->mockSync->pushPmt($payment);
  }
  
  /**
   * Given a creditmemo, push it to QB via api.
   * 
   * @param Array $creditmemo
   *   Payment details as built by CRM_Fpptaqb_Utils_Creditmemo::getReadyToSync().
   */
  public function pushCreditmemo($creditmemo) {
    return $this->mockSync->pushCreditmemo($creditmemo);
  }
  
  public function fetchItemById($id) {
    return $this->liveSync->fetchItemById($id);
  }

  public function fetchActiveItemsList() {
    return $this->liveSync->fetchActiveItemsList();
  }


  public function fetchAccountById($id) {
    return $this->liveSync->fetchAccountById($id);
  }

  public function fetchActiveAccountsList() {
    return $this->liveSync->fetchActiveAccountsList();
  }

  public function fetchPaymentMethodById($id) {
    return $this->liveSync->fetchPaymentMethodById($id);
  }

  public function fetchActivePaymentMethodsList() {
    return $this->liveSync->fetchActivePaymentMethodsList();
  }
}
