<?php

class CRM_Fpptaqb_Utils_Quickbooks {

  public static function getItemDetails(int $financialTypeId) {
    // FIXME: STUB
    return [
      'code' => "FIXME:qb{$financialTypeId}",
      'description' => "FIXME:qbDescription-{$financialTypeId}",
    ];
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
