<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbStepthruCreditmemo.Hold API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_stepthru_creditmemo_Hold_spec(&$spec) {
  $spec['id'] = [
    'title' => 'Trxn ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => true,
  ];
}

/**
 * FpptaqbStepthruCreditmemo.Hold API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception with code fpptaqb-404 if creditmemo can't be found.
 * @throws API_Exception if the hold() operation catches an exception; error code 
 *   is "fppta-{$e->getCode()}" if error code is available, else 'fppta-500'.
 */
function civicrm_api3_fpptaqb_stepthru_creditmemo_Hold($params) {
  $id = CRM_Fpptaqb_Utils_Creditmemo::validateId($params['id']);

  if ($id === FALSE) {
    throw new API_Exception('Could not find credit memo with id '. $params['id'], 'fpptaqb-404');
  }
  
  try {
    CRM_Fpptaqb_Utils_Creditmemo::hold($id);
  }
  catch (CRM_Core_Exception $e) {
    if ($e->getCode()) {
      throw new API_Exception($e->getMessage(), 'fpptaqb-'. $e->getCode());
    }
    else {
      throw new API_Exception("Unknown error: ". $e->getMessage(), 'fpptaqb-500');
    }
  }

  // Get the financial_trxn_id so we can report that, since we've already been showing
  // it to the user in the stepthru interface.
  $trxnId = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'getValue', [
    'id' => $id,
    'return' => 'financial_trxn_id',
  ]);
  $returnValues = array(
    // OK, return several data rows
    'id' => $id,
    'text' => "Credit memo for refund id=$trxnId has been placed on hold.",
    'statistics' => CRM_Fpptaqb_Utils_Creditmemo::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruCreditmemo', 'Hold');
}
