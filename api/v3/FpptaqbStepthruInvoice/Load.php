<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbStepthruInvoice.Load API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_stepthru_invoice_Load_spec(&$spec) {
  $spec['id'] = [
    'title' => 'Contribution ID',
    'type' => CRM_Utils_Type::T_INT,
  ];}

/**
 * FpptaqbStepthruInvoice.Load API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_fpptaqb_stepthru_invoice_Load($params) {
  $id = ($params['id'] ?? CRM_Fpptaqb_Util::getInvToSyncIdNext());

  $extraParams = [
    'id' => $id,
  ];
  try {
    $contribution = CRM_Fpptaqb_Util::getInvToSync($id);
  }
  catch (CRM_Core_Exception $e) {
    if ($e->getErrorCode()) {
      throw new API_Exception($e->getMessage(), 'fpptaqb-'. $e->getErrorCode(), $extraParams);
    }
    else {
      throw new API_Exception("Unknown error: ". $e->getMessage(), 'fpptaqb-500', $extraParams);
    }
  }

  $smarty = CRM_Core_Smarty::singleton();
  
  $smarty->assign('contribution', $contribution);
  $text = CRM_Core_Smarty::singleton()->fetch('CRM/Fpptaqb/Snippet/FpptaqbStepthruInvoice/load.tpl');
  $returnValues = array(
    // OK, return several data rows
    'id' => $id,
    'text' => $text,
    'hash' => CRM_Fpptaqb_Util::getContributionHash($id),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruInvoice', 'Load');
}