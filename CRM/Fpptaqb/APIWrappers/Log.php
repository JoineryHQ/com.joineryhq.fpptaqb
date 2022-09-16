<?php

class CRM_Fpptaqb_APIWrappers_Log  implements API_Wrapper {
  /**
   * Conditionally changes contact_type parameter for the API request.
   */
  public function fromApiInput($apiRequest) {
    // Log the beginning of the api call.
    // Note:
    //  We're only logging certain api entities, per fpptaqb_civicrm_apiWrappers().
    //  If this api call fails, self::toApiOutput() won't fire, therefore the log will have NULL for api_output.
    
    // Each api entity is expecting a relevant identifier; define that here.
    $apiEntityIdParamNames = [
      'fpptaquickbooksfinancialtypeitem' => 'financial_type_id',
      'fpptaquickbookscontactcustomer' => 'contact_id',
      'fpptaquickbookscontributioninvoice' => 'contribution_id',
      'fpptaquickbookstrxnpayment' => 'financial_trxn_id',
      'fpptaqbstepthruinvoice' => 'id',
      'fpptaqbstepthrupayment' => 'id',
    ];
    $apiEntityIdParamName = $apiEntityIdParamNames[strtolower($apiRequest['entity'])] ?? NULL;
    $entityId = $apiRequest['params'][$apiEntityIdParamName] ?? NULL;
    // Log the api call.
    $logParams = [
      'entity_id_param' => $apiEntityIdParamName,
      'entity_id' => $entityId,
      'api_entity' => $apiRequest['entity'],
      'api_action' => $apiRequest['action'],
      'api_params' => json_encode($apiRequest['params']),
      'sync_session_id' => CRM_Core_Session::singleton()->get('syncSessionId', 'fpptaqb'),
      'reason' => $apiRequest['params']['log_reason'],
    ];
    $create = _fpptaqb_civicrmapi('FpptaquickbooksLog', 'create', $logParams);

    // Store the log id in the api request itself, so we can update the log when 
    // the api request completes.
    $apiRequest['fpptaqb']['log_id'] = $create['id'];
    return $apiRequest;
  }

  /**
   * Munges the result before returning it to the caller.
   */
  public function toApiOutput($apiRequest, $result) {

    // If there's a log id, update that log entry with the api output.
    if (!empty($apiRequest['fpptaqb']['log_id'])) {
      $apiOutputText = NULL;
      if ($result['is_error']) {
        $apiOutputText = $result['error_message'];
      }
      elseif (isset($result['values']['text'])) {
        $apiOutputText = $result['values']['text'];
      }
      elseif (is_string($result['values'])) {
        $apiOutputText = $result['values'];
      }
      else {
        $apiOutputText = json_encode($result['values']);
      }

      $logParams = [
        'id' => $apiRequest['fpptaqb']['log_id'],
        'api_output' => json_encode($result),
        'api_output_text' => $apiOutputText,
        'api_output_error_code' => $result['error_code'] ?? NULL,
      ];
      _fpptaqb_civicrmapi('FpptaquickbooksLog', 'create', $logParams);
    }
    return $result;
  }
}
