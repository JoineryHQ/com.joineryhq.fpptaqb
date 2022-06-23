<?php

class CRM_Fpptaqb_Utils_Quickbooks {

    public static function getItemDetails(int $financialTypeId) {
    // FIXME: STUB
    return [
      'code' => "FIXME:qb{$financialTypeId}",
      'description' => "FIXME:qbDescription-{$financialTypeId}",
    ];
  }

  public static function getCustomerIdForContact($id) {
    $qbCustomer = civicrm_api3('FpptaquickbooksContactCustomer', 'get', [
      'sequential' => 1,
      'contact_id' => 1,
    ]);
    if (!empty($qbCustomer['values'][0]['qb_id'])) {
      $customerId = $qbCustomer['values'][0]['qb_id'];
    }
    else {
      $sync = CRM_Fpptaqb_Util::getSyncObject();
      $customerId = $sync->fetchCustomerIdForContact($id);
      civicrm_api3('FpptaquickbooksContactCustomer', 'create', [
        'contact_id' => 1,
        'qb_id' => $customerId,
      ]);
    }
    return $customerId;
  }

  public static function getCustomerDetails($customerId) {
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    return $sync->fetchCustomerDetails($customerId);
  }
}