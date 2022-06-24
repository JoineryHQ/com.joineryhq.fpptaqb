<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_BAO_FpptaquickbooksTrxnPayment extends CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment {

  /**
   * Create a new FpptaquickbooksTrxnPayment based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment|NULL
   *
  public static function create($params) {
    $className = 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnPayment';
    $entityName = 'FpptaquickbooksTrxnPayment';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
