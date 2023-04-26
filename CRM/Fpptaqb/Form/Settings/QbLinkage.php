<?php

use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Fpptaqb_Form_Settings_QbLinkage extends CRM_Fpptaqb_Form_Settings {

  public function buildQuickForm() {
    parent::buildQuickForm();

    $QBCredentials = CRM_Fpptaqb_APIHelper::getQuickBooksCredentials();
    $isRefreshTokenExpired = CRM_Fpptaqb_APIHelper::isTokenExpired($QBCredentials, TRUE);
    $this->assign('isRefreshTokenExpired', $isRefreshTokenExpired);

    $redirectUrl = '';
    if ((!empty($QBCredentials['clientID']) && !empty($QBCredentials['clientSecret']) && empty($QBCredentials['accessToken']) && empty($QBCredentials['refreshToken']) && empty($QBCredentials['realMId'])) || $isRefreshTokenExpired) {
      $redirectUrl = str_replace("&amp;", "&", CRM_Utils_System::url("civicrm/fpptaqb/OAuth", NULL, TRUE, NULL));
    }
    $this->assign('redirect_url', $redirectUrl);

    $showClientKeysMessage = TRUE;
    if (!empty($QBCredentials['clientID']) && !empty($QBCredentials['clientSecret'])) {
      $showClientKeysMessage = FALSE;
    }
    $this->assign('showClientKeysMessage', $showClientKeysMessage);
    
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   */
  public function saveSettings() {
    $settings = $this->_settings;
    $values = array_intersect_key($this->_submittedValues, $settings);

    // Compare new values to saved values for clientID and clientSecret, and note
    // whether either one has changed.
    $previousCredentials = CRM_Fpptaqb_APIHelper::getQuickBooksCredentials();
    $clientIDChanged = ($previousCredentials['clientID'] ?? NULL) != $values['fpptaqb_quickbooks_consumer_key'];
    $clientSecretChanged = ($previousCredentials['clientSecret'] ?? NULL) != $values['fpptaqb_quickbooks_shared_secret'];

    parent::saveSettings();

    // If clientID or clientSecret chaned, invalidate anything that depended on the old Client ID or Shared Secret
    if ($clientIDChanged || $clientSecretChanged) {
      civicrm_api3(
        'setting', 'create', array(
          "fpptaqb_quickbooks_access_token" => '',
          "fpptaqb_quickbooks_refresh_token" => '',
          "fpptaqb_quickbooks_realmId" => '',
          "fpptaqb_quickbooks_access_token_expiryDate" => '',
          "fpptaqb_quickbooks_refresh_token_expiryDate" => '',
        )
      );
    }
  }

}
