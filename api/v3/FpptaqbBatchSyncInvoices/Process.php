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
    $accounts = $sync->fetchActiveAccountsList();
    
    
    $syncedIds = $heldIds = [];
    while ($nextId = CRM_Fpptaqb_Utils_Invoice::getReadyToSyncIdNext()) {
      try {
        // We'll use the stepthru api to sync because it logs to our log table;
        // it also requires the hash, so we'll get the hash from the utility
        // method. (If we used the stepthru api to load the contribution, it
        // would also get us the hash, but it would also needlessly log this
        // unattended action.)
        $hash = CRM_Fpptaqb_Utils_Invoice::getHash($nextId);
        // Now we have the hash and the id, we can sync.
        $sync = _fpptaqb_civicrmapi('FpptaqbStepthruInvoice', 'sync', [
          'id' => $nextId,
          'hash' => $hash,
        ]);
        $syncedIds[] = $nextId;
      }
      catch (Exception $e) {
        // if anything goes wrong, use the hold api to place this invoice on hold,
        // and give the log_reason as whatever is in the $e->errorMessage().
        // Then move on to the next invoice.
        $holdReason = $e->getMessage();
        try {
          $hold = _fpptaqb_civicrmapi('FpptaqbStepthruInvoice', 'hold', [
           'id' => $nextId,
           'log_reason' => 'Error encountered in sync: '. $holdReason,
         ]);
        }
        catch (Exception $e) {
          throw new CRM_Fpptaqb_Exception("Error in trying to hold contribution $nextId (hold reason was: $holdReason): " . $e->getMessage(), '500');
        }
        $heldIds[] = $nextId;
      }
    }
    return civicrm_api3_create_success('synced: '. json_encode($syncedIds) .  '; held: ' . json_encode($heldIds), $params, 'FpptaqbBatchSyncInvoices', 'Process');
  }
  catch (Exception $e) {
    return CRM_Fpptaqb_Util::composeApiError('synced: '. json_encode($syncedIds) .  '; held: ' . json_encode($heldIds) . '; ended with error: '. $e->getMessage(), $e->getCode(), ['values' => $params, 'next' => $next]);
  }
}
