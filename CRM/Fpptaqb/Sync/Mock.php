<?php

/**
 * Mock QB sync class.
 */
class CRM_Fpptaqb_Sync_Mock {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   */
  private static $_singleton = NULL;

  /**
   * The constructor. Use self::singleton() to create an instance.
   */
  private function __construct() {
    
  }

  /**
   * Singleton function used to manage this object.
   *
   * @return Object
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Fpptaqb_Sync_Mock();
    }
    return self::$_singleton;
  }

  /**
   * Is this a mock sync? (It's either that, or it's live.)
   */
  public function isMock() {
    return TRUE;
  }  

  /**
   * For a given contact ID, get the QB customer number.
   * 
   * @return int
   */
  public function fetchCustomerIdForContact($contactId) {
    $dummyData = [
      $contactId => rand(1000, 9999),
    ];
    return 'MOCK-' . $dummyData[$contactId];
  }

  /**
   * For a given QuickBooks customer ID, get relevant customer details.
   * 
   * @return Array
   */
  public function fetchCustomerDetails($customerId) {
    return [
      'DisplayName' => "MOCK: Random Customer $customerId",
    ];
  }

  private static function failRandom($percent) {
    $rand = rand(1, 100);
    // Should return true around $percent % of the time.
    return ($rand <= $percent);
  }

  /**
   * Given a contribution, push it to QB via api.
   * 
   * @param Array $contribution
   *   Contribution details as built by CRM_Fpptaqb_Utils_Invoice::getInvToSync().
   */
  public function pushInv($contribution) {
    // Sometimes, fail with an error.
    if (self::failRandom(20)) {
      throw new CRM_Fpptaqb_Exception('MOCK sync: this error happens around 20% of the time.', 503);
    }

    return rand(1000, 9999);
  }
  
  /**
   * Given a contribution, push it to QB via api.
   * 
   * @param Array $payment
   *   Payment details as built by CRM_Fpptaqb_Utils_Pmt::getReadyToSync().
   */
  public function pushPmt($payment) {
    // Sometimes, fail with an error.
    if (self::failRandom(20)) {
      throw new CRM_Fpptaqb_Exception('MOCK sync: this error happens around 20% of the time.', 503);
    }

    return rand(1000, 9999);
  }
  
  public function fetchItemById($id) {
    // In LIVE sync this should probably be an actual live API query, but in
    // this mock we'll just use the static values from self::fetchActiveItemsList().
    $items = $this->fetchActiveItemsList();
    return $items[$id];
  }

