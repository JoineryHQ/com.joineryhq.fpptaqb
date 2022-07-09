<?php

class CRM_Fpptaqb_Utils_Quickbooks {

  public static function getItemDetails(int $financialTypeId) {
    $itemDetails = [];

    $financialTypeItem = _fpptaqb_civicrmapi('FpptaquickbooksFinancialTypeItem', 'get', [
      'sequential' => TRUE,
      'financial_type_id' => $financialTypeId,
    ]);
    if ($financialTypeItem['count'] == 1) {
      $qbItemId = $financialTypeItem['values'][0]['quickbooks_id'];
      $sync = CRM_Fpptaqb_Util::getSyncObject();
      $itemDetails = $sync->fetchItemById($qbItemId);
    }

    return $itemDetails;
  }

  public static function getCustomerIdForContact($contactId) {
    $qbCustomer = _fpptaqb_civicrmapi('FpptaquickbooksContactCustomer', 'get', [
      'sequential' => 1,
      'contact_id' => $contactId,
    ]);
    if (!empty($qbCustomer['values'][0]['quickbooks_id'])) {
      $customerId = $qbCustomer['values'][0]['quickbooks_id'];
    }
    else {
      $sync = CRM_Fpptaqb_Util::getSyncObject();
      $customerId = $sync->fetchCustomerIdForContact($contactId);
      _fpptaqb_civicrmapi('FpptaquickbooksContactCustomer', 'create', [
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
