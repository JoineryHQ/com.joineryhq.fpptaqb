<?php

class CRM_Fpptaqb_Utils_Quickbooks {

  public static function getItemDetails(int $financialTypeId) {
    $itemDetails = [];
    // Get financial account for this financial Type
    $entityFinancialAccount = civicrm_api3('entityFinancialAccount', 'get', [
      'sequential' => TRUE,
      'account_relationship' => 1,
      'entity_table' => 'civicrm_financial_type',
      'entity_id' => $financialTypeId,
    ]);

    if ($entityFinancialAccount['count'] == 1) {
      $accountItem = civicrm_api3('FpptaquickbooksAccountItem', 'get', [
        'sequential' => TRUE,
        'financial_account_id' => $entityFinancialAccount['values'][0]['financial_account_id'],
      ]);
      if ($accountItem['count'] == 1) {
        $qbItemId = $accountItem['values'][0]['quickbooks_id'];
        $itemDetails['code'] = $qbItemId;
        $sync = CRM_Fpptaqb_Util::getSyncObject();
        $itemDetails = $sync->fetchItemById($qbItemId);
      }
    }

    return $itemDetails;
  }

  public static function getCustomerIdForContact($contactId) {
    $qbCustomer = civicrm_api3('FpptaquickbooksContactCustomer', 'get', [
      'sequential' => 1,
      'contact_id' => $contactId,
    ]);
    if (!empty($qbCustomer['values'][0]['quickbooks_id'])) {
      $customerId = $qbCustomer['values'][0]['quickbooks_id'];
    }
    else {
      $sync = CRM_Fpptaqb_Util::getSyncObject();
      $customerId = $sync->fetchCustomerIdForContact($contactId);
      civicrm_api3('FpptaquickbooksContactCustomer', 'create', [
        'contact_id' => $contactId,
        'quickbooks_id' => $customerId,
      ]);
    }
    return $customerId;
  }

  public static function getCustomerDetails($customerId) {
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    return $sync->fetchCustomerDetails($customerId);
  }
  
  public static function getInvoiceDetails($invoiceId) {
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    return $sync->fetchInvoiceDetails($invoiceId);
  }

}