  public function fetchActiveItemsList() {
    $json = '
      {
        "QueryResponse": {
         "Item": [
          {
           "Name": "1 Winter Schools",
           "Active": true,
           "FullyQualifiedName": "1 Winter Schools",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "190",
            "name": "WINTER SCHOOL - CEU"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "8",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:40-07:00",
            "LastUpdatedTime": "2021-04-02T09:44:53-07:00"
           }
          },
          {
           "Name": "4701",
           "Description": "Winter School Sponsorship",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "8",
            "name": "1 Winter Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "1 Winter Schools:4701",
           "Taxable": true,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "558",
            "name": "Deferred Memberships"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027661",
            "name": "1-Spring Virtual School"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "334",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2020-09-08T10:30:00-07:00",
            "LastUpdatedTime": "2020-09-08T10:30:00-07:00"
           }
          },
          {
           "Name": "Active Registration",
           "Sku": "4510",
           "Description": "Winter Trustee School - Active Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "8",
            "name": "1 Winter Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "1 Winter Schools:Active Registration",
           "Taxable": false,
           "UnitPrice": 850,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "191",
            "name": "Active Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027661",
            "name": "1-Spring Virtual School"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "305",
           "SyncToken": "5",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2022-01-06T07:11:44-08:00"
           }
          },
          {
           "Name": "Associate Registration",
           "Sku": "4520",
           "Description": "Winter Trustee School - Associate Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "8",
            "name": "1 Winter Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "1 Winter Schools:Associate Registration",
           "Taxable": false,
           "UnitPrice": 1050,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "192",
            "name": "Associate Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027661",
            "name": "1-Spring Virtual School"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "284",
           "SyncToken": "5",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2022-01-06T07:12:02-08:00"
           }
          },
          {
           "Name": "On-Site NEW Active Registration",
           "Description": "On-Site Winter Trustee School - Active Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "8",
            "name": "1 Winter Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "1 Winter Schools:On-Site NEW Active Registration",
           "Taxable": false,
           "UnitPrice": 950,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "191",
            "name": "Active Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027661",
            "name": "1-Spring Virtual School"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "178",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2022-01-06T07:12:40-08:00"
           }
          },
          {
           "Name": "Onsite NEW Assoc Registration",
           "Description": "On-Site Winter Trustee School - Associate Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "8",
            "name": "1 Winter Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "1 Winter Schools:Onsite NEW Assoc Registration",
           "Taxable": false,
           "UnitPrice": 1150,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "192",
            "name": "Associate Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027661",
            "name": "1-Spring Virtual School"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "179",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2022-01-06T07:13:11-08:00"
           }
          },
          {
           "Name": "Winter School Guest Fee",
           "Description": "Winter School Guest Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "8",
            "name": "1 Winter Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "1 Winter Schools:Winter School Guest Fee",
           "Taxable": false,
           "UnitPrice": 300,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "543",
            "name": "Guest Fee Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027661",
            "name": "1-Spring Virtual School"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "236",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2022-01-06T07:14:38-08:00"
           }
          },
          {
           "Name": "Winter School Sponsorships",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "8",
            "name": "1 Winter Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "1 Winter Schools:Winter School Sponsorships",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "234",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T11:41:17-07:00"
           }
          },
          {
           "Name": "13 Membership",
           "Active": true,
           "FullyQualifiedName": "13 Membership",
           "Taxable": true,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "182",
            "name": "MEMBERSHIP"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "6",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:40-07:00",
            "LastUpdatedTime": "2022-02-09T10:19:58-08:00"
           }
          },
          {
           "Name": "2021 Active Membership",
           "Description": "Active Membership - Pension Boards",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "6",
            "name": "13 Membership"
           },
           "Level": 1,
           "FullyQualifiedName": "13 Membership:2021 Active Membership",
           "Taxable": false,
           "UnitPrice": 620,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "183",
            "name": "Active Membership"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027637",
            "name": "6-Membership"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "189",
           "SyncToken": "8",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2022-02-09T10:19:58-08:00"
           }
          },
          {
           "Name": "2021 Associate Membership",
           "Description": "Associate Membership - Service Providers",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "6",
            "name": "13 Membership"
           },
           "Level": 1,
           "FullyQualifiedName": "13 Membership:2021 Associate Membership",
           "Taxable": false,
           "UnitPrice": 1957,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "184",
            "name": "Associate Membership"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027637",
            "name": "6-Membership"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "190",
           "SyncToken": "6",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2020-11-13T11:48:22-08:00"
           }
          },
          {
           "Name": "2022 Active Membership",
           "Description": "2022 Active Membership",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "6",
            "name": "13 Membership"
           },
           "Level": 1,
           "FullyQualifiedName": "13 Membership:2022 Active Membership",
           "Taxable": true,
           "UnitPrice": 750,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "183",
            "name": "Active Membership"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027637",
            "name": "6-Membership"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "233",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2022-02-09T10:19:59-08:00"
           }
          },
          {
           "Name": "2022 Associate Membership",
           "Description": "2022 Associate Membership",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "6",
            "name": "13 Membership"
           },
           "Level": 1,
           "FullyQualifiedName": "13 Membership:2022 Associate Membership",
           "Taxable": true,
           "UnitPrice": 2100,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "184",
            "name": "Associate Membership"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027637",
            "name": "6-Membership"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "232",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2022-02-09T10:22:22-08:00"
           }
          },
          {
           "Name": "2 Fall Schools",
           "Active": true,
           "FullyQualifiedName": "2 Fall Schools",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "190",
            "name": "WINTER SCHOOL - CEU"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "12",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:40-07:00",
            "LastUpdatedTime": "2020-10-05T10:43:14-07:00"
           }
          },
          {
           "Name": "Active On-Site Registration",
           "Description": "2019 On-Site Fall Trustees School  Active Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Active On-Site Registration",
           "Taxable": false,
           "UnitPrice": 670,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "191",
            "name": "Active Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "61",
           "SyncToken": "5",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2020-10-09T13:47:47-07:00"
           }
          },
          {
           "Name": "Active Registration",
           "Description": "2021 Fall Trustees School Active Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Active Registration",
           "Taxable": false,
           "UnitPrice": 750,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "582",
            "name": "Active Registration Fall TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "316",
           "SyncToken": "5",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2021-08-31T11:01:52-07:00"
           }
          },
          {
           "Name": "Fall Refund Active Registration",
           "Description": "REFUND Active Registration Winter Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "316",
            "name": "2 Fall Schools:Active Registration"
           },
           "Level": 2,
           "FullyQualifiedName": "2 Fall Schools:Active Registration:Fall Refund Active Registration",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "582",
            "name": "Active Registration Fall TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "54",
           "SyncToken": "5",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-08-07T11:55:22-07:00"
           }
          },
          {
           "Name": "Associate On-Site Registration",
           "Description": "On-Site Fall Trustees School  Associate Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Associate On-Site Registration",
           "Taxable": false,
           "UnitPrice": 927,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "195",
            "name": "Assoc Registration Fall TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "64",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-12-27T11:10:34-08:00"
           }
          },
          {
           "Name": "Associate Registration",
           "Description": "2021 Fall School Associate Registration",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Associate Registration",
           "Taxable": false,
           "UnitPrice": 950,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "195",
            "name": "Assoc Registration Fall TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "244",
           "SyncToken": "4",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2021-08-31T11:02:48-07:00"
           }
          },
          {
           "Name": "Fall School Sponsorships",
           "Description": "Fall School Sponsorships",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Fall School Sponsorships",
           "Taxable": true,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "235",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2020-10-07T08:40:50-07:00"
           }
          },
          {
           "Name": "Guest Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Guest Fee",
           "Taxable": false,
           "UnitPrice": 150,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "592",
            "name": "Fall School Guest Fee"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "237",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2021-08-31T11:29:34-07:00"
           }
          },
          {
           "Name": "Pivot Registration",
           "Description": "Pivot 2020 attendee regsitrattion",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Pivot Registration",
           "Taxable": false,
           "UnitPrice": 250,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "624",
            "name": "Pivot Registration"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "335",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2020-10-05T09:24:00-07:00",
            "LastUpdatedTime": "2020-10-07T09:04:32-07:00"
           }
          },
          {
           "Name": "Pivot Sponsorship",
           "Description": "Pivot 2020 Sponsor",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Pivot Sponsorship",
           "Taxable": false,
           "UnitPrice": 2000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "625",
            "name": "Pivot Sponsorship"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "336",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2020-10-05T10:43:14-07:00",
            "LastUpdatedTime": "2020-10-07T09:05:23-07:00"
           }
          },
          {
           "Name": "Refund Active Registration",
           "Description": "REFUND Fall Trustees School Active Registration",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Refund Active Registration",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "582",
            "name": "Active Registration Fall TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "62",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-08-07T11:58:28-07:00"
           }
          },
          {
           "Name": "Active Refund - Full",
           "Description": "Full REFUND of Fall Trustees School  Active Registration due to special circumstances",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "62",
            "name": "2 Fall Schools:Refund Active Registration"
           },
           "Level": 2,
           "FullyQualifiedName": "2 Fall Schools:Refund Active Registration:Active Refund - Full",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "582",
            "name": "Active Registration Fall TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "151",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-08-07T11:58:56-07:00"
           }
          },
          {
           "Name": "Refund Associate Registration",
           "Description": "REFUND Fall Trustees School Associate Registration",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Refund Associate Registration",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "195",
            "name": "Assoc Registration Fall TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "63",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-12-27T11:10:34-08:00"
           }
          },
          {
           "Name": "Speaker Sponsorship TS",
           "Description": "Speaker Fee sponsor for TS",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "12",
            "name": "2 Fall Schools"
           },
           "Level": 1,
           "FullyQualifiedName": "2 Fall Schools:Speaker Sponsorship TS",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "402",
            "name": "Speakers Fees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "ClassRef": {
            "value": "1400000000001027638",
            "name": "2-Fall TS"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "208",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-05-06T10:32:58-07:00"
           }
          },
          {
           "Name": "2021 Deferred Membership",
           "Sku": "332",
           "Active": true,
           "FullyQualifiedName": "2021 Deferred Membership",
           "Taxable": false,
           "UnitPrice": 620,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "558",
            "name": "Deferred Memberships"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027637",
            "name": "6-Membership"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "338",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2020-11-16T07:29:34-08:00",
            "LastUpdatedTime": "2020-11-16T07:29:34-08:00"
           }
          },
          {
           "Name": "2022 re-cert fees",
           "Active": true,
           "FullyQualifiedName": "2022 re-cert fees",
           "Taxable": true,
           "UnitPrice": 31,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "187",
            "name": "CPPT INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027641",
            "name": "3-CPPT"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "347",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2021-11-22T08:25:14-08:00",
            "LastUpdatedTime": "2022-03-09T11:19:43-08:00"
           }
          },
          {
           "Name": "3 CPPT Program",
           "Active": true,
           "FullyQualifiedName": "3 CPPT Program",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "188",
            "name": "CPPT Application Fees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "3",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:40-07:00",
            "LastUpdatedTime": "2021-03-08T11:56:47-08:00"
           }
          },
          {
           "Name": "CPPT Annual Recertification",
           "Description": "CPPT Annual  Recertification Renewal Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "3",
            "name": "3 CPPT Program"
           },
           "Level": 1,
           "FullyQualifiedName": "3 CPPT Program:CPPT Annual Recertification",
           "Taxable": false,
           "UnitPrice": 31,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "279",
            "name": "2021 CPPT Recertification Fees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027641",
            "name": "3-CPPT"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "75",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-08-07T12:05:41-07:00"
           }
          },
          {
           "Name": "CPPT On-Site APP",
           "Description": "CPPT Certification Program On-Site Application Registration",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "3",
            "name": "3 CPPT Program"
           },
           "Level": 1,
           "FullyQualifiedName": "3 CPPT Program:CPPT On-Site APP",
           "Taxable": false,
           "UnitPrice": 1030,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "188",
            "name": "CPPT Application Fees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027641",
            "name": "3-CPPT"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "216",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-08-07T12:06:09-07:00"
           }
          },
          {
           "Name": "CPPT Program Application",
           "Description": "CPPT Certification Program Application Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "3",
            "name": "3 CPPT Program"
           },
           "Level": 1,
           "FullyQualifiedName": "3 CPPT Program:CPPT Program Application",
           "Taxable": false,
           "UnitPrice": 950,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "188",
            "name": "CPPT Application Fees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027641",
            "name": "3-CPPT"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "215",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2021-03-09T09:40:40-08:00"
           }
          },
          {
           "Name": "CPPT Refund",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "3",
            "name": "3 CPPT Program"
           },
           "Level": 1,
           "FullyQualifiedName": "3 CPPT Program:CPPT Refund",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "202",
            "name": "Refunds"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "105",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2019-05-06T11:45:31-07:00"
           }
          },
          {
           "Name": "CPPT Reinstatement Fee",
           "Description": "CPPT Reinstatement Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "3",
            "name": "3 CPPT Program"
           },
           "Level": 1,
           "FullyQualifiedName": "3 CPPT Program:CPPT Reinstatement Fee",
           "Taxable": false,
           "UnitPrice": 103,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "301",
            "name": "CPPT Reinstatement Fee"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027641",
            "name": "3-CPPT"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "92",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2019-08-07T12:06:55-07:00"
           }
          },
          {
           "Name": "Pension Fundamentals for New Trustees",
           "Sku": "450",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "3",
            "name": "3 CPPT Program"
           },
           "Level": 1,
           "FullyQualifiedName": "3 CPPT Program:Pension Fundamentals for New Trustees",
           "Taxable": false,
           "UnitPrice": 150,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "630",
            "name": "Pension Fundamentals for New Trustees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027641",
            "name": "3-CPPT"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "340",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2021-03-08T11:56:47-08:00",
            "LastUpdatedTime": "2021-03-08T11:56:47-08:00"
           }
          },
          {
           "Name": "4 Conference",
           "Active": true,
           "FullyQualifiedName": "4 Conference",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "190",
            "name": "WINTER SCHOOL - CEU"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "9",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:40-07:00",
            "LastUpdatedTime": "2021-06-21T09:08:29-07:00"
           }
          },
          {
           "Name": "4417",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:4417",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "558",
            "name": "Deferred Memberships"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027636",
            "name": "4-Conference"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "332",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-08-13T11:48:34-07:00",
            "LastUpdatedTime": "2019-08-13T11:48:34-07:00"
           }
          },
          {
           "Name": "Active Registration - copy",
           "Description": "Annual Conference Active Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Active Registration - copy",
           "Taxable": true,
           "UnitPrice": 775,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "585",
            "name": "Active Registration Conference"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027636",
            "name": "4-Conference"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "345",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2021-06-21T08:30:01-07:00",
            "LastUpdatedTime": "2021-06-21T08:30:01-07:00"
           }
          },
          {
           "Name": "Annual Conference Active Registration",
           "Description": "Annual Conference Active Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Annual Conference Active Registration",
           "Taxable": true,
           "UnitPrice": 875,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "585",
            "name": "Active Registration Conference"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027636",
            "name": "4-Conference"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "242",
           "SyncToken": "5",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2022-06-01T13:34:49-07:00"
           }
          },
          {
           "Name": "Annual Conference Associate Registration",
           "Description": "Annual Conference Associate Registration",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Annual Conference Associate Registration",
           "Taxable": true,
           "UnitPrice": 1150,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "586",
            "name": "Associate Reg Conference"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027636",
            "name": "4-Conference"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "56",
           "SyncToken": "6",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2022-06-01T13:36:04-07:00"
           }
          },
          {
           "Name": "Conference Sponsorships",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Conference Sponsorships",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "238",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T12:57:27-07:00"
           }
          },
          {
           "Name": "Dawna",
           "Description": "Annual Conference Active Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Dawna",
           "Taxable": true,
           "UnitPrice": 775,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "585",
            "name": "Active Registration Conference"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027636",
            "name": "4-Conference"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "346",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2021-06-21T09:08:29-07:00",
            "LastUpdatedTime": "2021-06-21T09:08:29-07:00"
           }
          },
          {
           "Name": "Expo Booth",
           "Description": "Exhibit Booth - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Expo Booth",
           "Taxable": true,
           "UnitPrice": 1600,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "199",
            "name": "Expo Booth"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "11",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:40-07:00",
            "LastUpdatedTime": "2021-06-03T07:16:25-07:00"
           }
          },
          {
           "Name": "Guest Fee Annual Conference",
           "Sku": "4411",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Guest Fee Annual Conference",
           "Taxable": true,
           "UnitPrice": 300,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "584",
            "name": "Guest Fee Annual Conference"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027636",
            "name": "4-Conference"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "239",
           "SyncToken": "7",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2022-02-17T11:37:14-08:00"
           }
          },
          {
           "Name": "Onsite Active Reg",
           "Description": "On-Site Annual Conference Active  Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Onsite Active Reg",
           "Taxable": false,
           "UnitPrice": 700,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "191",
            "name": "Active Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "39",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-05-06T11:19:06-07:00"
           }
          },
          {
           "Name": "Onsite Associate Registration",
           "Description": "On-Site Annual Conference Associate Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:Onsite Associate Registration",
           "Taxable": false,
           "UnitPrice": 950,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "192",
            "name": "Associate Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "57",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-05-06T11:19:49-07:00"
           }
          },
          {
           "Name": "REFUND Active Registration",
           "Description": "REFUND for Annual Conference Active Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:REFUND Active Registration",
           "Taxable": false,
           "UnitPrice": 600,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "202",
            "name": "Refunds"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "60",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-05-06T11:45:50-07:00"
           }
          },
          {
           "Name": "REFUND ASSOC REG",
           "Description": "REFUND for Annual Conference Associate Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "9",
            "name": "4 Conference"
           },
           "Level": 1,
           "FullyQualifiedName": "4 Conference:REFUND ASSOC REG",
           "Taxable": false,
           "UnitPrice": 850,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "202",
            "name": "Refunds"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "59",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-05-06T11:46:03-07:00"
           }
          },
          {
           "Name": "5 Golf Classic Tournament",
           "Description": "Annual  Charitable Golf Classic",
           "Active": true,
           "FullyQualifiedName": "5 Golf Classic Tournament",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "200",
            "name": "Golf Tournament Greens Fees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "81",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Golf Classic Sponsorships",
           "Description": "Annual Golf Classic Sponsorship Opportunities",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "81",
            "name": "5 Golf Classic Tournament"
           },
           "Level": 1,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Classic Sponsorships",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "82",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-05-06T10:39:19-07:00"
           }
          },
          {
           "Name": "Sponsor -  Closest to Pin",
           "Description": "Annual  Golf Classic: Closest to the Pin Sponsorship",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "82",
            "name": "5 Golf Classic Tournament:Golf Classic Sponsorships"
           },
           "Level": 2,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Classic Sponsorships:Sponsor -  Closest to Pin",
           "Taxable": true,
           "UnitPrice": 670,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "295",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2021-06-03T07:17:16-07:00"
           }
          },
          {
           "Name": "Sponsor - Golf Breakfast",
           "Description": "Annual Golf Classic Sunday Continental Breakfast Sponsorship",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "82",
            "name": "5 Golf Classic Tournament:Golf Classic Sponsorships"
           },
           "Level": 2,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Classic Sponsorships:Sponsor - Golf Breakfast",
           "Taxable": true,
           "UnitPrice": 670,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "262",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2021-06-03T07:17:32-07:00"
           }
          },
          {
           "Name": "Sponsor - Golf Hole",
           "Description": "Annual Golf Classic Hole Sponsorship",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "82",
            "name": "5 Golf Classic Tournament:Golf Classic Sponsorships"
           },
           "Level": 2,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Classic Sponsorships:Sponsor - Golf Hole",
           "Taxable": true,
           "UnitPrice": 670,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "587",
            "name": "Golf Tournament Sponsors"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "293",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2021-07-28T11:27:44-07:00"
           }
          },
          {
           "Name": "Sponsor - Golf Hole Package",
           "Description": "Annual Golf Classic: Golf Hole Package - Sponsorship ( + $400 FOURSOME)",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "82",
            "name": "5 Golf Classic Tournament:Golf Classic Sponsorships"
           },
           "Level": 2,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Classic Sponsorships:Sponsor - Golf Hole Package",
           "Taxable": true,
           "UnitPrice": 1050,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "587",
            "name": "Golf Tournament Sponsors"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "311",
           "SyncToken": "4",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2021-07-28T11:01:58-07:00"
           }
          },
          {
           "Name": "Sponsor - Golf Longest Drive",
           "Description": "Annual Golf Classic: Longest Drive Sponsorship",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "82",
            "name": "5 Golf Classic Tournament:Golf Classic Sponsorships"
           },
           "Level": 2,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Classic Sponsorships:Sponsor - Golf Longest Drive",
           "Taxable": false,
           "UnitPrice": 650,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "264",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T10:39:55-07:00"
           }
          },
          {
           "Name": "Sponsor - Golf Shirts",
           "Description": "Charitable Golf Classic Golf Shirt Sponsor",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "82",
            "name": "5 Golf Classic Tournament:Golf Classic Sponsorships"
           },
           "Level": 2,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Classic Sponsorships:Sponsor - Golf Shirts",
           "Taxable": true,
           "UnitPrice": 2000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "265",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2021-06-03T07:18:10-07:00"
           }
          },
          {
           "Name": "Golf Greens Fee",
           "Description": "Charitable Golf Classic Greens Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "81",
            "name": "5 Golf Classic Tournament"
           },
           "Level": 1,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Greens Fee",
           "Taxable": false,
           "UnitPrice": 100,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "200",
            "name": "Golf Tournament Greens Fees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "294",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-05-06T10:38:40-07:00"
           }
          },
          {
           "Name": "Golf Foursome",
           "Description": "Annual Golf Classic Foursome Greens Fees",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "294",
            "name": "5 Golf Classic Tournament:Golf Greens Fee"
           },
           "Level": 2,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Greens Fee:Golf Foursome",
           "Taxable": false,
           "UnitPrice": 400,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "200",
            "name": "Golf Tournament Greens Fees"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "319",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-05-06T10:38:48-07:00"
           }
          },
          {
           "Name": "Golf Refund",
           "Description": "Refund Gold Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "81",
            "name": "5 Golf Classic Tournament"
           },
           "Level": 1,
           "FullyQualifiedName": "5 Golf Classic Tournament:Golf Refund",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "202",
            "name": "Refunds"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "ClassRef": {
            "value": "1400000000001027648",
            "name": "5-Golf"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "106",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2019-05-06T10:38:58-07:00"
           }
          },
          {
           "Name": "7 NYSE TRIP",
           "Active": true,
           "FullyQualifiedName": "7 NYSE TRIP",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "192",
            "name": "Associate Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "18",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:40-07:00",
            "LastUpdatedTime": "2019-08-13T11:41:11-07:00"
           }
          },
          {
           "Name": "4116",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:4116",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "558",
            "name": "Deferred Memberships"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027635",
            "name": "7- NYSE"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "331",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-08-13T11:41:11-07:00",
            "LastUpdatedTime": "2019-08-13T11:41:11-07:00"
           }
          },
          {
           "Name": "NYSE Co-Sponsor",
           "Description": "Annual CEU CPPT Wall Street Program Co-Sponsorship",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:NYSE Co-Sponsor",
           "Taxable": false,
           "UnitPrice": 6695,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027635",
            "name": "7- NYSE"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "177",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-08-07T12:09:32-07:00"
           }
          },
          {
           "Name": "NYSE Guest",
           "Description": "2018 CPPT NYSE Guest Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:NYSE Guest",
           "Taxable": false,
           "UnitPrice": 400,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "192",
            "name": "Associate Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "218",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T11:23:49-07:00"
           }
          },
          {
           "Name": "NYSE SCHOLARSHIP",
           "Description": "Annual CPPT NYSE Trip Scholarship Winner and Guest",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:NYSE SCHOLARSHIP",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "191",
            "name": "Active Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "184",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-05-06T12:59:19-07:00"
           }
          },
          {
           "Name": "NYSE Trustee",
           "Description": "2018 CPPT NYSE Trustee Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:NYSE Trustee",
           "Taxable": false,
           "UnitPrice": 800,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "192",
            "name": "Associate Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "217",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T13:11:22-07:00"
           }
          },
          {
           "Name": "Prepaid NYSE Co-Sponsor",
           "Description": "Prepaid Annual CEU CPPT Wall Street Program Co-Sponsorship",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:Prepaid NYSE Co-Sponsor",
           "Taxable": false,
           "UnitPrice": 6500,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "34",
            "name": "DEFERRED INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "227",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T13:10:38-07:00"
           }
          },
          {
           "Name": "Prepaid NYSE Guest",
           "Description": "2018 CPPT NYSE Prepaid Guest Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:Prepaid NYSE Guest",
           "Taxable": false,
           "UnitPrice": 400,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "34",
            "name": "DEFERRED INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "206",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-05-06T11:26:01-07:00"
           }
          },
          {
           "Name": "Prepaid NYSE Trustee",
           "Description": "2018 CPPT CEU Wall Street Program Prepaid Trustee Registration Fee",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:Prepaid NYSE Trustee",
           "Taxable": false,
           "UnitPrice": 800,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "34",
            "name": "DEFERRED INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "205",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-05-06T11:24:48-07:00"
           }
          },
          {
           "Name": "REFUND NYSE Guest Reg.",
           "Description": "Refund of CPPT NYSE Trip Guest Registration Fees",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:REFUND NYSE Guest Reg.",
           "Taxable": false,
           "UnitPrice": 200,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "192",
            "name": "Associate Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "84",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-05-06T11:25:09-07:00"
           }
          },
          {
           "Name": "REFUND NYSE Trustee Reg.",
           "Description": "Refund of CPPT NYSE trip Trustee Registration Fees",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:REFUND NYSE Trustee Reg.",
           "Taxable": false,
           "UnitPrice": 550,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "543",
            "name": "Guest Fee Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "55",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:41-07:00",
            "LastUpdatedTime": "2019-05-06T11:25:30-07:00"
           }
          },
          {
           "Name": "Refund Prepaid NYSE Guest",
           "Description": "Refund CPPT NYSE Prepaid Guest Registration Fees",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:Refund Prepaid NYSE Guest",
           "Taxable": false,
           "UnitPrice": 200,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "34",
            "name": "DEFERRED INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "220",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T11:26:47-07:00"
           }
          },
          {
           "Name": "Refund Prepaid NYSE Trustee",
           "Description": "REFUND CPPT NYSE Prepaid Trustee Registration Fees",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "18",
            "name": "7 NYSE TRIP"
           },
           "Level": 1,
           "FullyQualifiedName": "7 NYSE TRIP:Refund Prepaid NYSE Trustee",
           "Taxable": false,
           "UnitPrice": 550,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "34",
            "name": "DEFERRED INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "219",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T11:27:11-07:00"
           }
          },
          {
           "Name": "9 Voice Magazine Ad",
           "Description": "Annual Voice Magazine Advertising Opportunities",
           "Active": true,
           "FullyQualifiedName": "9 Voice Magazine Ad",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "204",
            "name": "the Voice Magazine"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "17",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:40-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:44-07:00"
           }
          },
          {
           "Name": "Prepaid Voice",
           "Description": "Prepaid Voice Magazine Ad",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "17",
            "name": "9 Voice Magazine Ad"
           },
           "Level": 1,
           "FullyQualifiedName": "9 Voice Magazine Ad:Prepaid Voice",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "34",
            "name": "DEFERRED INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "214",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T11:27:59-07:00"
           }
          },
          {
           "Name": "the Voice - 1/2 Page",
           "Description": "the Voice: Half Page Ad",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "17",
            "name": "9 Voice Magazine Ad"
           },
           "Level": 1,
           "FullyQualifiedName": "9 Voice Magazine Ad:the Voice - 1/2 Page",
           "Taxable": false,
           "UnitPrice": 1000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "204",
            "name": "the Voice Magazine"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027634",
            "name": "8-TheVoice"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "136",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2021-03-09T09:41:59-08:00"
           }
          },
          {
           "Name": "the Voice - 1/4 page",
           "Description": "the Voice: Quarter Page Ad",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "17",
            "name": "9 Voice Magazine Ad"
           },
           "Level": 1,
           "FullyQualifiedName": "9 Voice Magazine Ad:the Voice - 1/4 page",
           "Taxable": false,
           "UnitPrice": 575,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "204",
            "name": "the Voice Magazine"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027634",
            "name": "8-TheVoice"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "134",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2021-03-09T09:42:53-08:00"
           }
          },
          {
           "Name": "the Voice - Business Card",
           "Description": "the Voice: Business Card Ad (3.5\"x2\")",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "17",
            "name": "9 Voice Magazine Ad"
           },
           "Level": 1,
           "FullyQualifiedName": "9 Voice Magazine Ad:the Voice - Business Card",
           "Taxable": false,
           "UnitPrice": 250,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "204",
            "name": "the Voice Magazine"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027634",
            "name": "8-TheVoice"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "138",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2021-03-09T09:43:06-08:00"
           }
          },
          {
           "Name": "the Voice - Full Page",
           "Description": "the Voice: Full Page Ad (7.5\" x 10\")",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "17",
            "name": "9 Voice Magazine Ad"
           },
           "Level": 1,
           "FullyQualifiedName": "9 Voice Magazine Ad:the Voice - Full Page",
           "Taxable": false,
           "UnitPrice": 1650,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "204",
            "name": "the Voice Magazine"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027634",
            "name": "8-TheVoice"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "137",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2021-03-09T09:43:23-08:00"
           }
          },
          {
           "Name": "the Voice - Inside Back",
           "Description": "the Voice Ad: Inside Back Cover",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "17",
            "name": "9 Voice Magazine Ad"
           },
           "Level": 1,
           "FullyQualifiedName": "9 Voice Magazine Ad:the Voice - Inside Back",
           "Taxable": false,
           "UnitPrice": 2250,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "204",
            "name": "the Voice Magazine"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027634",
            "name": "8-TheVoice"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "133",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2021-03-09T09:42:15-08:00"
           }
          },
          {
           "Name": "the Voice - Inside Front Cover",
           "Description": "the Voice Ad: Inside Front Cover",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "17",
            "name": "9 Voice Magazine Ad"
           },
           "Level": 1,
           "FullyQualifiedName": "9 Voice Magazine Ad:the Voice - Inside Front Cover",
           "Taxable": false,
           "UnitPrice": 2060,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "204",
            "name": "the Voice Magazine"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027634",
            "name": "8-TheVoice"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "135",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2019-08-07T12:14:00-07:00"
           }
          },
          {
           "Name": "the Voice - Outside Back Cover",
           "Description": "the Voice Ad: Outside Back Cover",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "17",
            "name": "9 Voice Magazine Ad"
           },
           "Level": 1,
           "FullyQualifiedName": "9 Voice Magazine Ad:the Voice - Outside Back Cover",
           "Taxable": false,
           "UnitPrice": 2500,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "204",
            "name": "the Voice Magazine"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "139",
           "SyncToken": "2",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:42-07:00",
            "LastUpdatedTime": "2021-03-09T09:42:29-08:00"
           }
          },
          {
           "Name": "AR Clean up",
           "Description": "write-off Per Cindy",
           "Active": true,
           "FullyQualifiedName": "AR Clean up",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "99",
            "name": "Suspense"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027632",
            "name": "0-OPERATING"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "344",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2021-04-17T10:48:52-07:00",
            "LastUpdatedTime": "2021-04-17T10:48:52-07:00"
           }
          },
          {
           "Name": "AR Clean up Write off",
           "Active": true,
           "FullyQualifiedName": "AR Clean up Write off",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "PurchaseDesc": "write-off Per Cindy",
           "PurchaseCost": 0,
           "ExpenseAccountRef": {
            "value": "99",
            "name": "Suspense"
           },
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027632",
            "name": "0-OPERATING"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "343",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2021-04-17T10:30:32-07:00",
            "LastUpdatedTime": "2021-04-17T10:45:00-07:00"
           }
          },
          {
           "Name": "Deferred 2022 Membership",
           "Sku": "331",
           "Active": true,
           "FullyQualifiedName": "Deferred 2022 Membership",
           "Taxable": true,
           "UnitPrice": 750,
           "Type": "Inventory",
           "IncomeAccountRef": {
            "value": "576",
            "name": "Sales of Product Income"
           },
           "PurchaseCost": 0,
           "ExpenseAccountRef": {
            "value": "577",
            "name": "Cost of Goods Sold"
           },
           "AssetAccountRef": {
            "value": "570",
            "name": "Inventory Asset"
           },
           "TrackQtyOnHand": true,
           "QtyOnHand": -213,
           "InvStartDate": "2020-11-16",
           "TaxClassificationRef": {
            "value": "EUC-09020802-V1-00120000",
            "name": "General taxable retail products (use this if nothing else fits)"
           },
           "ClassRef": {
            "value": "1400000000001027637",
            "name": "6-Membership"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "337",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2020-11-16T07:25:43-08:00",
            "LastUpdatedTime": "2022-05-17T17:41:49-07:00"
           }
          },
          {
           "Name": "Deferred 2021 Winter School",
           "Sku": "4511",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "337",
            "name": "Deferred 2022 Membership"
           },
           "Level": 1,
           "FullyQualifiedName": "Deferred 2022 Membership:Deferred 2021 Winter School",
           "Taxable": true,
           "UnitPrice": 850,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "558",
            "name": "Deferred Memberships"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990202-V1-00020000",
            "name": "Service marked taxable by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1400000000001027661",
            "name": "1-Spring Virtual School"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "348",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2021-12-16T07:17:57-08:00",
            "LastUpdatedTime": "2021-12-16T07:17:57-08:00"
           }
          },
          {
           "Name": "Impact Sponsor-2 Trustee Schools",
           "Sku": "4503",
           "Description": "Impact Sponsor-2 Trustee Schools",
           "Active": true,
           "FullyQualifiedName": "Impact Sponsor-2 Trustee Schools",
           "Taxable": false,
           "UnitPrice": 4500,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "638",
            "name": "Impact Sponsor"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1800000000000262591",
            "name": "Sponsorships"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "341",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2021-03-09T11:04:29-08:00",
            "LastUpdatedTime": "2021-03-09T12:39:26-08:00"
           }
          },
          {
           "Name": "PmntDiscount_Active Registration Winter TS",
           "Active": true,
           "FullyQualifiedName": "PmntDiscount_Active Registration Winter TS",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "191",
            "name": "Active Registration Winter TS"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "330",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T08:04:46-07:00",
            "LastUpdatedTime": "2019-04-22T08:04:46-07:00"
           }
          },
          {
           "Name": "PmntDiscount_FPPTA Members Benefits",
           "Active": true,
           "FullyQualifiedName": "PmntDiscount_FPPTA Members Benefits",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "185",
            "name": "FPPTA Members Benefits"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "329",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T08:04:12-07:00",
            "LastUpdatedTime": "2019-04-22T08:04:12-07:00"
           }
          },
          {
           "Name": "Postage Reimbursable",
           "Active": true,
           "FullyQualifiedName": "Postage Reimbursable",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "512",
            "name": "Office Postage [non-event]"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "231",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:44-07:00"
           }
          },
          {
           "Name": "Postage/Shipping",
           "Active": true,
           "FullyQualifiedName": "Postage/Shipping",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "512",
            "name": "Office Postage [non-event]"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "226",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:44-07:00"
           }
          },
          {
           "Name": "PPI Webinars",
           "Description": "FPPTA Online Program Webcast",
           "Active": true,
           "FullyQualifiedName": "PPI Webinars",
           "Taxable": false,
           "UnitPrice": 500,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "488",
            "name": "PPI Webinar Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "181",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:43-07:00"
           }
          },
          {
           "Name": "Premium Sponsorship",
           "Sku": "4504",
           "Active": true,
           "FullyQualifiedName": "Premium Sponsorship",
           "Taxable": false,
           "UnitPrice": 8000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "634",
            "name": "Premium Sponsorships"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1800000000000262591",
            "name": "Sponsorships"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "339",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2021-02-11T10:14:28-08:00",
            "LastUpdatedTime": "2021-03-09T09:43:47-08:00"
           }
          },
          {
           "Name": "Relief Fund Donation",
           "Description": "Relief Fund Donation received",
           "Active": true,
           "FullyQualifiedName": "Relief Fund Donation",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "284",
            "name": "Donations - to Relief Fund"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "153",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:43-07:00"
           }
          },
          {
           "Name": "Sales",
           "Active": true,
           "FullyQualifiedName": "Sales",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "558",
            "name": "Deferred Memberships"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "327",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:46-07:00",
            "LastUpdatedTime": "2020-09-08T13:16:39-07:00"
           }
          },
          {
           "Name": "Sponsorship Bundles",
           "Description": "Sponsorship Bundle Packages",
           "Active": true,
           "FullyQualifiedName": "Sponsorship Bundles",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "191",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:44-07:00"
           }
          },
          {
           "Name": "CONF SPONSORSHIP BUNDLES",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "191",
            "name": "Sponsorship Bundles"
           },
           "Level": 1,
           "FullyQualifiedName": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "224",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Conf Diamond Bundles",
           "Description": "Diamond Sponsorship Bundle - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "224",
            "name": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES:Conf Diamond Bundles",
           "Taxable": false,
           "UnitPrice": 3500,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "272",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Conf Gold Bundles",
           "Description": "Gold Sponsorship Bundle - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "224",
            "name": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES:Conf Gold Bundles",
           "Taxable": false,
           "UnitPrice": 2000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "299",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Conf Platinum Bundles",
           "Description": "Platinum Sponsorship Bundle - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "224",
            "name": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES:Conf Platinum Bundles",
           "Taxable": false,
           "UnitPrice": 4000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "269",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Conf Silver Bundle",
           "Description": "Silver Sponsorship Bundle - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "224",
            "name": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:CONF SPONSORSHIP BUNDLES:Conf Silver Bundle",
           "Taxable": false,
           "UnitPrice": 1000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "312",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "TS SPONSORSHIP BUNDLES",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "191",
            "name": "Sponsorship Bundles"
           },
           "Level": 1,
           "FullyQualifiedName": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "225",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "PrepTS Silver Bundles",
           "Description": "Prepaid Silver Sponsorship Bundle - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "225",
            "name": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES:PrepTS Silver Bundles",
           "Taxable": false,
           "UnitPrice": 1000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "34",
            "name": "DEFERRED INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "228",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T11:39:11-07:00"
           }
          },
          {
           "Name": "TS Diamond Bundles",
           "Description": "Diamond Sponsorship Bundle - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "225",
            "name": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES:TS Diamond Bundles",
           "Taxable": false,
           "UnitPrice": 10000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "270",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "TS Gold Bundles",
           "Description": "Gold Sponsorship Bundle - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "225",
            "name": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES:TS Gold Bundles",
           "Taxable": false,
           "UnitPrice": 2000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "268",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:44-07:00"
           }
          },
          {
           "Name": "TS Platinum Bundles",
           "Description": "Platinum Sponsorship Bundle - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "225",
            "name": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES:TS Platinum Bundles",
           "Taxable": false,
           "UnitPrice": 4000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "271",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "TS Silver Bundles",
           "Description": "Silver Sponsorship Bundle - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "225",
            "name": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles:TS SPONSORSHIP BUNDLES:TS Silver Bundles",
           "Taxable": false,
           "UnitPrice": 1000,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "267",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:44-07:00"
           }
          },
          {
           "Name": "Sponsorship Bundles Discounts",
           "Active": true,
           "FullyQualifiedName": "Sponsorship Bundles Discounts",
           "Taxable": true,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "192",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:43-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:44-07:00"
           }
          },
          {
           "Name": "CONF DISCOUNT",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "192",
            "name": "Sponsorship Bundles Discounts"
           },
           "Level": 1,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:CONF DISCOUNT",
           "Taxable": true,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "222",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Conf  Diamond Discount",
           "Description": "Diamond Sponsorship Bundle Discount - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "222",
            "name": "Sponsorship Bundles Discounts:CONF DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:CONF DISCOUNT:Conf  Diamond Discount",
           "Taxable": true,
           "UnitPrice": -2138,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "274",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Conf Gold Discount",
           "Description": "Gold Sponsorship Bundle Discount - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "222",
            "name": "Sponsorship Bundles Discounts:CONF DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:CONF DISCOUNT:Conf Gold Discount",
           "Taxable": true,
           "UnitPrice": -739,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "273",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Conf Platinum Discount",
           "Description": "Platinum Sponsorship Bundle Discount - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "222",
            "name": "Sponsorship Bundles Discounts:CONF DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:CONF DISCOUNT:Conf Platinum Discount",
           "Taxable": true,
           "UnitPrice": -1084,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "300",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Conf Silver Discount",
           "Description": "Silver Sponsorship Bundle Discount - Annual Conference",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "222",
            "name": "Sponsorship Bundles Discounts:CONF DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:CONF DISCOUNT:Conf Silver Discount",
           "Taxable": true,
           "UnitPrice": -420,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "320",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "TS DISCOUNT",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "192",
            "name": "Sponsorship Bundles Discounts"
           },
           "Level": 1,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:TS DISCOUNT",
           "Taxable": true,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "223",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "PREP TS Silver Discount",
           "Description": "Prepaid Silver Sponsorship Bundle Discount - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "223",
            "name": "Sponsorship Bundles Discounts:TS DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:TS DISCOUNT:PREP TS Silver Discount",
           "Taxable": true,
           "UnitPrice": -260,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "34",
            "name": "DEFERRED INCOME"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "229",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T10:47:58-07:00"
           }
          },
          {
           "Name": "TS Diamond Discount",
           "Description": "Diamond Sponsorship Bundle Discount - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "223",
            "name": "Sponsorship Bundles Discounts:TS DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:TS DISCOUNT:TS Diamond Discount",
           "Taxable": true,
           "UnitPrice": -1315,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "313",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "TS Gold Discount",
           "Description": "Gold Sponsorship Bundle Discount - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "223",
            "name": "Sponsorship Bundles Discounts:TS DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:TS DISCOUNT:TS Gold Discount",
           "Taxable": true,
           "UnitPrice": -413,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "326",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "TS Platinum Discount",
           "Description": "Platinum Sponsorship Bundle Discount - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "223",
            "name": "Sponsorship Bundles Discounts:TS DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:TS DISCOUNT:TS Platinum Discount",
           "Taxable": true,
           "UnitPrice": -655,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "298",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "TS Silver Discount",
           "Description": "Silver Sponsorship Bundle Discount - Trustees School",
           "Active": true,
           "SubItem": true,
           "ParentRef": {
            "value": "223",
            "name": "Sponsorship Bundles Discounts:TS DISCOUNT"
           },
           "Level": 2,
           "FullyQualifiedName": "Sponsorship Bundles Discounts:TS DISCOUNT:TS Silver Discount",
           "Taxable": true,
           "UnitPrice": -260,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "203",
            "name": "NYSE Sponsor Income"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "domain": "QBO",
           "sparse": false,
           "Id": "297",
           "SyncToken": "0",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:45-07:00",
            "LastUpdatedTime": "2019-04-22T07:59:45-07:00"
           }
          },
          {
           "Name": "Virtual Learning Sponsorship",
           "Sku": "4704",
           "Active": true,
           "FullyQualifiedName": "Virtual Learning Sponsorship",
           "Taxable": false,
           "UnitPrice": 1500,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "623",
            "name": "Virtual Learning Sponsor"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "TaxClassificationRef": {
            "value": "EUC-99990201-V1-00020000",
            "name": "Service marked exempt by its seller (seller accepts full responsibility)"
           },
           "ClassRef": {
            "value": "1800000000000149139",
            "name": "11-Virtual Learning"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "333",
           "SyncToken": "3",
           "MetaData": {
            "CreateTime": "2020-07-30T10:45:39-07:00",
            "LastUpdatedTime": "2020-09-08T13:17:44-07:00"
           }
          },
          {
           "Name": "Winter School Books",
           "Active": true,
           "FullyQualifiedName": "Winter School Books",
           "Taxable": false,
           "UnitPrice": 0,
           "Type": "Service",
           "IncomeAccountRef": {
            "value": "357",
            "name": "Educational Material"
           },
           "PurchaseCost": 0,
           "TrackQtyOnHand": false,
           "ClassRef": {
            "value": "1400000000001027661",
            "name": "1-Spring Virtual School"
           },
           "domain": "QBO",
           "sparse": false,
           "Id": "230",
           "SyncToken": "1",
           "MetaData": {
            "CreateTime": "2019-04-22T07:59:44-07:00",
            "LastUpdatedTime": "2019-05-06T10:34:17-07:00"
           }
          }
         ],
         "startPosition": 1,
         "maxResults": 121
        },
        "time": "2022-07-06T07:41:05.479-07:00"
       }
      ';
      $response = json_decode($json, TRUE);
      return CRM_Utils_Array::rekey($response['QueryResponse']['Item'], 'Id');
  }

  public function fetchAccountById($id) {
    // In LIVE sync this should probably be an actual live API query, but in
    // this mock we'll just use the static values from self::fetchActiveItemsList().
    $items = $this->fetchActiveAccountsList();
    return $items[$id];
  }

  public function fetchActiveAccountsList() {
    $json = '
      {
       "QueryResponse": {
        "Account": [
         {
          "Name": "CD #2 Prime meridian",
          "SubAccount": false,
          "Description": "CD #2 Prime meridian",
          "FullyQualifiedName": "CD #2 Prime meridian",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "Savings",
          "AcctNum": "1126",
          "CurrentBalance": 51634.78,
          "CurrentBalanceWithSubAccounts": 51634.78,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "614",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-09-09T10:26:06-07:00",
           "LastUpdatedTime": "2021-04-17T12:47:02-07:00"
          }
         },
         {
          "Name": "CD-Prime Meridian",
          "SubAccount": false,
          "FullyQualifiedName": "CD-Prime Meridian",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "Checking",
          "AcctNum": "1010",
          "CurrentBalance": 0,
          "CurrentBalanceWithSubAccounts": 0,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "138",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:59:31-07:00",
           "LastUpdatedTime": "2022-06-15T21:12:29-07:00"
          }
         },
         {
          "Name": "Directors Travel12.5870",
          "SubAccount": false,
          "FullyQualifiedName": "Directors Travel12.5870",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "CashOnHand",
          "CurrentBalance": 0,
          "CurrentBalanceWithSubAccounts": 0,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "643",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2021-09-27T08:27:22-07:00",
           "LastUpdatedTime": "2022-02-15T10:03:46-08:00"
          }
         },
         {
          "Name": "NYSE Guest Fees",
          "SubAccount": false,
          "FullyQualifiedName": "NYSE Guest Fees",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "CashOnHand",
          "CurrentBalance": 0,
          "CurrentBalanceWithSubAccounts": 0,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "594",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-06-13T12:22:31-07:00",
           "LastUpdatedTime": "2021-03-17T18:00:46-07:00"
          }
         },
         {
          "Name": "Petty Cash",
          "SubAccount": false,
          "FullyQualifiedName": "Petty Cash",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "Checking",
          "AcctNum": "1299",
          "CurrentBalance": 27.17,
          "CurrentBalanceWithSubAccounts": 27.17,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "470",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:59:38-07:00",
           "LastUpdatedTime": "2020-02-12T10:26:29-08:00"
          }
         },
         {
          "Name": "PRIME MERIDIAN - MM SAVINGS",
          "SubAccount": false,
          "Description": "Account changed from Savings to MM Savings",
          "FullyQualifiedName": "PRIME MERIDIAN - MM SAVINGS",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "Checking",
          "AcctNum": "1031",
          "CurrentBalance": 305428.62,
          "CurrentBalanceWithSubAccounts": 305428.62,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "283",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:59:34-07:00",
           "LastUpdatedTime": "2022-08-15T13:41:00-07:00"
          }
         },
         {
          "Name": "PRIME MERIDIAN -MM Relief Fund",
          "SubAccount": false,
          "Description": "Golf Classic Relief Fund Money Market Account",
          "FullyQualifiedName": "PRIME MERIDIAN -MM Relief Fund",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "Checking",
          "AcctNum": "1040",
          "CurrentBalance": 29665.45,
          "CurrentBalanceWithSubAccounts": 29665.45,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "141",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:59:31-07:00",
           "LastUpdatedTime": "2022-08-15T13:46:49-07:00"
          }
         },
         {
          "Name": "PRIME MERIDIAN OPERATING ACCT",
          "SubAccount": false,
          "Description": "Checking Account - Payroll",
          "FullyQualifiedName": "PRIME MERIDIAN OPERATING ACCT",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "Checking",
          "AcctNum": "1025",
          "CurrentBalance": 844544.27,
          "CurrentBalanceWithSubAccounts": 844544.27,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "510",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:59:39-07:00",
           "LastUpdatedTime": "2022-08-17T12:13:04-07:00"
          }
         },
         {
          "Name": "Scholarship Fund",
          "SubAccount": false,
          "Description": "Scholarship fund",
          "FullyQualifiedName": "Scholarship Fund",
          "Active": true,
          "Classification": "Asset",
          "AccountType": "Bank",
          "AccountSubType": "Checking",
          "AcctNum": "1026",
          "CurrentBalance": 13378.34,
          "CurrentBalanceWithSubAccounts": 13378.34,
          "CurrencyRef": {
           "value": "USD",
           "name": "United States Dollar"
          },
          "domain": "QBO",
          "sparse": false,
          "Id": "611",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-08-01T09:14:04-07:00",
           "LastUpdatedTime": "2022-08-01T11:15:13-07:00"
          }
         }
        ],
        "startPosition": 1,
        "maxResults": 9
       },
       "time": "2022-08-17T13:34:09.192-07:00"
      }    ';
    $response = json_decode($json, TRUE);
    return CRM_Utils_Array::rekey($response['QueryResponse']['Account'], 'Id');
  }

  public function fetchPaymentMethodById($id) {
    $paymentMethods = $this->fetchActivePaymentMethodsList();
    return $paymentMethods[$id];
  }
  
  public function fetchActivePaymentMethodsList() {
    $json = '
      {
       "QueryResponse": {
        "PaymentMethod": [
         {
          "Name": "ACH E-check",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "24",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "American Express",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "7",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "AmEx",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "13",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "Cash",
          "Active": true,
          "Type": "NON_CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "4",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "ch",
          "Active": true,
          "Type": "NON_CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "26",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-10-22T12:45:05-07:00",
           "LastUpdatedTime": "2019-10-22T12:45:05-07:00"
          }
         },
         {
          "Name": "Check",
          "Active": true,
          "Type": "NON_CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "25",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "Check/Money Order",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "5",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "discover",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "27",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2020-10-27T11:29:59-07:00",
           "LastUpdatedTime": "2020-10-27T11:29:59-07:00"
          }
         },
         {
          "Name": "MasterCard",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "9",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "OL CREDIT CARD",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "6",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "TRANSFER",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "11",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "Visa",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "8",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         },
         {
          "Name": "VISA-OL",
          "Active": true,
          "Type": "CREDIT_CARD",
          "domain": "QBO",
          "sparse": false,
          "Id": "14",
          "SyncToken": "0",
          "MetaData": {
           "CreateTime": "2019-04-22T07:57:54-07:00",
           "LastUpdatedTime": "2019-04-22T07:57:54-07:00"
          }
         }
        ],
        "startPosition": 1,
        "maxResults": 13
       },
       "time": "2022-08-23T09:34:13.697-07:00"
      }
    ';
    $response = json_decode($json, TRUE);
    return CRM_Utils_Array::rekey($response['QueryResponse']['PaymentMethod'], 'Id');
  }

}
