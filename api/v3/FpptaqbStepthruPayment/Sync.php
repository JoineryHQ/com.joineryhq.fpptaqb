<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbStepthruPayment.Sync API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_stepthru_payment_Sync_spec(&$spec) {
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
 * FpptaqbStepthruPayment.Sync API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception with code 'fppta-404', if the contribution cannot be found.
 * @throws API_Exception with code 'fppta-409', the contribution hash has changed.
 * @throws API_Exception if the sync() operation catches an exception; error code 
 *   is "fppta-{$e->getErrorCode()}" if error code is available, else 'fppta-500'.
 */
function civicrm_api3_fpptaqb_stepthru_payment_Sync($params) {
  $id = CRM_Fpptaqb_Utils_Payment::validateId($params['id']);

  if ($id === FALSE) {
    throw new API_Exception('Could not find payment with transaction id '. $params['id'], 'fpptaqb-404');
  }

  if ($params['hash'] != CRM_Fpptaqb_Utils_Payment::getHash($id)) {
    throw new API_Exception('This payment transaction has changed since you viewed it. Please reload it before continuing.', 'fpptaqb-409');
  }
  
  try {
    $qbPmtId = CRM_Fpptaqb_Utils_Payment::sync($id);
  }
  catch (CRM_Core_Exception $e) {
    $extraParams = ['values' => $params];
    if ($e->getErrorCode()) {
      throw new API_Exception($e->getMessage(), 'fpptaqb-'. $e->getErrorCode(), $extraParams);
    }
    else {
      throw new API_Exception("Unknown error: ". $e->getMessage(), 'fpptaqb-500', $extraParams);
    }
  }

  $returnValues = array(
    // OK, return several data rows
    'id' => $id,
    'text' => "Created QuickBooks payment id=$qbPmtId",
    'statusCode' => 201,
    'statistics' => CRM_Fpptaqb_Utils_Payment::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruPayment', 'Sync');
}
