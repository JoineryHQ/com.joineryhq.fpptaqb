<?php

require_once 'fpptaqb.civix.php';
// phpcs:disable
use CRM_Fpptaqb_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_pageRun().
 * @param type $page
 */
function fpptaqb_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  $action = $page->getVar('_action');
  // On browse financial types, display a link to browse FT/QB linkage.
  if ($pageName == 'CRM_Financial_Page_FinancialType' && $action == CRM_Core_Action::BROWSE) {
    $ext = CRM_Extension_Info::loadFromFile(E::path('info.xml'));
    $url = CRM_Utils_System::url('civicrm/admin/fpptaqb/financialType', 'reset=1', NULL, NULL, NULL, NULL, TRUE);
    $message = E::ts('All Financial Types should be linked to a QuickBooks Product/Service. <a href="%1">View linkage for all Financial Types.</a>', [
      '%1' => $url,
    ]);
    CRM_Core_Session::setStatus($message, $ext->label, 'no-popup', ['expires' => 0]);
  }
}

/**
 * Implements hook_civicrm_links().
 */
function fpptaqb_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values) {
  if ($objectName == 'Contribution' && $op = 'contribution.selector.row') {
    $links[] = [
      'name' => E::ts('QB Status'),
      'url' => 'civicrm/fpptaqb/syncstatus',
      'qs' => 'id=%%myObjId%%',
      'title' => E::ts('QB Sync Status'),
    ];
    $values['myObjId'] = $objectId;
  }
}

/**
 * Implements hook_civicrm_buildForm().
 */
function fpptaqb_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Contribute_Form_AdditionalPayment') {
    if (is_array($form->_fpptaqbTemporarilyUnrequiredFields)) {
      // Re-add tempoarily unrequired fields to the list of required fields.
      $form->_required = array_merge($form->_required, $form->_fpptaqbTemporarilyUnrequiredFields);
    }
    if($form->_submitValues['fpptaqb_is_creditmemo']) {
    // If is_creditnote:
      // Ensure the refund amount is matched by sum of line item amounts.
      $lineTotal = 0;
      foreach ($form->_doneFinancialTypeIds as $ftId) {
        $lineTotal += $form->_submitValues['fpptaqb_line_ft_' . $ftId];
      }
      if ($lineTotal != $form->_submitValues['total_amount']) {
        $errors['total_amount'] = E::ts('The Refund Amount must be matched by the total of all credit memo line values (the given total is %1 across all lines, which does not match the Refund Amount of %2).', [
          '1' => CRM_Utils_Money::format($lineTotal),
          '2' => CRM_Utils_Money::format($form->_submitValues['total_amount']),
        ]);
      }

      // Also ensure creditmemo_doc_number doesn't already exist in quickbooks.
      // FIXME: write utility function to test this.
    }
  }
}

/**
 * Implements hook_civicrm_buildForm().
 */
