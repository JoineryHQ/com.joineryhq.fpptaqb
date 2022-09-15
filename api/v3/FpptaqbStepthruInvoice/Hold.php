<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbStepthruInvoice.Hold API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_stepthru_invoice_Hold_spec(&$spec) {
  $spec['id'] = [
    'title' => 'Contribution ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => true,
  ];
}

/**
 * FpptaqbStepthruInvoice.Hold API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_fpptaqb_stepthru_invoice_Hold($params) {
  $id = CRM_Fpptaqb_Utils_Invoice::validateId($params['id']);
  $extraParams = ['values' => $params];

  if ($id === FALSE) {
    return CRM_Fpptaqb_Util::composeApiError('Could not find contribution with id '. $params['id'], 'fpptaqb-404', $extraParams);
  }
  
  try {
    CRM_Fpptaqb_Utils_Invoice::hold($id);
  }
  catch (Exception $e) {
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
    'text' => "Contribution id=$id has been placed on hold.",
    'statistics' => CRM_Fpptaqb_Utils_Invoice::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruInvoice', 'Hold');
}
