<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * FpptaqbBatchSyncInvoices.Process API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_fpptaqb_batch_sync_invoices_Process_spec(&$spec) {
}

/**
 * FpptaqbBatchSyncInvoices.Process API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_fpptaqb_batch_sync_invoices_Process($params) {
  try {
    // Before we do anything, try to connect to the sync. If this fails, it should
    // throw an exception, which we will of course catch here and then return an
    // error array; that error should be logged by api wrappers as defined in 
    // fpptaqb_civicrm_apiWrappers().
    // In any case, if an exception is thrown here, no further processing will
    // happen.
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $sync = new CRM_Fpptaqb_Sync_Mock();
    $accounts = $sync->fetchActiveAccountsList();
    
    
    $ids = [];
    while ($nextId = CRM_Fpptaqb_Utils_Invoice::getReadyToSyncIdNext()) {
      $ids[] = $nextId;
      try {
        $nextEntity = CRM_Fpptaqb_Utils_Invoice::getReadyToSync($nextId);
        // get hash from $nextEntity.
        // call fpptaqb_stepthru_invoice . Sync api with $nextId and hash to perform sync
        _fpptaqb_civicrmapi($entity, $action, $params);
      }
      catch (Exception $e) {
        // if anything goes wrong, use the hold api to place this invoice on hold,
        // and give the log_reason as whatever is in the $e->errorMessage().
        // Then move on to the next invoice.
      }
    }
    
    // ALTERNATIVE: $returnValues = []; // OK, success
    // ALTERNATIVE: $returnValues = ["Some value"]; // OK, return a single value

    // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
    return civicrm_api3_create_success($ids, $params, 'FpptaqbBatchSyncInvoices', 'Process');
  }
  catch (Exception $e) {
    return CRM_Fpptaqb_Util::composeApiError($e->getMessage(), $e->getCode(), ['values' => $params, 'next' => $next]);
  }
}
