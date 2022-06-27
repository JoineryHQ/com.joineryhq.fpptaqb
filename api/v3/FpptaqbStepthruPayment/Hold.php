<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbStepthruPayment.Hold API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_stepthru_payment_Hold_spec(&$spec) {
  $spec['id'] = [
    'title' => 'Trxn ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => true,
  ];
}

/**
 * FpptaqbStepthruPayment.Hold API
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
function civicrm_api3_fpptaqb_stepthru_payment_Hold($params) {
  $id = CRM_Fpptaqb_Utils_Payment::validateId($params['id']);

  if ($id === FALSE) {
    throw new API_Exception('Could not find payment with id '. $params['id'], 'fpptaqb-404');
  }
  
  try {
    CRM_Fpptaqb_Utils_Payment::hold($id);
  }
  catch (CRM_Core_Exception $e) {
    if ($e->getErrorCode()) {
      throw new API_Exception($e->getMessage(), 'fpptaqb-'. $e->getErrorCode());
    }
    else {
      throw new API_Exception("Unknown error: ". $e->getMessage(), 'fpptaqb-500');
    }
  }

  $returnValues = array(
    // OK, return several data rows
    'id' => $id,
    'text' => "Payment transaction id=$id has been placed on hold.",
    'statistics' => CRM_Fpptaqb_Utils_Payment::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruPayment', 'Hold');
}
