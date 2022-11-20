<?php

// FIXME: TODO: create api for creditmemos like api/v3/FpptaqbBatchSyncInvoices/Process.php

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
 * Implements hook_civicrm_validateForm().
 */
function fpptaqb_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if (
    $formName == 'CRM_Contribute_Form_AdditionalPayment'
    || $formName == 'CRM_Financial_Form_PaymentEdit'
  ) {
    // $total_amount might have a non-numeric characters, e.g. '$ 1,000.02', which
    // will screw up our math below. Therefore clean it up to a proper Float.
    $total_amount = CRM_Utils_Rule::cleanMoney($fields['total_amount']);
    // Creditmemo field validation for "New Refund" and "Edit Payment" forms.
    // Get appropriate comparison values, depending on the form.
    if ($formName == 'CRM_Financial_Form_PaymentEdit') {
      // On Payment Edit form, total is negative for refunds, but on
      // New Refund form, total is positive for refunds. Therefore on Payment Edit
      // form, we should negate total_amount before comparing.
      $total_amount = ($total_amount * -1);
      // On Payment Edit, form->_id is the financial_trxn_id for the payment.
      $financial_trxn_id = $form->getVar('_id');
    }
    else {
      // On New Refund form, there is no financial_trxn_id because we haven't created it yet.
      $financial_trxn_id = NULL;
    }
    CRM_Fpptaqb_Util::validateCreditMemoInPaymentForm($formName, $fields, $files, $form, $errors, $total_amount, $financial_trxn_id);
  }
}

/**
 * Implements hook_civicrm_buildForm().
 */
