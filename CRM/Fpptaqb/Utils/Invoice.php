<?php

// phpcs:disable
use CRM_Fpptaqb_ExtensionUtil as E;
// phpcs:enable

class CRM_Fpptaqb_Utils_Invoice {

  /**
   * Get a list of IDs for contributions which are ready to be synced.
   *
   * @return Array
   */
  public static function getReadyToSyncIds() {
    static $ids;
    if (!isset($ids)) {
      $ids = [];
      $query = "
        SELECT ctrb.id
        FROM civicrm_contribution ctrb
          LEFT JOIN civicrm_fpptaquickbooks_contribution_invoice fci ON fci.contribution_id = ctrb.id
        WHERE
          ctrb.receive_date >= %1
          AND ctrb.receive_date <= (NOW() - INTERVAL %2 DAY)
          AND fci.id IS NULL
        ORDER BY
          ctrb.receive_date, ctrb.id
      ";
      $queryParams = [
        '1' => [CRM_Utils_Date::isoToMysql(Civi::settings()->get('fpptaqb_minimum_date')), 'Int'],
        '2' => [CRM_Utils_Date::isoToMysql(Civi::settings()->get('fpptaqb_sync_wait_days')), 'Int'],
      ];
      $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
      $ids = CRM_Utils_Array::collect('id', $dao->fetchAll());
    }
    return $ids;
  }

  /**
   * For all contributions which are ready to be synced, get the first available one.
   *
   * @return Int
   */
  public static function getReadyToSyncIdNext() {
    $ids = self::getReadyToSyncIds();
    return $ids[0];
  }

  /**
   * For a given contribution ID, get an array of all relevant properties for syncing.
   *
   * @return Array
   * @throws CRM_Fpptaqb_Exception with code 404 if contribution can't be found
   * @throws CRM_Fpptaqb_Exception with code 503 if contribution contains a non-zero 
   *   line-item for which a corresponding QB item can't be determined.
   * @throws CRM_Fpptaqb_Exception with code 503 if the contribution can't be 
   *   attributed to an organization contact.
   */
  public static function getReadyToSync(int $contributionId) {
    static $cache = [];
    if (!isset($cache[$contributionId])) {
      $contributionCount = _fpptaqb_civicrmapi('Contribution', 'getCount', [
        'id' => $contributionId,
      ]);

      if (!$contributionCount) {
        throw new CRM_Fpptaqb_Exception('Contribution not found', 404);
      }

      $lineItemsGet = _fpptaqb_civicrmapi('LineItem', 'get', [
        'sequential' => 1,
        'contribution_id' => $contributionId,
        'api.FinancialType.get' => ['return' => ["name"]],
      ]);
      $lineItems = $lineItemsGet['values'];
      $qbLineItems = [];
      foreach ($lineItems as &$lineItem) {
        $lineItem['financialType'] = $lineItem['api.FinancialType.get']['values'][0]['name'];
        $financialTypeId = $lineItem['api.FinancialType.get']['values'][0]['id'];
        if ((float)$lineItem['line_total'] > 0) {
          $lineItem['qbItemDetails'] = CRM_Fpptaqb_Utils_Quickbooks::getItemDetails($financialTypeId);
          $qbLineItems[] = $lineItem;
        }
        else {
          $lineItem['qbItemDetails'] = CRM_Fpptaqb_Utils_Quickbooks::getNullItem();
        }

        // If we have no qbItem, it means this is a non-zero line item, and that
        // no corresponding active qbItem was found, so we can't proceed. Throw
        // an exception.
        if (empty($lineItem['qbItemDetails'])) {
          throw new CRM_Fpptaqb_Exception(E::ts('QuickBooks item not found for financial type: %1; is the Financial Type properly configured?', ['%1' => $lineItem['financialType']]), 503);
        }
      }
      $contribution = _fpptaqb_civicrmapi('Contribution', 'getSingle', [
        'id' => $contributionId,
      ]);
      $organizationCid = self::getAttributedContactId($contributionId);
      if (!$organizationCid) {
        throw new CRM_Fpptaqb_Exception(E::ts('Could not identify an attributed organization for contribution id=%1', ['%1' => $contributionId]), 503);
      } 
      $qbCustomerId = CRM_Fpptaqb_Utils_Quickbooks::getCustomerIdForContact($organizationCid);
      $qbCustomerDetails = CRM_Fpptaqb_Utils_Quickbooks::getCustomerDetails($qbCustomerId);
      $contribution += [
        'organizationCid' => $organizationCid,
        'organizationName' => _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]),
        'qbCustomerName' => $qbCustomerDetails['DisplayName'],
        'qbCustomerId' => $qbCustomerId,
        'qbInvNumber' => CRM_Fpptaqb_Utils_Quickbooks::prepInvNumber($contribution['invoice_number']),
        'lineItems' => $lineItems,
        'qbNote' => self::composeQbNote($contributionId),
        'qbLineItems' => CRM_Fpptaqb_Utils_Quickbooks::consolidateLineItems($qbLineItems),
      ];

