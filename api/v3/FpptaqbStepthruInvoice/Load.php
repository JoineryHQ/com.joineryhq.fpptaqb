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
  ];
}

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
 */
function civicrm_api3_fpptaqb_stepthru_invoice_Load($params) {
  $id = ($params['id'] ?? CRM_Fpptaqb_Utils_Invoice::getReadyToSyncIdNext());
  $extraParams = ['values' => $params];
  $extraParams['values']['id'] = $id;

  if (!$id) {
    // No "next" contribution id was found; there must be none ready.
    // This is not an error; just inform the user.
    $text = 'There are no more items ready to be synced.';
    $statusCode = 204;
  }
  else {
    try {
      $contribution = CRM_Fpptaqb_Utils_Invoice::getReadyToSync($id);
    }
    catch (Exception $e) {
      if ($e->getCode()) {
        $errorCode = 'fpptaqb-' . $e->getCode();
        $errorMessage = $e->getMessage();
      }
      else {
        $errorCode = 'fpptaqb-500';
        $errorMessage = "Unknown error: " . $e->getMessage();
      }
      return CRM_Fpptaqb_Util::composeApiError("Inv. $id: " . $errorMessage, $errorCode, $extraParams);
    }

    $smarty = CRM_Core_Smarty::singleton();

    $smarty->assign('contribution', $contribution);
    $text = CRM_Core_Smarty::singleton()->fetch('CRM/Fpptaqb/Snippet/FpptaqbStepthruInvoice/load.tpl');
    $hash = CRM_Fpptaqb_Utils_Invoice::getHash($id);
    $statusCode = 200;
  }
  $returnValues = array(
    // OK, return several data rows
    'id' => $id,
    'text' => $text,
    'hash' => $hash,
    'statusCode' => $statusCode,
    'statistics' => CRM_Fpptaqb_Utils_Invoice::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruInvoice', 'Load');
}
