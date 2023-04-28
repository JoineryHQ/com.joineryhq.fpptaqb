<?php

use QuickBooksOnline\API\Core\HttpClients\FaultHandler;
use CRM_Fpptaqb_ExtensionUtil as E;
require E::path('vendor/autoload.php');


class CRM_Fpptaqb_APIHelper {

  private static $quickBooksDataService = NULL; //Data service object for login

  private static $quickBooksAccountingDataService = NULL; // Data service object for accounting and company info retrieval

  /**
   * Generate random State token to verify the Access token on redirection.
   *
   * @param $length
   * @param string $keyspace
   *
   * @return string
   */
  public static function generateStateToken($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
      $pieces[] = $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
  }

  /**
   * Generate dataservice object to verify and login into QuickBooks
   *
   * @return \QuickBooksOnline\API\DataService\DataService|null
   */
  public static function getLoginDataServiceObject() {

    if (self::$quickBooksDataService) {
      return self::$quickBooksDataService;
    }

    $redirectUrl = self::getRedirectUrl();
    $stateTokenValue = self::generateStateToken(40);

    $clientID = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_consumer_key"));
    $clientSecret = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_shared_secret"));
    $logLocation = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_log_dir"));
    $logActivated = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_activate_qbo_logging"));


    $stateToken = array(
      'state_token' => $stateTokenValue,
    );
    Civi::settings()->set('fpptaqb_quickbooks_state_token', $stateTokenValue);

    self::$quickBooksDataService = \QuickBooksOnline\API\DataService\DataService::Configure(array(
      'auth_mode' => 'oauth2',
      'ClientID' => $clientID,
      'ClientSecret' => $clientSecret,
      'RedirectURI' => $redirectUrl,
      'scope' => "com.intuit.quickbooks.accounting",
      'response_type' => 'code',
      'state' => json_encode($stateToken),
  ));

    self::$quickBooksDataService->setLogLocation($logLocation);
    if (!$logActivated) {
        self::$quickBooksDataService->disableLog();
    }

    return self::$quickBooksDataService;
  }

  /**
   * Generates data service object for accounting into QuickBooks.
   *
   * @return \QuickBooksOnline\API\DataService\DataService|null
   */
  public static function getAccountingDataServiceObject($forRefreshToken = FALSE) {
    if (!$forRefreshToken) {
      self::refreshAccessTokenIfRequired();
    }

    if (self::$quickBooksAccountingDataService && !$forRefreshToken) {
      return self::$quickBooksAccountingDataService;
    }

    $QBCredentials = self::getQuickBooksCredentials();
    $logLocation = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_log_dir"));
    $logActivated = civicrm_api3('Setting', 'getvalue', array('name' => "fpptaqb_quickbooks_activate_qbo_logging"));

    $dataServiceParams = array(
      'auth_mode' => 'oauth2',
      'ClientID' => $QBCredentials['clientID'],
      'ClientSecret' => $QBCredentials['clientSecret'],
      'accessTokenKey' => $QBCredentials['accessToken'],
      'refreshTokenKey' => $QBCredentials['refreshToken'],
      'QBORealmID' => $QBCredentials['realMId'],
      'baseUrl' => "Production",
    );

    if ($forRefreshToken) {
      unset($dataServiceParams['accessTokenKey']);
    }

    $dataService = \QuickBooksOnline\API\DataService\DataService::Configure($dataServiceParams);

    $dataService->setLogLocation($logLocation);
    if (!$logActivated) {
        $dataService->disableLog();
    }

    if (!$forRefreshToken) {
      self::$quickBooksAccountingDataService = $dataService;
      return self::$quickBooksAccountingDataService;
    }

    return $dataService;
  }

  /**
   * Get redirection URL for OAuth request.
   *
   * @return mixed
   */
  private static function getRedirectUrl() {
    return str_replace("&amp;", "&", CRM_Utils_System::url("civicrm/fpptaqb/OAuth", NULL, TRUE, NULL));
  }

