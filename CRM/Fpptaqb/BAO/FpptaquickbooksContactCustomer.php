<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_BAO_FpptaquickbooksContactCustomer extends CRM_Fpptaqb_DAO_FpptaquickbooksContactCustomer {

  /**
   * Create a new FpptaquickbooksContactCustomer based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Fpptaqb_DAO_FpptaquickbooksContactCustomer|NULL
   *
  public static function create($params) {
    $className = 'CRM_Fpptaqb_DAO_FpptaquickbooksContactCustomer';
    $entityName = 'FpptaquickbooksContactCustomer';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