function fpptaqb_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_AdditionalPayment') {
    // FIXME: add similar fields in CRM_Financial_Form_PaymentEdit if payment is a refund.
    $paymentType = $form->get_template_vars('paymentType');
    if ($paymentType == 'refund') {
      // Get the list of bhfe elements from the template; if none, start with
      // an empty array; we'll add fields to this array as we go along, and then
      // assign it to the template at the end.
      $bhfe = $form->get_template_vars('beginHookFormElements');
      if (!$bhfe) {
        $bhfe = [];
      }

      // Define an array to hold default field values.
      $defaults = [];

      $form->addElement('checkbox', 'fpptaqb_is_creditmemo', E::ts('Credit memo?'));
      $defaults['fpptaqb_is_creditmemo'] = 0;
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
        'contribution_id' => $form->_id,
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

      // Assign bhfe array to template so that it will inject the elements.
      $form->assign('beginHookFormElements', $bhfe);

      // Set field default values.
      $form->setDefaults($defaults);

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

      // Add js to place fields in the right location on the form.
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/CRM_Contribute_Form_AdditionalPayment.js');

      $jsvars = [
        'descriptions' => $ftLineFieldDescriptions + [
          // You could define descriptions here, but currently there are none defined.
          // See js/CRM_Financial_Form_FinancialType.js
          // $fieldId => $fieldDescription,
          'fpptaqb_is_creditmemo' => E::ts('Record a credit memo in QuickBooks?'),
        ],
      ];
//      $jsVars['descriptions'] += $ftLineFieldDescriptions;
      CRM_Core_Resources::singleton()->addVars('fpptaqb', $jsvars);
    }
  }
  elseif ($formName == "CRM_Financial_Form_FinancialType") {
    // For the form CRM_Financial_Form_FinancialType, we'll add a 'quickbooks item'
    // field for linking to the correct QB item.
    // First get the list of QB Item options and add the select element.
    $options = CRM_Fpptaqb_Utils_Quickbooks::getItemOptions();
    $form->addElement(
      'select',
      'fpptaqb_quickbooks_id',
      E::ts('QuickBooks: Linked item'),
      ['' => E::ts('- select -')] + $options,
      ['class' => 'crm-select2']
    );
    // Set a default value for this field, if possbile.    
    $defaults = [];
    $financialTypeId = $form->_id;
    if ($financialTypeId) {
      // If this is not a "create new" form:
      // Get existing link if any;
      $financialTypeItem = _fpptaqb_civicrmapi('FpptaquickbooksFinancialTypeItem', 'get', [
        'sequential' => TRUE,
        'financial_type_id' => $financialTypeId,
      ]);
      if ($financialTypeItem['values']) {
        $defaults['fpptaqb_quickbooks_id'] = $financialTypeItem['values'][0]['quickbooks_id'];
      }    
      $form->setDefaults($defaults);
    }
      
    // Add the field to bhfe fields and add JS to move it int othe right place in the DOM.
    $bhfe = $form->get_template_vars('beginHookFormElements');
    if (!$bhfe) {
      $bhfe = [];
    }
    $bhfe[] = 'fpptaqb_quickbooks_id';
    $form->assign('beginHookFormElements', $bhfe);
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/CRM_Financial_Form_FinancialType.js');
    $jsvars = [
      'descriptions' => [
        // You could define descriptions here, but currently there are none defined.
        // See js/CRM_Financial_Form_FinancialType.js
        // $fieldId => $fieldDescription,
      ],
    ];
    CRM_Core_Resources::singleton()->addVars('fpptaqb', $jsvars);
  }
  else {
    $customFieldId = NULL;
    if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
      $customFieldId = Civi::settings()->get('fpptaqb_cf_id_contribution');
    }
    if ($formName == 'CRM_Event_Form_Registration_Register') {
      $customFieldId = Civi::settings()->get('fpptaqb_cf_id_participant');
    }
    if (!empty($customFieldId)) {
      if (array_key_exists("custom_{$customFieldId}", $form->_elementIndex)) {
        $jsVars = [
          'contactRefCustomFieldId' => $customFieldId,
        ];
        CRM_Core_Resources::singleton()->addVars('fpptaqb', $jsVars);
        CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/alterContactRef.js');
      }
    }  
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function fpptaqb_civicrm_postProcess($formName, $form) {
  if ($formName == "CRM_Financial_Form_FinancialType") {
    // Upon processing the CRM_Financial_Form_FinancialType:
    $financialTypeId = $form->_id;
    // Get existing link if any;
    $financialTypeItemId = NULL;
    $financialTypeItem = _fpptaqb_civicrmapi('FpptaquickbooksFinancialTypeItem', 'get', [
      'financial_type_id' => $financialTypeId
    ]);
    if ($financialTypeItem['id']) {
      $financialTypeItemId = $financialTypeItem['id'];
    }
    // Update, create, or delete linked financialtype_item.
    if ($form->_submitValues['fpptaqb_quickbooks_id']) {
      // A QB item is selected, so save the link record.
      _fpptaqb_civicrmapi('FpptaquickbooksFinancialTypeItem', 'create', [
        'id' => $financialTypeItemId,
        'financial_type_id' => $financialTypeId,
        'quickbooks_id' => $form->_submitValues['fpptaqb_quickbooks_id'],
      ]);
    }
    elseif($financialTypeItemId) {
      // A QB item is NOT selected, but a financialTypeItem record exists;
      // delete the link record.
      _fpptaqb_civicrmapi('FpptaquickbooksFinancialTypeItem', 'delete', [
        'id' => $financialTypeItemId,
      ]);
    }
  }
  elseif ($formName == 'CRM_Contribute_Form_AdditionalPayment') {
    // FIXME: allow editing of credit memo details on edit of refund, if cm has not already been synced.
  }
}

/**
 * Implements hook_civicrm_apiWrappers().
 */