function fpptaqb_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_AdditionalPayment') {
    $paymentType = $form->getVar('_paymentType');
    if ($paymentType == 'refund') {
      $contributionId = $form->_id;
      CRM_Fpptaqb_Util::alterPaymentFormForCreditmemo($form, $contributionId);
      // Set field default values.
      $form->setDefaults(['fpptaqb_is_creditmemo' => 0]);
      // Add js to place fields in the right location on the form.
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/CRM_Contribute_Form_AdditionalPayment.js');
    }
  }
  elseif ($formName == 'CRM_Financial_Form_PaymentEdit') {
    $trxnId = $form->getVar('_id');
    // ID of Contribution entity for this payment.
    $contributionId = _fpptaqb_civicrmapi('EntityFinancialTrxn', 'getValue', [
      'financial_trxn_id' => $trxnId,
      'entity_table' => "civicrm_contribution",
      'return' => 'entity_id',
    ]);

    // Values of this payment, per form storage (because we need the total_amount).
    $formValues = $form->getVar('_values');

    // Any creditmemo filed on this payment.
    $trxnCreditmemoGet = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'get', [
      'sequential' => 1,
      'financial_trxn_id' => $trxnId,
      'api.FpptaquickbooksTrxnCreditmemoLine.get' => ['creditmemo_id' => '$value.id'],
    ]);

    if (
      ($trxnCreditmemoGet['count'] == 1)
      || ($formValues['total_amount'] < 0 )
    ) {
      // If this payment is a refund (i.e., the amount is negative, OR it already
      // is marked for creditmemo processin) then add relevant creditmemo fields to the form.
      CRM_Fpptaqb_Util::alterPaymentFormForCreditmemo($form, $contributionId);
      // Also add js to place fields in the right location on the form.
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/CRM_Financial_Form_PaymentEdit.js');
    }

    if ($trxnCreditmemoGet['count'] == 1) {
      // If it's marked as a creditmemo, we'll set default field values and a
      // message about the sync status of the creditmemo.

      $trxnCreditmemo = $trxnCreditmemoGet['values'][0];

      // Determine and set default values.
      $defaults = [
        'fpptaqb_is_creditmemo' => 1,
        'fpptaqb_creditmemo_doc_number' => $trxnCreditmemo['quickbooks_doc_number'],
        'fpptaqb_creditmemo_customer_memo' => $trxnCreditmemo['quickbooks_customer_memo'],
      ];
      foreach ($trxnCreditmemo['api.FpptaquickbooksTrxnCreditmemoLine.get']['values'] as $trxnCreditmemoLineValue) {
        $defaults['fpptaqb_line_ft_' . $trxnCreditmemoLineValue['ft_id']] = $trxnCreditmemoLineValue['total_amount'];
      }
      $form->setDefaults($defaults);

      // Define a status message indicating sync status.
      $syncMessage = '';
      if ($trxnCreditmemo['quickbooks_id'] > 0) {
        // Creditmemo has already been synced.
        $url = "https://app.qbo.intuit.com/app/creditmemo?txnId={$trxnCreditmemo['quickbooks_id']}";
        $syncMessage = E::ts('This credit memo has already been synced to QuickBooks (<a href="%1" target="_blank">trxnid=%2</a>) and cannot be edited here.', [
          '1' => $url,
          '2' => $trxnCreditmemo['quickbooks_id'],
        ]);
        // freeze creditmemo form elements; we can't allow them to be edited, since
        // the credtimemo has alrady been synced to quickbooks.
        foreach (array_keys($defaults) as $elementName) {
          $form->getElement($elementName)->freeze();
        }
      }
      else {
        // Credit memo not synced yet.
        $syncMessage = E::ts('This credit memo has not yet been synced to QuickBooks.');
      }
      CRM_Core_Resources::singleton()->addVars('fpptaqb', [
        'syncMessage' => $syncMessage
      ]);
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
  elseif ($formName == 'CRM_Financial_Form_PaymentEdit') {
    // Save creditmemo values appropriately when editing of credit memo details
    // on edit of refund, if cm has not already been synced to qb.
    $trxnId = $form->getVar('_id');
    // Get any creditmemo filed on this payment.
    $trxnCreditmemoGet = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'get', [
      'sequential' => 1,
      'financial_trxn_id' => $trxnId
    ]);
    if ($trxnCreditmemoGet['count']) {
      // Store any existing values in this creditmemo, before we delete it.
      $existingCreditmemo = $trxnCreditmemoGet['values'][0];
      // We have a creditmemo on record. If it hasn't been synced, we'll delete it,
      // and below we'll create a brand new cm record, if called for with the
      // submitted values
      // Determine if credimemo has been synced to qb.
      $isSynced = (($trxnCreditmemoGet['values'][0]['quickbooks_id'] ?? 0) > 0);
      if ($isSynced) {
        // If it's already synced, we don't want to save anything about this creditmemo,
        // so just return.
        return;
      }
      else  {
        // If not yet synced, we'll save appropriate values. But rather than trying to
        // update the credimemo and any creditmemolines individually, we'll just
        // delete the creditmemo (which will cascade delete any creditmemolines) and
        // then re-create them below.
        $trxnCreditmemoDelete= _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'delete', [
          'id' => $trxnCreditmemoGet['id'],
        ]);
      }
    }
    // If we're still here, it means no creditmemo exists for this payment (either
    // it didn't exist from the start, or we deleted it above.) So if requested
    // to do so in this form, go ahead and save it.
    $submitValues = $form->_submitValues;
    if ($submitValues['fpptaqb_is_creditmemo']) {
      // Define parameters for our FpptaquickbooksTrxnCreditmemo.create api call.
      $creditmemoParams = [
        'financial_trxn_id' => $trxnId,
        'quickbooks_doc_number' => $submitValues['fpptaqb_creditmemo_doc_number'],
        'quickbooks_customer_memo' => $submitValues['fpptaqb_creditmemo_customer_memo'],
      ];
      if ($existingCreditmemo) {
        // retain existing value of 'quickbooks_id' because it may be NULL ('on hold')
        $creditmemoParams['quickbooks_id'] = ($existingCreditmemo['quickbooks_id'] === NULL ? 'null' : $existingCreditmemo['quickbooks_id']);
      }
      $lineFinancialtypeAmounts = CRM_Fpptaqb_Utils_Creditmemo::composeLinesFromFormValues($submitValues);
      CRM_Fpptaqb_Utils_Creditmemo::createCreditmemoWithLines($creditmemoParams, $lineFinancialtypeAmounts);
    }
    // In core, this form does not redirect to itself; it only reloads. As a result,
    // $form properties persist (because there's no 'reset=1' url query parameter).
    // Under this model, Because we're adding our own fields and default values in
    // buildForm hook,these values persist on the form, which causes display of old
    // data in the form. To avoide this, we'll set the form detsination to the
    // original entryurl of the form. (BTW, this isn't relevant in most cases for the
    // civicrm ui, because in most cases this form apepears in a dialog; that's probably
    // why core devs didn't bother to set the redirect properly. However, this situation
    // does matter in the case that the you open the Edit Payment form in a new tab
    // instead of in the default dialog display.)
    $form->controller->_destination = $form->controller->_entryURL;
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
    && (!$apiRequest['params']['id'])
    && (($apiRequest['params']['fpptaqb_is_creditmemo'] ?? 0) == 1)
  ) {
    // On payment.create where fpptaqb_is_creditmemo, and id=NULL (we're not updating a payment),
    // add wrappers to create a creditmemo entry after payment creation.
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
    'fpptaqbstepthrucreditmemo' => ['load', 'sync', 'hold'],
    'fpptaqbbatchsyncinvoices' => ['process'],
    'fpptaqbbatchsyncpayments' => ['process'],
    'fpptaqbbatchsynccreditmemos' => ['process'],
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
