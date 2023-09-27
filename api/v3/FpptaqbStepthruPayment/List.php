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
function _civicrm_api3_fpptaqb_stepthru_payment_List_spec(&$spec) {
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
function civicrm_api3_fpptaqb_stepthru_payment_List($params) {
  $readyToSyncIds = ($params['id'] ?? CRM_Fpptaqb_Utils_Payment::getReadyToSyncIds());

  if (empty($readyToSyncIds)) {
    // No "ready" contribution ids were found; there must be none ready.
    // This is not an error; just inform the user.
    $text = 'There are no more items ready to be synced.';
    $statusCode = 204;
  }
  else {
    $financialTrxnGet = _fpptaqb_civicrmapi('FinancialTrxn', 'get', [
      'id' => ['IN' => $readyToSyncIds],
      'options' => ['limit' => 0],
    ]);
    $payments = [];
    foreach ($financialTrxnGet['values'] as $financialTrxnId => $financialTrxnValue) {
      $entityFinancialTrxnGet = civicrm_api3('EntityFinancialTrxn', 'get', [
        'sequential' => 1,
        'entity_table' => "civicrm_contribution",
        'financial_trxn_id' => $financialTrxnId,
        'api.Contribution.get' => [
          'id' => '$value.entity_id',
          'api.Contact.get' => ['id' => '$value.contact_id'],
        ],
        
      ]);
      $payments[$financialTrxnId] = $financialTrxnValue;
      $payments[$financialTrxnId]['contributionId'] = $entityFinancialTrxnGet['values'][0]['entity_id'];
      $payments[$financialTrxnId]['sort_name'] = $entityFinancialTrxnGet['values'][0]['api.Contribution.get']['values'][0]['api.Contact.get']['values'][0]['sort_name'];
      $payments[$financialTrxnId]['contactId'] = $entityFinancialTrxnGet['values'][0]['api.Contribution.get']['values'][0]['contact_id'];
      $payments[$financialTrxnId]['paymentInstrumentLabel'] = CRM_Core_PseudoConstant::getLabel('CRM_Core_BAO_FinancialTrxn', 'payment_instrument_id', $financialTrxnValue['payment_instrument_id']);
    }
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('payments', $payments);
    $smarty->assign('uniqid', uniqid());
    $text = CRM_Core_Smarty::singleton()->fetch('CRM/Fpptaqb/Snippet/FpptaqbStepthruPayment/list.tpl');
    $statusCode = 200;
  }
  $returnValues = array(
    // OK, return several data rows
    'text' => $text,
    'statusCode' => $statusCode,
    'statistics' => CRM_Fpptaqb_Utils_Invoice::getStepthruStatistics(),
  );

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'FpptaqbStepthruPayment', 'List');
}
