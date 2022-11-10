<?php
// phpcs:disable
use CRM_Fpptaqb_ExtensionUtil as E;
// phpcs:enable

class CRM_Fpptaqb_BAO_FpptaquickbooksTrxnCreditmemoLine extends CRM_Fpptaqb_DAO_FpptaquickbooksTrxnCreditmemoLine {

  /**
   * Create a new FpptaquickbooksTrxnCreditmemoLine based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Fpptaqb_DAO_FpptaquickbooksTrxnCreditmemoLine|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Fpptaqb_DAO_FpptaquickbooksTrxnCreditmemoLine';
    $entityName = 'FpptaquickbooksTrxnCreditmemoLine';
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
