<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbStepthruCreditmemo.Sync API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_stepthru_creditmemo_Sync_spec(&$spec) {
  $spec['id'] = [
    'title' => 'Financial Trxn ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => true,
  ];
  $spec['hash'] = [
    'title' => 'Validation hash',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => true,
  ];
}

/**
 * FpptaqbStepthruCreditmemo.Sync API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_fpptaqb_stepthru_creditmemo_Sync($params) {
  $id = CRM_Fpptaqb_Utils_Creditmemo::validateId($params['id'], TRUE);
  $extraParams = ['values' => $params];

  if ($id === FALSE) {
    return CRM_Fpptaqb_Util::composeApiError('Could not find credit memo with transaction id '. $params['id'], 'fpptaqb-404', $extraParams);
  }

  if ($params['hash'] != CRM_Fpptaqb_Util::getHashBypassString()) {
    $currentHash = CRM_Fpptaqb_Utils_Creditmemo::getHash($id);
    if ($params['hash'] != $currentHash) {
      return CRM_Fpptaqb_Util::composeApiError('This credit memo transaction has changed since you viewed it. Please reload it before continuing.', 'fpptaqb-409', $extraParams);
    }
  }
  
  try {
    $qbPmtId = CRM_Fpptaqb_Utils_Creditmemo::sync($id);
  }
  catch (CRM_Core_Exception $e) {
    if ($e->getErrorCode()) {
      $errorCode = 'fpptaqb-' . $e->getErrorCode();
      $errorMessage = $e->getMessage();
    }
    else {
      $errorCode = 'fpptaqb-500';
      $errorMessage = "Unknown error: " . $e->getMessage();
    }
    return CRM_Fpptaqb_Util::composeApiError($errorMessage, $errorCode, $extraParams);
  }

  $returnValues = array(
    // OK, return several data rows
    'id' => $id,
    'text' => "Created QuickBooks credit memo id=$qbPmtId",
    'statusCode' => 201,
    'statistics' => CRM_Fpptaqb_Utils_Creditmemo::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruCreditmemo', 'Sync');
}
