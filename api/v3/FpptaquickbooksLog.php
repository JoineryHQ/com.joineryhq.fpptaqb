<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaquickbooksLog.create API specification (optional).
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaquickbooks_log_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * FpptaquickbooksLog.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_fpptaquickbooks_log_create($params) {
  $params['contact_id'] = CRM_Core_Session::singleton()->getLoggedInContactID();
  $params['created'] = CRM_Utils_Date::currentDBDate();
  $params['unique_request_id'] = CRM_Fpptaqb_Util::getLogCallerId();
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'FpptaquickbooksLog');
}

/**
 * FpptaquickbooksLog.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_fpptaquickbooks_log_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * FpptaquickbooksLog.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_fpptaquickbooks_log_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, 'FpptaquickbooksLog');
}
