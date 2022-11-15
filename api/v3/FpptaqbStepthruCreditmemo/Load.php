<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbStepthruCreditmemo.Load API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_stepthru_creditmemo_Load_spec(&$spec) {
  $spec['id'] = [
    'title' => 'Financial Trxn ID',
    'type' => CRM_Utils_Type::T_INT,
  ];
}

/**
 * FpptaqbStepthruCreditmemo.Load API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception if the getReadyToSync() operation catches an exception; error code 
 *   is "fppta-{$e->getErrorCode()}" if error code is available, else 'fppta-500'.
 */
function civicrm_api3_fpptaqb_stepthru_creditmemo_Load($params) {
  $id = ($params['id'] ?? CRM_Fpptaqb_Utils_Creditmemo::getReadyToSyncIdNext());
  if (!$id) {
    // No "next" creditmemo id was found; there must be none ready.
    // This is not an error; just inform the user.
    $text = 'There are no more items ready to be synced.';
    $statusCode = 204;
  }
  else {
    try {
      $creditmemo = CRM_Fpptaqb_Utils_Creditmemo::getReadyToSync($id);
    }
    catch (CRM_Core_Exception $e) {
      $extraParams = ['values' => $params];
      if ($e->getErrorCode()) {
        throw new API_Exception($e->getMessage(), 'fpptaqb-' . $e->getErrorCode(), $extraParams);
      }
      else {
        throw new API_Exception("Unknown error: " . $e->getMessage(), 'fpptaqb-500', $extraParams);
      }
    }

    $smarty = CRM_Core_Smarty::singleton();

    $smarty->assign('creditmemo', $creditmemo);
    $text = CRM_Core_Smarty::singleton()->fetch('CRM/Fpptaqb/Snippet/FpptaqbStepthruCreditmemo/load.tpl');
    $hash = CRM_Fpptaqb_Utils_Creditmemo::getHash($id);
    $statusCode = 200;
  }
  $returnValues = array(
    // OK, return several data rows
    'id' => $id,
    'text' => $text,
    'hash' => $hash,
    'statusCode' => $statusCode,
    'statistics' => CRM_Fpptaqb_Utils_Creditmemo::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruCreditmemo', 'Load');
}
