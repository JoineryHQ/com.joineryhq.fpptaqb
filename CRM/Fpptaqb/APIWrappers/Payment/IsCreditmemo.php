<?php

class CRM_Fpptaqb_APIWrappers_Payment_IsCreditmemo implements API_Wrapper {

  /**
   * Do nothing to api input.
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * After creation of a refund flagged as credit memo, use given API parameters
   * (which will almost certainly have been added only by the buildForm hook
   * on the CRM_Contribute_Form_AdditionalPayment form) to create a creditmemo
   * and creditmemoLines.
   */
  public function toApiOutput($apiRequest, $result) {
    // We should only be here if this is a refund flagged as a credit memo,
    // but double-check that here just to be sure.
    $params = $apiRequest['params'];
    if (
      ($trxnId = $result['id'])
      && $params['fpptaqb_is_creditmemo']
    ) {
      // Define parameters for our FpptaquickbooksTrxnCreditmemo.create api call.
      $creditmemoParams = [
        'financial_trxn_id' => $trxnId,
        'quickbooks_doc_number' => $params['fpptaqb_creditmemo_doc_number'],
        'quickbooks_customer_memo' => $params['fpptaqb_creditmemo_customer_memo'],
      ];
      $lineFinancialtypeAmounts = CRM_Fpptaqb_Utils_Creditmemo::composeLinesFromFormValues($params);
      CRM_Fpptaqb_Utils_Creditmemo::createCreditmemoWithLines($creditmemoParams, $lineFinancialtypeAmounts);
    }
    return $result;    
  }
}