<?php

/** Load CiviX ExtensionUtil class and bundled autoload resolver. **/
use CRM_Fpptaqb_ExtensionUtil as E;

require E::path('vendor/autoload.php');

/**
 * @class CRM_Fpptaqb_Page_OAuthQBO
 * Page for completing OAuth 2 handshaking.
 **/
class CRM_Fpptaqb_Page_OAuthQBO extends CRM_Core_Page {

  private $consumer_key;

  private $shared_secret;

  private $output;

  /**
   * Get Login Helper to get Redirection URL and Access/Refresh Tokens.
   *
   * @return \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper
   * @throws CiviCRM_API3_Exception
   * @throws \QuickBooksOnline\API\Exception\SdkException
   */
  private function getLoginHelper() {
    $dataService = CRM_Fpptaqb_APIHelper::getLoginDataServiceObject();
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    return $OAuth2LoginHelper;
  }

  /**
   * Redirect from CiviCRM to QuickBooks for App authorization.
   *
   * @throws CiviCRM_API3_Exception
   * @throws \QuickBooksOnline\API\Exception\SdkException
   */
  private function redirectForAuth() {
    $OAuth2LoginHelper = $this->getLoginHelper();
    $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
    CRM_Utils_System::redirect($authorizationCodeUrl);
  }

  public function run() {
    //get current value in the database
    $this->consumer_key = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_consumer_key"));
    $this->shared_secret = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_shared_secret"));

    //the initial value of Client ID and Client Secret is empty string, need to check if they have been set
    if ($this->consumer_key === 0 || $this->consumer_key == '' || !isset($this->consumer_key)) {
      throw new Exception("Initial Client ID is NOT set!", 1);
    }
    if ($this->shared_secret === 0 || $this->shared_secret == '' || !isset($this->shared_secret)) {
      throw new Exception("Initial Client Secret is NOT set!", 1);
    }

    $doRedirectForAuth = TRUE;

    // Check if its a request from QuickBooks after redirection.
    if (isset($_GET['state']) && isset($_GET['code']) && isset($_GET['realmId'])) {
      $stateToken = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_state_token"));;
      $state = $_GET['state'];
      $state = json_decode($state, TRUE);

      // Check if provided state token is received back in request.
      // If verified no need to redirect.

      if (isset($state['state_token']) && $state['state_token'] == $stateToken) {
        $doRedirectForAuth = FALSE;

        $code = $_GET['code'];
        $realmId = $_GET['realmId'];

        // Get same login helper used in generating Redirection URL.
        $OAuth2LoginHelper = $this->getLoginHelper();

        try {
          // Get Access token object using received code and RealMID.
          $accessTokenObject = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);

          $refreshToken = $accessTokenObject->getRefreshToken();
          $refreshTokenExpiresIn = $accessTokenObject->getRefreshTokenExpiresAt();
          $tokenExpiresIn = $accessTokenObject->getAccessTokenExpiresAt();
          $accessToken = $accessTokenObject->getAccessToken();

          // Expiry date received in Y/m/d H:i:s format from QuickBooks
          $refreshTokenExpiresIn = DateTime::createFromFormat('Y/m/d H:i:s', $refreshTokenExpiresIn);
          $tokenExpiresIn = DateTime::createFromFormat('Y/m/d H:i:s', $tokenExpiresIn);

          // Save all the required settings.
          civicrm_api3('Setting', 'create', array(
            'fpptaqb_quickbooks_access_token' => $accessToken,
            'fpptaqb_quickbooks_refresh_token' => $refreshToken,
            'fpptaqb_quickbooks_realmId' => $realmId,
            'fpptaqb_quickbooks_access_token_expiryDate' => $tokenExpiresIn->format("Y-m-d H:i:s"),
            'fpptaqb_quickbooks_refresh_token_expiryDate' => $refreshTokenExpiresIn->format("Y-m-d H:i:s"),
          ));

          // Successfully tokens and Company details stored in database.
          $this->output = array(
            'message' => "Access token info retrieved and stored successfully!",
            'redirect_url' => '<a href="' . str_replace("&amp;", "&", CRM_Utils_System::url("civicrm/admin/fpptaqb/settings", 'reset=1', TRUE, NULL)) . '">Click here to go back to FPPTA Quickbooks settings page to see the new expiry date of your new access token and key</a>',
          );

        } catch (\QuickBooksOnline\API\Exception\IdsException $e) {
          // Exception while interacting with QuickBooks
          // Output an error with try again message.

          $this->output = array(
            'message' => $e->getMessage(),
            'redirect_url' => '<a href="' . str_replace("&amp;", "&", CRM_Utils_System::url("civicrm/admin/fpptaqb/settings", 'reset=1', TRUE, NULL)) . '">Click here to go back to FPPTA Quickbooks settings page and try again.</a>',
          );
        }
      }
    }

    // Check if error is returned from QuickBooks redirection (e.g. User cancelled the Auth step)
    if (isset($_GET['error'])) {
      $doRedirectForAuth = FALSE;
      $error = $_GET['error'];
      if ($error == "access_denied") {
        // Output error if User denied the access.
        $this->output = array(
          'message' => 'You\'ve not authorize the request. Please authorize it to sync CiviCRM with QuickBooks',
          'redirect_url' => '<a href="' . str_replace("&amp;", "&", CRM_Utils_System::url("civicrm/admin/fpptaqb/settings", 'reset=1', TRUE, NULL)) . '">Click here to go back to FPPTA Quickbooks settings page to authorise the App</a>',
        );
      }
    }

    // If first request without error/code, redirect user for Auth.
    if ($doRedirectForAuth) {
      $this->output = array('message' => 'Authorizing...');
      $this->redirectForAuth();
    }

    $this->assign('output', $this->output);

    parent::run();
  }

}
