<?php

use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbStepthruInvoice.List API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_stepthru_invoice_List_spec(&$spec) {
}

/**
 * FpptaqbStepthruInvoice.List API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_fpptaqb_stepthru_invoice_List($params) {
  $readyToSyncIds = ($params['id'] ?? CRM_Fpptaqb_Utils_Invoice::getReadyToSyncIds());

  if (empty($readyToSyncIds)) {
    // No "ready" contribution ids were found; there must be none ready.
    // This is not an error; just inform the user.
    $text = 'There are no more items ready to be synced.';
    $statusCode = 204;
  }
  else {
    $contributionGet = _fpptaqb_civicrmapi('Contribution', 'get', [
      'id' => ['IN' => $readyToSyncIds],
      'options' => ['limit' => 0],
    ]);
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('invoices', $contributionGet['values']);
    $smarty->assign('uniqid', uniqid());
    $text = CRM_Core_Smarty::singleton()->fetch('CRM/Fpptaqb/Snippet/FpptaqbStepthruInvoice/list.tpl');
    $statusCode = 200;
  }
  $returnValues = array(
    // OK, return several data rows
    'text' => $text,
    'statusCode' => $statusCode,
    'statistics' => CRM_Fpptaqb_Utils_Invoice::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruInvoice', 'List');
}
