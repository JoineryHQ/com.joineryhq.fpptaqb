<?php

require_once 'fpptaqb.civix.php';
// phpcs:disable
use CRM_Fpptaqb_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_pageRun().
 */
function fpptaqb_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
//  $page = new CRM_Financial_Page_FinancialAccount();
  if ($pageName == 'CRM_Financial_Page_FinancialAccount' && $page->getVar('_action') == CRM_Core_Action::BROWSE) {
    $smarty = CRM_Core_Smarty::singleton();
    $rows = $smarty->get_template_vars('rows');
    $a = 1;
  }
}

/**
 * Implements hook_civicrm_buildForm().
 */
function fpptaqb_civicrm_buildForm($formName, &$form) {
  if (
    $formName == "CRM_Financial_Form_FinancialAccount"
    && ($form->_defaultValues['financial_account_type_id'][0] ?? ($form->_defaultValues['financial_account_type_id'] ?? 3)) == 3
  ) {
    // For the form CRM_Financial_Form_FinancialAccount
    // when financial_account_type_id is NULL or 3, we'll add a 'quickbooks item'
    // field for linking to the correct QB item.
    // First get the list of QB Item options and add the select element.
    $options = CRM_Fpptaqb_Utils_FinancialAccount::getItemOptions();
    $form->addElement(
      'select',
      'fpptaqb_quickbooks_id',
      E::ts('QuickBooks: Linked item'),
      ['' => E::ts('- select -')] + $options,
      ['class' => 'crm-select2']
    );
    // Set a default value for this field, if possbile.    
    $defaults = [];
    $financialAccountId = $form->_id;
    if ($financialAccountId) {
      // If this is not a "create new" form:
      // Get existing link if any;
      $accountItem = civicrm_api3('FpptaquickbooksAccountItem', 'get', [
        'sequential' => TRUE,
        'financial_account_id' => $financialAccountId,
      ]);
      if ($accountItem['values']) {
        $defaults['fpptaqb_quickbooks_id'] = $accountItem['values'][0]['quickbooks_id'];
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
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/CRM_Financial_Form_FinancialAccount.js');
    $jsvars = [
      'descriptions' => [
        'fpptaqb_quickbooks_id' => 'Only relevant if Financial Account Type is "Revenue".',
      ],
    ];
    CRM_Core_Resources::singleton()->addVars('fpptaqb', $jsvars);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function fpptaqb_civicrm_postProcess($formName, $form) {
  if (
    $formName == "CRM_Financial_Form_FinancialAccount"
    && $form->_submitValues['financial_account_type_id'] == 3
  ) {
    // Upon processing the CRM_Financial_Form_FinancialAccount form for 'Revenue' accounts:
    $financialAccountId = $form->_id;
    // Get existing link if any;
    $accountItemId = NULL;
    $accountItem = civicrm_api3('FpptaquickbooksAccountItem', 'get', [
      'financial_account_id' => $financialAccountId
    ]);
    if ($accountItem['id']) {
      $accountItemId = $accountItem['id'];
    }
    // Update, create, or deletelinked account_item.
    if ($form->_submitValues['fpptaqb_quickbooks_id']) {
      // A QB item is selected, so save the link record.
      civicrm_api3('FpptaquickbooksAccountItem', 'create', [
        'id' => $accountItemId,
        'financial_account_id' => $financialAccountId,
        'quickbooks_id' => $form->_submitValues['fpptaqb_quickbooks_id'],
      ]);
    }
    else {
      // A QB item is NOT selected, so delete the link record.
      civicrm_api3('FpptaquickbooksAccountItem', 'delete', [
        'id' => $accountItemId,
      ]);
    }
  }  
}

/**
 * Implements hook_civicrm_apiWrappers().
 */
function fpptaqb_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  // The APIWrapper is conditionally registered so that it runs only when appropriate
  $loggedApiEntities = [
    'fpptaquickbooksaccountitem' => ['create'],
    'fpptaquickbookscontactcustomer' => ['create'],
    'fpptaquickbookscontributioninvoice' => ['create'],
    'fpptaquickbookstrxnpayment' => ['create'],
    'fpptaqbstepthruinvoice' => ['load', 'sync', 'hold'],
    'fpptaqbstepthrupayment' => ['load', 'sync', 'hold'],
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
 * Implements hook_civicrm_alterTemplateFile().
 */
function fpptaqb_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  if ($context == 'page' && $formName == 'CRM_Fpptaqb_Page_ItemAction') {
    $type = CRM_Utils_Request::retrieveValue('type', 'String');
    $itemaction = CRM_Utils_Request::retrieveValue('itemaction', 'String');
    if ($type === 'inv' && $itemaction === 'load') {
      $tplName = 'CRM/Fpptaqb/Snippet/FpptaqbStepthruInvoice/load.tpl';
    }
    elseif ($type === 'pmt' && $itemaction === 'load') {
      $tplName = 'CRM/Fpptaqb/Snippet/FpptaqbStepthruPayment/load.tpl';
    }
  }
}

/**
 * Implements hook_civicrm_fpptaqb_settings().
 */
function fpptaqb_civicrm_fpptaqbhelper_settings(&$settingsGroups) {
  $settingsGroups[] = 'fpptaqb';
}

/**
 * Implements hook_civicrm_validateForm().
 * 
 * This extension uses fpptaqbhelper to manage settings, so it must use this
 * hook to validate its settings in that form.
 */
function fpptaqb_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Fpptaqbhelper_Form_Settings') {
    if (!empty($fields['fpptaqb_minimum_date'])) {
      $yyyymmdd = CRM_Utils_Date::customFormat($fields['fpptaqb_minimum_date'], '%Y-%m-%d');
      if ($yyyymmdd != $fields['fpptaqb_minimum_date']) {
        $thisYear = CRM_Utils_Date::getToday(NULL, 'Y');
        $errors['fpptaqb_minimum_date'] = E::ts('Please specify a date in the format "YYYY-MM-DD" (e.g. "%1-12-01" for Dec. 1 this year.)', ['1' => $thisYear]);
      }
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
//function fpptaqb_civicrm_navigationMenu(&$menu) {
//  _fpptaqb_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _fpptaqb_civix_navigationMenu($menu);
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
  _fpptaqb_civix_insert_navigation_menu($menu, 'Contributions', array(
    'label' => E::ts('FPPTA QuickBooks Sync'),
    'name' => 'FPPTA QuickBooks Sync',
    'url' => 'civicrm/fpptaqb/stepthru',
    'permission' => 'fpptaqb_sync_to_quickbooks',
    'operator' => 'AND',
    'separator' => NULL,
    'navID' => ++$max_navID,
  ));
  _fpptaqb_civix_navigationMenu($menu);
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