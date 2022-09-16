<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaquickbooksContributionInvoice.create API specification (optional).
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaquickbooks_contribution_invoice_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * FpptaquickbooksContributionInvoice.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 */
function civicrm_api3_fpptaquickbooks_contribution_invoice_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'FpptaquickbooksContributionInvoice');
}

/**
 * FpptaquickbooksContributionInvoice.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 */
function civicrm_api3_fpptaquickbooks_contribution_invoice_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * FpptaquickbooksContributionInvoice.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 */
function civicrm_api3_fpptaquickbooks_contribution_invoice_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, 'FpptaquickbooksContributionInvoice');
}
