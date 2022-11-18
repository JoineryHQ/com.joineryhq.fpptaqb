<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Utility methods for fpptaqb extension
 */
class CRM_Fpptaqb_Util {

  /**
   * 
   * @return Object of the appropriate sync class.
   * @throws CRM_Fpptaqb_Exception if an invalid sync class has been configured 
   *   in fpptaqb_use_sync_class.
   */
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
      'fpptaquickbooksfinancialtypeitem.create' => E::ts('Link a Financial Type to a QuickBooks item'),
      'fpptaquickbookscontactcustomer.create' => E::ts('Link a Contact to a QuickBooks customer'),
      'fpptaquickbookscontributioninvoice.create' => E::ts('Record the link between a Contribution/Invoice and a QuickBooks invoice'),
      'fpptaquickbookstrxnpayment.create' => E::ts('Record the link between a Payment/Transaction and a QuickBooks payment'),
      'fpptaqbstepthruinvoice.load' => E::ts('Load a Contribution/Invoice in preparation for sync to QuickBooks'),
      'fpptaqbstepthruinvoice.sync' => E::ts('Sync a Contribution/Invoice to QuickBooks'),
      'fpptaqbstepthruinvoice.hold' => E::ts('Place a Contribution/Invoice on hold'),
      'fpptaqbstepthrupayment.load' => E::ts('Load a Payment/Transaction in preparation for sync to QuickBooks'),
      'fpptaqbstepthrupayment.sync' => E::ts('Sync a Payment/Transaction to QuickBooks'),
      'fpptaqbstepthrupayment.hold' => E::ts('Place a Payment/Transaction on hold'),
      'fpptaqbbatchsyncinvoices.process' => E::ts('Process all ready invoices for sync'),
      'fpptaqbbatchsyncpayments.process' => E::ts('Process all ready payments for sync'),
    ];
  }
  
  public static function formatApiAction($apiEntity, $apiAction) {
    $defaultFormattedAction = "{$apiAction} {$apiEntity}";
    
    // Force to lowercase to facilityate string comparison.
    $apiEntity = strtolower($apiEntity);
    $apiAction = strtolower($apiAction);
      
    $key = "{$apiEntity}.{$apiAction}";
    $options = self::createApiActionOptionsList();
    $formattedAction = $options[$key];

    // If we haven't identified a proper return value yet, fall back to a default 
    // 'action entity' format.
    if (empty($formattedAction)) {
      $formattedAction = $defaultFormattedAction;
    }

    return $formattedAction;
  }

  /**
   * Generate a magic string to bypass verification hash on invoice and payment
   * syncing. This string is unique per php invocation so that it can't (reasonably)
   * be guessed but will be reusable in a single api call, e.g. fpptaqbBatchSyncPayments.process.
   *
   * @staticvar string $hash
   * @return string
   */
  public static function getHashBypassString() {
    static $hash;
    if (!isset($hash)) {
      $hash = 'FPPTAQB_HASH_BYPASS_' . uniqid();
    }
    return $hash;
  }

  /**
   * Add creditmemo-related fields to a given form. This is expected to be the "new payment"
   * or "edit payment" form for a refund.
   *
   * @param Obj $form CRM_Core_Form
   * @param Int $contributionId ID of the contribution entity associated with the payment.
   */
  public static function alterPaymentFormForCreditmemo(&$form, $contributionId) {
    // Get the list of bhfe elements from the template; if none, start with
    // an empty array; we'll add fields to this array as we go along, and then
    // assign it to the template at the end.
    $bhfe = $form->get_template_vars('beginHookFormElements');
    if (!$bhfe) {
      $bhfe = [];
    }

    $form->addElement('checkbox', 'fpptaqb_is_creditmemo', E::ts('Credit memo?'));
    $bhfe[] = 'fpptaqb_is_creditmemo';

    $form->addElement(
      'Text',
      // field name
      'fpptaqb_creditmemo_doc_number',
      // field label
      E::ts('Credit memo number'),
      ['class' => 'fpptaqb_creditmemo_hide']
    );
    $form->addRule('fpptaqb_creditmemo_doc_number', E::ts('Credit memo number is a required field.'), 'required');
    $bhfe[] = 'fpptaqb_creditmemo_doc_number';

    $form->addElement(
      'textarea',
      'fpptaqb_creditmemo_customer_memo',
      E::ts('Credit memo comment'),
      ['class' => 'fpptaqb_creditmemo_hide']
    );
    $bhfe[] = 'fpptaqb_creditmemo_customer_memo';

    // Add one Line field per financial type in the contribution
    $lineItemGet = _fpptaqb_civicrmapi('lineItem', 'get', [
      'contribution_id' => $contributionId,
      'unit_price' => ['>' => 0],
      'api.FinancialType.getSingle' => [],
    ]);
    // Define a container for financial types for which line fields are created.
    $doneFinancialTypeIds = [];
    $ftLineFieldDescriptions = [];
    foreach($lineItemGet['values'] as $lineItemValue) {
      $ftId = $lineItemValue['financial_type_id'];
      if (in_array($ftId, $doneFinancialTypeIds)) {
        // we've already created a field for this financial type, so don't bother.
        continue;
      }
      $ftName = $lineItemValue['api.FinancialType.getSingle']['name'];
      $ftLineFieldId = 'fpptaqb_line_ft_'. $ftId;
      $form->addMoney(
        $ftLineFieldId,
        E::ts('Line value: %1', [1 => $ftName]),
        NULL,
        ['class' => 'fpptaqb_creditmemo_hide']
      );
      // Default each line amount to zero (the calling function can further update
      // the default value if needed, e.g. for existing creditmemos).
      $form->setDefaults([$ftLineFieldId => '0.00']);

      // Set a description for this line amount field.
      $ftLineFieldDescriptions[$ftLineFieldId] = E::ts('Dollar amount for financial type "%1"', [1 => $ftName]);
      // Append the QB item name if known, or warning if unknown.
      $qbItemDetails = CRM_Fpptaqb_Utils_Quickbooks::getItemDetails($ftId);
      if($qbItemDetails['FullyQualifiedName']) {
        $ftLineFieldDescriptions[$ftLineFieldId] .= E::ts(' (QuickBooks Item: %1)', [1 => $qbItemDetails['FullyQualifiedName']]);
      }
      else {
        $ftLineFieldDescriptions[$ftLineFieldId] .= E::ts(' (WARNING: no QuickBooks Item found for this Financial Type!)');
      }

      $bhfe[] = 'fpptaqb_line_ft_'. $ftId;
      $doneFinancialTypeIds[] = $ftId;
    }
    $form->_doneFinancialTypeIds = $doneFinancialTypeIds;

    foreach ($bhfe as $bhfeElementId) {
      // Append a class to all bhfe elements so we can manage them in JS without
      // interfering with other possible bhfe elements provided by other extensions.
      $el = $form->getElement($bhfeElementId);
      $class = $el->getAttribute('class') . ' fpptaqb_creditmemo_field';
      $el->updateAttributes(['class' => $class]);
    }

    // Assign bhfe array to template so that it will inject the elements.
    $form->assign('beginHookFormElements', $bhfe);

    if ($form->_flagSubmitted && !$form->_submitValues['fpptaqb_is_creditmemo']) {
      // Some of our fields are displayed only if "is_creditmemo" is 'yes';
      // but when they're displayed, some of them are required. In that case
      // we want them to be displayed as required (red asterisk), so we define
      // them as required in this buildForm method; but in truth they're only
      // conditionally required -- only if "is_creditmemo" is 'yes'. Therefore,
      // if "is_creditmemo" is 'no', we'll temporarily unrequire them.
      $temporarilyUnrequiredFields = [];
      $index = array_search('fpptaqb_creditmemo_doc_number', $form->_required);
      if ($index) {
        unset($form->_required[$index]);
        $temporarilyUnrequiredFields[] = 'fpptaqb_creditmemo_doc_number';
      }
      // Store these unrequired field names so we can re-require them in hook_civicrm_validateForm().
      $form->_fpptaqbTemporarilyUnrequiredFields = $temporarilyUnrequiredFields;
    }

    $jsvars = [
      'descriptions' => $ftLineFieldDescriptions + [
        'fpptaqb_is_creditmemo' => E::ts('Record a credit memo in QuickBooks?'),
      ],
    ];
    CRM_Core_Resources::singleton()->addVars('fpptaqb', $jsvars);

  }
}