      $cache[$contributionId] = $contribution;
    }
    return $cache[$contributionId];
  }

  /**
   * For a given contribution ID, get an array of all relevant properties for listing
   * in "Review Held Items".
   *
   * @return Array
   */
  public static function getHeldItem(int $contributionId) {
    static $cache = [];
    if (!isset($cache[$contributionId])) {
      $contributionCount = _fpptaqb_civicrmapi('Contribution', 'getCount', [
        'id' => $contributionId,
      ]);

      if (!$contributionCount) {
        $cache[$contributionId]['error'] = E::ts('Contribution not found');
        return $cache[$contributionId];
      }

      $contribution = _fpptaqb_civicrmapi('Contribution', 'getSingle', [
        'id' => $contributionId,
      ]);
      $organizationCid = self::getAttributedContactId($contributionId);
      if ($organizationCid) {
        $organizationName = _fpptaqb_civicrmapi('Contact', 'getValue', [
          'id' => $organizationCid,
          'return' => 'display_name',
        ]);
      }
      else {
        $organizationName = E::ts('ERROR: NONE FOUND');
      }
      $contribution += [
        'organizationCid' => $organizationCid,
        'organizationName' => $organizationName,
      ];

      $cache[$contributionId] = $contribution;
    }
    return $cache[$contributionId];
  }

  /**
   * Get a list of IDs for all contributions marked to be held out from syncing.
   *
   * @return Array
   */
  public static function getHeldIds() {
    static $ids;
    if (!isset($ids)) {
      $ids = [];
      $query = "
        SELECT ctrb.id
        FROM civicrm_contribution ctrb
          INNER JOIN civicrm_fpptaquickbooks_contribution_invoice fci ON fci.contribution_id = ctrb.id
        WHERE
          fci.quickbooks_id IS NULL
        ORDER BY
          ctrb.receive_date, ctrb.id
      ";
      $dao = CRM_Core_DAO::executeQuery($query);
      $ids = CRM_Utils_Array::collect('id', $dao->fetchAll());
    }
    return $ids;
  }

  /**
   * For a given contribution id, check that the contribution exists.
   *
   * @param int $id
   *
   * @return boolean|int FALSE if not valid; otherwise the given $id.
   */
  public static function validateId($id) {
    $count = _fpptaqb_civicrmapi('Contribution', 'getCount', [
      'id' => $id,
    ]);
    if ($count) {
      return $id;
    }
    else {
      return FALSE;
    }
  }

  /**
   * For a given contribution id, mark it on hold.
   *
   * @param int $contributionId
   *
   * @return void
   */
  public static function hold(int $contributionId) {
    // Log the contribution-invoice connection
    $result = _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'create', [
      'contribution_id' => $contributionId,
      'quickbooks_id' => 'null',
    ]);
  }

  /**
   * For a given contribution id, compose a formatted note for the QuickBooks invoice.
   */
  public static function composeQbNote(int $contributionId) {
    $contactNamesByType = CRM_Fpptaqb_Utils_Invoice::getRelatedContactNames($contributionId);
    $contactNameRows = [];
    foreach ($contactNamesByType as $type => $contactNames) {
      $contactNameRows[] = $type . ': ' . implode(', ', $contactNames);
    }
    return "CiviCRM Contribution ID: {$contributionId}\n" . implode("\n", $contactNameRows);
  }

  public static function getHash($id) {
    $contribution = self::getReadyToSync($id);
    return sha1(json_encode($contribution));
  }

  /**
   * For a given contributionId, return the contact ID of the contact to whom
   * the contribution should be attributed (per the field specified in 
   * settings fpptaqb_cf_id_contribution or fpptaqb_cf_id_participant)
   * 
   * @param int $contributionId
   * 
   * @return int | NULL if none found.
   */
  public static function getAttributedContactId($contributionId) {    
    $contribution = _fpptaqb_civicrmapi('Contribution', 'getSingle', ['id' => $contributionId]);
    $contributionOrgCustomFieldId = Civi::settings()->get('fpptaqb_cf_id_contribution');
    // Return the org attributed for this contribution, if any.
    $contributionOrgCid = ($contribution['custom_' . $contributionOrgCustomFieldId] ?? NULL);
    if ($contributionOrgCid) {
      return $contributionOrgCid;
    }
    
    // If we're still here, that means no "attributed organization" value was set 
    // on the contribution. Perhaps it's a participant payment, so we'll check the
    // participant record for an "attributed organization".
    $participantOrgCustomFieldId = Civi::settings()->get('fpptaqb_cf_id_participant');
    $participantPaymentGet = _fpptaqb_civicrmapi('participantPayment', 'get', [
      'sequential' => TRUE,
      'contribution_id' => $contributionId,
      'api.Participant.get' => [],
    ]);
    $participantOrgCid = ($participantPaymentGet['values'][0]['api.Participant.get']['values'][0]['custom_' . $participantOrgCustomFieldId . '_id'] ?? NULL);
    if ($participantOrgCid) {
      return $participantOrgCid;
    }

    // If we're still here, that means we're still looking. Perhaps it's a
    // contribution from an Organization contatc. In that case, use that contact.
    $contributionGet = _fpptaqb_civicrmapi('Contribution', 'get', [
      'sequential' => 1,
      'id' => $contributionId,
      'api.Contact.get' => [],
    ]);
    if (strtolower($contributionGet['values'][0]['api.Contact.get']['values'][0]['contact_type'] ?? "") == 'organization') {
      $contributionOrgCid = $contributionGet['values'][0]['api.Contact.get']['values'][0]['contact_id'];
      return $contributionOrgCid;
    }

    // If we're still here, we have no idea; just return null.
    return NULL;
  }

  /**
   * For a given contributionId, get an array of display_name for each related contact,
   * grouped by "attribution type", i.e., how they are related (e.g.,
   * participant, soft-credit-type)
   * 
   * @param type $contributionId
   * 
   * @return Array ['Human Readable Attribution Type' => [$contactId => $displayName]]
   */
  public static function getRelatedContactNames($contributionId) {
    $contactNames = [];
    // If this is a participant payment, get all names of participants on this 
    // potentially multi-person event registration.
    $participantPaymentGet = _fpptaqb_civicrmapi('ParticipantPayment', 'get', [
      'sequential' => 1,
      'contribution_id' => $contributionId,
    ]);
    $primaryParticipantId = ($participantPaymentGet['values'][0]['participant_id'] ?? NULL);
    if ($participantPaymentGet['count']) {
      // Get the primary participant record.
      $primaryParticipantGet = _fpptaqb_civicrmapi('Participant', 'get', [
        'sequential' => 1,
        'id' => $primaryParticipantId,
        'api.Contact.get' => ['return' => "display_name"],
      ]);
      $roleId = $primaryParticipantGet['values'][0]['participant_role_id'];
      $eventId = $primaryParticipantGet['values'][0]['event_id'];
      // Find out if this role is a groupReg 'nonattendee_role_id' for this event.
      $includePrimaryPartipant = TRUE;
      $groupregIsInstalled = ('installed' === CRM_Extension_System::singleton()->getManager()->getStatus('com.joineryhq.groupreg'));
      if ($groupregIsInstalled) {
        $groupregEvents = \Civi\Api4\GroupregEvent::get()
          ->setCheckPermissions(FALSE)
          ->addWhere('nonattendee_role_id', '=', $roleId)
          ->addWhere('event_id', '=', $eventId)
          ->setLimit(1)
          ->execute();
        if (($groupregEvents->count())) {
          $includePrimaryPartipant = FALSE;
        }
      }
      if ($includePrimaryPartipant) {
        $contactId = $primaryParticipantGet['values'][0]["api.Contact.get"]['values'][0]['id'];
        $displayName = $primaryParticipantGet['values'][0]["api.Contact.get"]['values'][0]['display_name'];
        $contactNames['Participant'][$contactId] = $displayName;
      }
      // Now include additional participants (assume they are all attending).
      $additionalParticipantsGet = _fpptaqb_civicrmapi('Participant', 'get', [
        'sequential' => 1,
        'registered_by_id' => $primaryParticipantId,
        'api.Contact.get' => ['return' => "display_name"],
      ]);
      if ($additionalParticipantsGet['count']) {
        foreach ($additionalParticipantsGet['values'] as $additionalParticipant) {
          $contactId = $additionalParticipant["api.Contact.get"]['values'][0]['id'];
          $displayName = $additionalParticipant["api.Contact.get"]['values'][0]['display_name'];
          $contactNames['Participant'][$contactId] = $displayName;
        }
      }
    }

    // Also append any soft-credit contacts.
    $contributionSoftGet = _fpptaqb_civicrmapi('ContributionSoft', 'get', [
      'sequential' => 1,
      'contribution_id' => $contributionId,
      'api.Contact.get' => [],
      'api.OptionValue.getSingle' => ['option_group_id' => "soft_credit_type", 'value' => '$value.soft_credit_type_id'],
    ]);
    foreach ($contributionSoftGet['values'] as $contributionSoft) {
      $softCreditType = $contributionSoft['api.OptionValue.getSingle']['label'];
      $displayName = $contributionSoft['api.Contact.get']['values'][0]['display_name'];
      $contactNames[$softCreditType][] = $displayName;
    }

    return $contactNames;
  }

  public static function sync($contributionId) {
    $contribution = self::getReadyToSync($contributionId);
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $qbInvId = $sync->pushInv($contribution);

    // Log the contribution-invoice connection
    $result = _fpptaqb_civicrmapi('FpptaquickbooksContributionInvoice', 'create', [
      'contribution_id' => $contributionId,
      'quickbooks_id' => $qbInvId,
      'is_mock' => $sync->isMock(),
    ]);

    return $qbInvId;
  }

  public static function getStepthruStatistics() {
    return [
      'countReady' => count(self::getReadyToSyncIds()),
      'countHeld' => count(self::getHeldIds()),
    ];
  }
  
  public static function getPaymentFinancialTrxnIds(int $contributionId) {
      $query = "
        select
          ft.id
        from
          civicrm_entity_financial_trxn eft
          inner join civicrm_financial_trxn ft on eft.financial_trxn_id = ft.id
        where
          ft.is_payment
          and eft.entity_table = 'civicrm_contribution'
          and eft.entity_id = %1
      ";
      $queryParams = [
        '1' => [$contributionId, 'Int'],
      ];
      $dao = CRM_Core_DAO::executeQuery($query, $queryParams);      
      $trxnIds = CRM_Utils_Array::collect('id', $dao->fetchAll());
      return $trxnIds;
  }

}
