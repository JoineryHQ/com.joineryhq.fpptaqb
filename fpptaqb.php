<?php

require_once 'fpptaqb.civix.php';
// phpcs:disable
use CRM_Fpptaqb_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_fpptaqbhelper_settings().
 */
function fpptaqb_civicrm_fpptaqbhelper_settings(&$settingsGroups) {
  $settingsGroups[] = 'fpptaqb';
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission/
 */
function fpptaqb_civicrm_permission(&$permissions) {
  $permissions['fpptaqb: administer quickbooks configuration'] = [
    ts('FPPTA QuickBooks: administer configuration'),                     // label
    null,  // description
  ];  
  $permissions['fpptaqb: sync to quickbooks'] = [
    ts('FPPTA QuickBooks: sync data to QuickBooks'),                     // label
    null,  // description
  ];  
  _fpptaqb_civix_civicrm_config($config);
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
