<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Utility methods for fpptaqb extension
 */
  class CRM_Fpptaqb_Util {

  public static function getSyncObject() {
    $classname = Civi::settings()->get('fpptaqb_use_sync_class');
    if (!array_key_exists($classname, self::getSyncClassOptions())) {
      throw new CRM_Fpptaqb_Exception('Invalid sync object; check configuration for FPPTA QuickBooks Sync.');
    }
    return $classname::singleton();
  }

  public static function getSyncClassOptions() {
    return [
      'CRM_Fpptaqb_Sync_Mock' => E::ts('Development mock for all actions'),
      'CRM_Fpptaqb_Sync_QuickbooksReadonly' => E::ts('Live QuickBooks connection READ actions; Develpoment mock for WRITE actions'),
      'CRM_Fpptaqb_Sync_Quickbooks' => E::ts('Live QuickBooks connection for all actions'),
    ];
  }
  
  public static function getLogCallerId() {
    if (!isset(Civi::$statics[__METHOD__]['logCallerId'])) {
      $maxId = CRM_Core_DAO::singleValueQuery("select ifnull(max(id), 0) from civicrm_fpptaquickbooks_log");
      Civi::$statics[__METHOD__]['logCallerId'] = uniqid() . $maxId;
    }
    return Civi::$statics[__METHOD__]['logCallerId'];
  }

  public static function getNavigationMenuItems() {
    $items = [];
    $items[] = [
      'parent' => 'Contributions',
      'properties' => [
        'label' => E::ts('FPPTA QuickBooks Sync'),
        'name' => 'FPPTA QuickBooks Sync',
        'url' => 'civicrm/fpptaqb/stepthru',
        'permission' => 'fpptaqb_sync_to_quickbooks',
        'operator' => 'AND',
        'separator' => NULL,
      ]
    ];
    $items[] = [
      'parent' => 'Administer/CiviContribute',
      'properties' => [
        'label' => E::ts('FPPTA QuickBooks Settings'),
        'name' => 'FPPTA QuickBooks Settings',
        'url' => 'civicrm/admin/fpptaqb/settings?reset=1',
        'permission' => 'administer CiviCRM',
        'operator' => 'AND',
        'separator' => NULL,
      ]
    ];
    $items[] = [
      'parent' => 'Administer/CiviContribute/' . E::ts('FPPTA QuickBooks Settings'),
      'properties' => [
        'label' => E::ts('Financial Types: Linked to QuickBooks Items'),
        'name' => 'Financial Types: Linked to QuickBooks Items',
        'url' => 'civicrm/admin/fpptaqb/financialType?reset=1',
        'permission' => 'fpptaqb_administer_quickbooks_configuration',
        'operator' => 'AND',
        'separator' => NULL,
      ]
    ];
    $items[] = [
      'parent' => 'Administer/CiviContribute/' . E::ts('FPPTA QuickBooks Settings'),
      'properties' => [
        'label' => E::ts('QuickBooks Payment Method: Rules'),
        'name' => 'QuickBooks Payment Method: Rules',
        'url' => 'civicrm/admin/fpptaqb/qbPaymentMethodRules?reset=1',
        'permission' => 'fpptaqb_administer_quickbooks_configuration',
        'operator' => 'AND',
        'separator' => NULL,
      ]
    ];
    return $items;
  }

  /**
   * Create an array suitable for returning as an error from an api function.
   *
   * This is really just a wrapper around civicrm_api3_create_error(), with
   * slightly easier defining of the 'error_code' value through a function parameter.
   *
   * @param String $errorMessage
   * @param String $errorCode
   * @param Array $extraParams
   * @return Array
   */
  public static function composeApiError($errorMessage, $errorCode, $extraParams) {
    $error = civicrm_api3_create_error($errorMessage, $extraParams);
    $error['error_code'] = $errorCode;
    return $error;
  }
  
  public static function createApiActionOptionsList() {
      return [
      'fpptaquickbooksfinancialtypeitem:create' => E::ts('Link a Financial Type to a QuickBooks item'),
      'fpptaquickbookscontactcustomer:create' => E::ts('Link a Contact to a QuickBooks customer'),
      'fpptaquickbookscontributioninvoice:create' => E::ts('Record the link between a Contribution and a QuickBooks invoice'),
      'fpptaquickbookstrxnpayment:create' => E::ts('Record the link between a Payment and a QuickBooks payment'),
      'fpptaqbstepthruinvoice:load' => E::ts('Load a Contribution in preparation for sync to QuickBooks'),
      'fpptaqbstepthruinvoice:sync' => E::ts('Sync a Contribution to QuickBooks'),
      'fpptaqbstepthruinvoice:hold' => E::ts('Place a Contribution on hold'),
      'fpptaqbstepthrupayment:load' => E::ts('Load a Payment in preparation for sync to QuickBooks'),
      'fpptaqbstepthrupayment:sync' => E::ts('Sync a Payment to QuickBooks'),
      'fpptaqbstepthrupayment:hold' => E::ts('Place a Payment on hold'),
      'fpptaqbbatchsyncinvoices:process' => E::ts('Process all ready invoices for sync'),
    ];
  }
  
  public static function formatApiAction($apiEntity, $apiAction) {
    $defaultFormattedAction = "{$apiAction} {$apiEntity}";
    
    // Force to lowercase to facilityate string comparison.
    $apiEntity = strtolower($apiEntity);
    $apiAction = strtolower($apiAction);
      
    $key = "{$apiEntity}:{$apiAction}";
    $options = self::createApiActionOptionsList();
    $formattedAction = $options[$key];

    // If we haven't identified a proper return value yet, fall back to a default 
    // 'action entity' format.
    if (empty($formattedAction)) {
      $formattedAction = $defaultFormattedAction;
    }

    return $formattedAction;
  }
}
