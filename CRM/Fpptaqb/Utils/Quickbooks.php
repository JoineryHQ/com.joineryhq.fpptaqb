<?php
// phpcs:disable
use CRM_Fpptaqb_ExtensionUtil as E;
// phpcs:enable

class CRM_Fpptaqb_Utils_Quickbooks {

  public static function prepInvNumber($invNumber) {
    return preg_replace('/^' . Civi::settings()->get('invoice_prefix') . '/', '', $invNumber);
  }

  public static function getItemOptions() {
    $options = [];
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $activeItems = $sync->fetchActiveItemsList();
    foreach ($activeItems as $activeItem) {
      $options[$activeItem['Id']] = $activeItem['FullyQualifiedName'];
    }
    return $options;
  }

  public static function getAccountOptions() {
    $options = [
      '' => E::ts('[None / QuickBooks default]'),
    ];
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $activeAccounts = $sync->fetchActiveAccountsList();
    foreach ($activeAccounts as $activeAccount) {
      $options[$activeAccount['Id']] = $activeAccount['Name'];
    }
    return $options;
  }

  public static function getPaymentMethodOptions() {
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $activePaymentMethods = $sync->fetchActivePaymentMethodsList();
    foreach ($activePaymentMethods as $activePaymentMethod) {
      $options[$activePaymentMethod['Id']] = $activePaymentMethod['Name'];
    }
    return $options;
  }

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
      if ($customerId) {
        // Only cache this if an actual value is found.
        _fpptaqb_civicrmapi('FpptaquickbooksContactCustomer', 'create', [
          'contact_id' => $contactId,
          'quickbooks_id' => $customerId,
        ]);
      }
    }
    return $customerId;
  }

  public static function getCustomerDetails($customerId) {
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    return $sync->fetchCustomerDetails($customerId);
  }

  public static function getNullItem() {
    return array (
      'Name' => E::ts('N/A: zero-dollar line, not used.'),
      'Description' => E::ts('N/A: zero-dollar line, not used.'),
      'Active' => true,
      'SubItem' => true,
      'Level' => 1,
      'FullyQualifiedName' => E::ts('N/A: zero-dollar line, not used.'),
      'domain' => 'QBO',
    );
  }

  /**
   * For a given set of lineItems, consolidate to an array grouped by QB ItemId,
   * with a correct count per type.
   *
   * @param array $qbLineItems An array of line items. Each item is the output of
   *   civicrm api3 lineItem.getSingle, with an additional element 'qbItemDetails',
   *   which is the output of CRM_Fpptaqb_Utils_Quickbooks::getItemDetails($financialTypeId)
   *   for the value of lineItem.financial_type_id.
   *
   * @return array [$qbItemId => [$prop => $value]].
   */
  public static function consolidateLineItems($qbLineItems) {
    $ret = [];
    $countsPerItem = [];
    foreach ($qbLineItems as $qbLineItem) {
      $itemId = $qbLineItem['qbItemDetails']['Id'];
      if (isset($ret[$itemId])) {
        ++$ret[$itemId]['qty'];
      }
      else {
        $ret[$itemId] = [
          'qty' => 1,
          'qbItemDetails' => $qbLineItem['qbItemDetails'],
          'unit_price' => $qbLineItem['unit_price'],
          'label' => $qbLineItem['label'],
        ];
      }
    }
    return $ret;
  }
}