  /**
   * Refresh QuickBooks access token if required.
   *
   */
  private static function refreshAccessTokenIfRequired() {
    $QBCredentials = self::getQuickBooksCredentials();
    $now = new DateTime();
    $now->modify("-5 minutes");

    $isAccessTokenExpired = self::isTokenExpired($QBCredentials);
    $isRefreshTokenExpired = self::isTokenExpired($QBCredentials, TRUE);

    if ($isAccessTokenExpired || $isRefreshTokenExpired) {
      $dataService = self::getAccountingDataServiceObject(TRUE);

      try {
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

        if ($isRefreshTokenExpired) {
          $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($QBCredentials['refreshToken']);
        }
        else {
          $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();
        }
        $tokenExpiresIn = new DateTime();
        $tokenExpiresIn->modify("+" . $refreshedAccessTokenObj->getAccessTokenValidationPeriodInSeconds() . "seconds");

        $refreshTokenExpiresIn = new DateTime();
        $refreshTokenExpiresIn->modify("+" . $refreshedAccessTokenObj->getRefreshTokenValidationPeriodInSeconds() . "seconds");

        $accessToken = $refreshedAccessTokenObj->getAccessToken();
        $refreshToken = $refreshedAccessTokenObj->getRefreshToken();

        civicrm_api3('Setting', 'create', array(
          'fpptaqb_quickbooks_access_token' => $accessToken,
          'fpptaqb_quickbooks_refresh_token' => $refreshToken,
          'fpptaqb_quickbooks_access_token_expiryDate' => $tokenExpiresIn->format("Y-m-d H:i:s"),
          'fpptaqb_quickbooks_refresh_token_expiryDate' => $refreshTokenExpiresIn->format("Y-m-d H:i:s"),
        ));

      } catch (\QuickBooksOnline\API\Exception\IdsException $e) {

      }
    }
  }

  /**
   * Get all required credentials to connect with QuickBooks
   *
   * @return array
   */
  public static function getQuickBooksCredentials() {
    return array(
      'clientID' => Civi::settings()->get('fpptaqb_quickbooks_consumer_key'),
      'clientSecret' => Civi::settings()->get('fpptaqb_quickbooks_shared_secret'),
      'accessToken' => Civi::settings()->get('fpptaqb_quickbooks_access_token'),
      'refreshToken' => Civi::settings()->get('fpptaqb_quickbooks_refresh_token'),
      'realMId' => Civi::settings()->get('fpptaqb_quickbooks_realmId'),
      'accessTokenExpiryDate' => Civi::settings()->get('fpptaqb_quickbooks_access_token_expiryDate'),
      'refreshTokenExpiryDate' => Civi::settings()->get('fpptaqb_quickbooks_refresh_token_expiryDate'),
    );
  }

  /**
   * Check if refresh/access token expired or not.
   *
   * @param $QBCredentials
   *
   * @return bool
   */
  public static function isTokenExpired($QBCredentials, $isRefreshToken = FALSE) {
    $tokenKey = "accessTokenExpiryDate";
    if ($isRefreshToken) {
      $tokenKey = "refreshTokenExpiryDate";
    }
    $isTokenExpired = FALSE;
    if (isset($QBCredentials[$tokenKey]) && !empty($QBCredentials[$tokenKey]) && $QBCredentials[$tokenKey] != 1) {
      $currentDateTime = new DateTime();
      $currentDateTime->modify("-5 minutes");
      $tokenExpiryDate = DateTime::createFromFormat("Y-m-d H:i:s", $QBCredentials[$tokenKey]);

      if ($currentDateTime > $tokenExpiryDate) {
        $isTokenExpired = TRUE;
      }
    }
    return $isTokenExpired;
  }

  /**
   * Check if the API credentials are authorized.
   *
   * @return bool
   */
  public static function isAuthorized() {
    $QBCredentials = CRM_Fpptaqb_APIHelper::getQuickBooksCredentials();
    if (empty($QBCredentials['accessToken'])) {
      return FALSE;
    }
    if (empty($QBCredentials['refreshToken'])) {
      return FALSE;
    }

    if (self::isTokenExpired($QBCredentials, TRUE)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Helper Function to convert faults errors saved by the SDK into something
   *   we can store in an Account* error_data
   *
   * @param FaultHandler $error_response
   *
   * @return array|string[]
   */
  public static function parseErrorResponse($error_response) {
    // Start with a blank set of error messages.
    $error_message = [];

    // Quickbooks Online API seems to always send XML error messages, even when
    // you query with JSON.  Parse into a document tree.
    $response_doc = new DOMDocument();
    if($response_doc->loadXML($error_response->getResponseBody())) {

      // Error responses wrap (at least one) Error element.
      $error_els = $response_doc->getElementsByTagName('Error');
      foreach($error_els as $error) {

        // Nice errors have a Detail element, which tends to render the possibly
        // also present Message redundant.
        if (($details = $error->getElementsByTagName('Detail'))->length > 0) {
          for ($n = 0; $n < $details->length; $n++) {
            $detail = $details->item($n);
            $error_message[] = $detail->textContent;
          }
        }
        // If there's no Detail, just use the Message
        elseif(($messages = $error->getElementsByTagName('Message'))->length > 0) {
          for ($n = 0; $n < $messages->length; $n++) {
            $message = $messages->item($n);
            $error_message[] = $message->textContent;
          }
        }
        // Finally, the error might just have text in it.
        else {
          $error_message[] =& $error->textContent;
        }
      }
    }

    // If either the response was not XML, or was not in an XML format we
    // expected, just put the code and response in as it has been.
    if(!count($error_message)){
      $error_message = [ $error_response->getHttpStatusCode() . ': [' . $error_response->getResponseBody() . ']' ];
    }

    return $error_message;
  }
}
