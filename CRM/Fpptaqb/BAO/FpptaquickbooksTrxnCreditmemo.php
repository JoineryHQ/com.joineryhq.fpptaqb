<?php
// phpcs:disable
use CRM_Fpptaqb_ExtensionUtil as E;
// phpcs:enable

class CRM_Fpptaqb_BAO_FpptaquickbooksTrxnCreditmemo extends CRM_Fpptaqb_DAO_FpptaquickbooksTrxnCreditmemo {

  /**
   * Create a new FpptaquickbooksTrxnCreditmemo based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Fpptaqb_DAO_FpptaquickbooksTrxnCreditmemo|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnCreditmemo';
    $entityName = 'FpptaquickbooksTrxnCreditmemo';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
  */

}
