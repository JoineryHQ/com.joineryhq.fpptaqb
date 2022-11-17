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
      // Create the creditmemo.
      $creditmemo = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemo', 'create', $creditmemoParams);
      
      // Prep to create creditmemo lines.
      $contributionId = _fpptaqb_civicrmapi('EntityFinancialTrxn', 'getvalue', [
        'return' => "entity_id",
        'entity_table' => "civicrm_contribution",
        'financial_trxn_id' => $trxnId,
      ]);
      $lineItemGet = _fpptaqb_civicrmapi('lineItem', 'get', [
        'contribution_id' => $contributionId,
        'unit_price' => ['>' => 0],
        'return' => ["financial_type_id"],
      ]);
      foreach($lineItemGet['values'] as $lineItemGetValue) {
        $financialTypeId = $lineItemGetValue['financial_type_id'];
        // Create a creditmemoLine only if a positive value was given for this financialtype.
        if ($params['fpptaqb_line_ft_'. $financialTypeId] > 0) {
          $creditmemoLine = _fpptaqb_civicrmapi('FpptaquickbooksTrxnCreditmemoLine', 'create', [
            'creditmemo_id' => $creditmemo['id'],
            'ft_id' => $financialTypeId,
            'total_amount' => $params['fpptaqb_line_ft_'. $financialTypeId],
          ]);
        }
      }
    }
    return $result;    
  }
}