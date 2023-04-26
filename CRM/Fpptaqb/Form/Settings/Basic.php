<?php

require_once 'CRM/Core/Form.php';
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Form controller class for extension Settings form.
 * Borrowed heavily from
 * https://github.com/eileenmcnaughton/nz.co.fuzion.civixero/blob/master/CRM/Civixero/Form/XeroSettings.php
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Fpptaqb_Form_Settings_Basic extends CRM_Fpptaqb_Form_Settings {
  /**
   * You need to write custom code for this function to validate the data in your settings fields
   */
  public function validate() {
    $errors = parent::validate();
    $fields = $this->exportValues();

    if (!empty($fields['fpptaqb_minimum_date'])) {
      $yyyymmdd = CRM_Utils_Date::customFormat($fields['fpptaqb_minimum_date'], '%Y-%m-%d');
      if ($yyyymmdd != $fields['fpptaqb_minimum_date']) {
        $thisYear = CRM_Utils_Date::getToday(NULL, 'Y');
        $this->_errors['fpptaqb_minimum_date'] = E::ts('Please specify a date in the format "YYYY-MM-DD" (e.g. "%1-12-01" for Dec. 1 this year.)', ['1' => $thisYear]);
      }
    }

    return (0 == count($this->_errors));
  }

  /**
   * X_options_callback for fpptaqb_cf_id_contribution setting.
   *
   */
  public static function getCustomFieldsContribution() {
    // Select placeholder
    $options = [
      '' => '-' . E::ts('none') . '-',
    ];
    
    $customFields = \Civi\Api4\CustomField::get()
      ->setCheckPermissions(FALSE)
      ->addWhere("data_type", '=', "ContactReference")
      ->addChain('custom_group', \Civi\Api4\CustomGroup::get()
        ->addWhere('id', '=', '$custom_group_id'),
      0)
      ->execute();
    foreach ($customFields as $customField) {
      $groupExtends = $customField['custom_group']['extends'];
      // Only add this field as an option if it meets certain criteria:
      if (
        // Field Group extends contribution.
        $groupExtends == 'Contribution'
        // Fields is filtered to organiztion
        && (stristr($customField['filter'], 'contact_type=organization') !== FALSE)
      ) {
        $options[$customField['id']] = $customField['custom_group']['title'] . ' :: ' . $customField['label'];
      }
    }
    return $options;
  }
  
  /**
   * X_options_callback for fpptaqb_cf_id_participant setting.
   *
   */
  public static function getCustomFieldsParticipant() {
    // Select placeholder
    $options = [
      '' => '-' . E::ts('none') . '-',
    ];
    
    $customFields = \Civi\Api4\CustomField::get()
      ->setCheckPermissions(FALSE)
      ->addWhere("data_type", '=', "ContactReference")
      ->addChain('custom_group', \Civi\Api4\CustomGroup::get()
        ->addWhere('id', '=', '$custom_group_id'),
      0)
      ->execute();
    foreach ($customFields as $customField) {
      $groupExtends = $customField['custom_group']['extends'];
      // Only add this field as an option if it meets certain criteria:
      if (
        // Field Group extends participant.
        $groupExtends == 'Participant'
        // Fields is filtered to organiztion
        && (stristr($customField['filter'], 'contact_type=organization') !== FALSE)
      ) {
        $options[$customField['id']] = $customField['custom_group']['title'] . ' :: ' . $customField['label'];
      }
    }
    return $options;
  }

  public static function getSettingsAccountOptions() {
    try {
      return CRM_Fpptaqb_Utils_Quickbooks::getAccountOptions();
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Error getting Account options for '. E::ts('QuickBook account for Payments') . ' field: ' . $e->getMessage(), E::ts('Error'), 'error');
      return [];
    }
  }

}

