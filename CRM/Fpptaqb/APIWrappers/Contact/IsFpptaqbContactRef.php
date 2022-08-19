<?php

class CRM_Fpptaqb_APIWrappers_Contact_IsFpptaqbContactRef implements API_Wrapper {

  /**
   * Conditionally changes contact_type parameter for the API request.
   */
  public function fromApiInput($apiRequest) {

    // Ensure we're searching by organization_name and not by sort_name.
    $apiRequest['params']['organization_name'] = ['LIKE' => '%' . $apiRequest['params']['term'] . '%'];
    unset($apiRequest['params']['sort_name']);
    
    // Increase count and set sorting so toApiOutput() can do something useful with duplicates.
    $defaultCount = Civi::settings()->get('search_autocomplete_count');
    $apiRequest['params']['rowCount'] = ($defaultCount * 5);
    $apiRequest['params']['options']['sort'] = "organization_name, contact_id";
    return $apiRequest;
  }

  /**
   * Munges the result before returning it to the caller.
   */
  public function toApiOutput($apiRequest, $result) {
    // strip duplicates and limit to Civi::settings()->get('search_autocomplete_count');
    $defaultCount = Civi::settings()->get('search_autocomplete_count');
    $dedupedValues = [];
    $foundNames = [];
    foreach ($result['values'] as $id => $value) {
      $organizationName = $value['organization_name'];
      if (!in_array($organizationName, $foundNames)) {
        $foundNames[] = $organizationName;
        $dedupedValues[$id] = $value;
        if (count($dedupedValues) >= $defaultCount) {
          // If we've reached the max number of results, stop here.
          break;
        }
      }
    }
    // Replace actual results with deduped results.
    $result['values'] = $dedupedValues;
    return $result;
  }
}