function fpptaqb_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if (
    strtolower($apiRequest['entity']) == 'contact'
    && strtolower($apiRequest['action']) == 'get'
    && (($apiRequest['params']['isFpptaqbContactRef'] ?? 0) == 1)
  ) {
    // On contact.get where isFpptaqbContactRef, add wrappers to limit the 
    // contacts returned (see comments in wrapper class).
    $wrappers[] = new CRM_Fpptaqb_APIWrappers_Contact_IsFpptaqbContactRef();
  }
  if (
    strtolower($apiRequest['entity']) == 'payment'
    && strtolower($apiRequest['action']) == 'create'
    && (($apiRequest['params']['fpptaqb_is_creditmemo'] ?? 0) == 1)
  ) {
    // On payment.create where fpptaqb_is_creditmemo, add wrappers to create a
    // creditmemo entry after payment creation.
    $wrappers[] = new CRM_Fpptaqb_APIWrappers_Payment_IsCreditmemo();
  }

  // The APIWrapper is conditionally registered so that it runs only when appropriate
  $loggedApiEntities = [
    'fpptaquickbooksfinancialtypeitem' => ['create'],
    'fpptaquickbookscontactcustomer' => ['create'],
    'fpptaquickbookscontributioninvoice' => ['create'],
    'fpptaquickbookstrxnpayment' => ['create'],
    'fpptaqbstepthruinvoice' => ['load', 'sync', 'hold'],
    'fpptaqbstepthrupayment' => ['load', 'sync', 'hold'],
    'fpptaqbbatchsyncinvoices' => ['process'],
    'fpptaqbbatchsyncpayments' => ['process'],
    // FIXME: add logging for creditmemos
  ];
  $loggedActions = ($loggedApiEntities[strtolower($apiRequest['entity'])] ?? array());
  if (
    !empty($loggedActions)
    && in_array(strtolower($apiRequest['action']), $loggedActions)
  ) {
    if ($apiRequest['version'] == 3) {
      $wrappers[] = new CRM_Fpptaqb_APIWrappers_Log();
    }
  }
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission/
 */
function fpptaqb_civicrm_permission(&$permissions) {
  $permissions['fpptaqb_administer_quickbooks_configuration'] = [
    ts('FPPTA QuickBooks: administer configuration'),                     // label
    null,  // description
  ];
  $permissions['fpptaqb_sync_to_quickbooks'] = [
    ts('FPPTA QuickBooks: sync data to QuickBooks'),                     // label
    null,  // description
  ];
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function fpptaqb_civicrm_config(&$config) {
  _fpptaqb_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function fpptaqb_civicrm_install() {
  _fpptaqb_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function fpptaqb_civicrm_postInstall() {
  _fpptaqb_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function fpptaqb_civicrm_uninstall() {
  _fpptaqb_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function fpptaqb_civicrm_enable() {
  _fpptaqb_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function fpptaqb_civicrm_disable() {
  _fpptaqb_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function fpptaqb_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _fpptaqb_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function fpptaqb_civicrm_entityTypes(&$entityTypes) {
  _fpptaqb_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function fpptaqb_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function fpptaqb_civicrm_navigationMenu(&$menu) {
  _fpptaqb_get_max_navID($menu, $max_navID);
  $items = CRM_Fpptaqb_Util::getNavigationMenuItems();
  foreach ($items as $item) {
    $item['properties']['navID'] = ++$max_navID;
    _fpptaqb_civix_insert_navigation_menu($menu, $item['parent'], $item['properties']);
    _fpptaqb_civix_navigationMenu($menu);
  }
}

/**
 * For an array of menu items, recursively get the value of the greatest navID
 * attribute.
 * @param <type> $menu
 * @param <type> $max_navID
 */
function _fpptaqb_get_max_navID(&$menu, &$max_navID = NULL) {
  foreach ($menu as $id => $item) {
    if (!empty($item['attributes']['navID'])) {
      $max_navID = max($max_navID, $item['attributes']['navID']);
    }
    if (!empty($item['child'])) {
      _fpptaqb_get_max_navID($item['child'], $max_navID);
    }
  }
}

/**
 * Log CiviCRM API errors to CiviCRM log.
 */
function _fpptaqb_log_api_error(Exception $e, string $entity, string $action, array $params) {
  $message = "CiviCRM API Error '{$entity}.{$action}': " . $e->getMessage() . '; ';
  $message .= "API parameters when this error happened: " . json_encode($params) . '; ';
  $bt = debug_backtrace();
  $error_location = "{$bt[1]['file']}::{$bt[1]['line']}";
  $message .= "Error API called from: $error_location";
  CRM_Core_Error::debug_log_message($message);
}

/**
 * CiviCRM API wrapper. Wraps with try/catch, redirects errors to log, saves
 * typing.
 */
function _fpptaqb_civicrmapi(string $entity, string $action, array $params, bool $silence_errors = FALSE) {
  try {
    $result = civicrm_api3($entity, $action, $params);
  }
  catch (CiviCRM_API3_Exception $e) {
    _fpptaqb_log_api_error($e, $entity, $action, $params);
    if (!$silence_errors) {
      throw $e;
    }
  }

  return $result;
}
