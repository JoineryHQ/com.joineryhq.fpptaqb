<?php
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Fpptaqb_Upgrader extends CRM_Fpptaqb_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  // public function postInstall() {
  //  $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
  //    'return' => array("id"),
  //    'name' => "customFieldCreatedViaManagedHook",
  //  ));
  //  civicrm_api3('Setting', 'create', array(
  //    'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
  //  ));
  // }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   */
  // public function uninstall() {
  //  $this->executeSqlFile('sql/myuninstall.sql');
  // }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  // public function enable() {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  // public function disable() {
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  // }

  /**
   * Co-opt settings from (now defunct) fpptaqbhelper extension.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4201(): bool {
    $values = [];
    if ($fpptaqbhelper_cf_id_contribution = Civi::settings()->get('fpptaqbhelper_cf_id_contribution')) {
      $values['fpptaqb_cf_id_contribution'] = $fpptaqbhelper_cf_id_contribution;
    }
    if ($fpptaqbhelper_cf_id_participant = Civi::settings()->get('fpptaqbhelper_cf_id_participant')) {
      $values['fpptaqb_cf_id_participant'] = $fpptaqbhelper_cf_id_participant;
    }
    if (!empty($values)) {
      civicrm_api3('setting', 'create', $values);
    }
    return TRUE;
  }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4202(): bool {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface
    $alterQuery = "ALTER TABLE `civicrm_fpptaquickbooks_log`
      ADD `sync_session_id` varchar(64) COMMENT 'Unique ID per sync session, e.g. Step-thru sync page load or Scheduled Job run.' AFTER `unique_request_id`,
      ADD `api_output_text` varchar(2550) COMMENT 'Text or error message extracted from API call output.' AFTER `api_output`,
      ADD `api_output_error_code` varchar(64) COMMENT 'Error code, if any, extracted from API call output.' AFTER `api_output_text`
    ";
    $this->addTask("Add columns in civicrm_fpptaquickbooks_log table.", 'executeSql', $alterQuery);

    $dao = CRM_Core_DAO::executeQuery('
      select id, api_output
      from civicrm_fpptaquickbooks_log
      where
        api_output is not null
      order by id
    ');

    while ($dao->fetch()) {

      $api_output = json_decode($dao->api_output, TRUE);

      if (empty($api_output)) {
        continue;
      }

      $apiOutputText = ($api_output['is_error'] ? $api_output['error_message'] : $api_output['values']['text']) ?? '';
      $apiOutputErrorCode = $api_output['error_code'] ?? '';
      if (empty($apiOutputText) && empty($apiOutputErrorCode)) {
        // Nothing to update on this row, so continue to next.
        continue;
      }
      $updateQuery = '
        update civicrm_fpptaquickbooks_log
        set
          api_output_text = if(%1 > "", %1, NULL),
          api_output_error_code = if(%2 > "", %2, NULL)
        where id = %3
      ';
      $updateQueryParams = [
        1 => [$apiOutputText, 'String'],
        2 => [$apiOutputErrorCode, 'String'],
        3 => [$dao->id, 'Int'],
      ];
      $this->addTask("Update civicrm_fpptaquickbooks_log for entry id={$dao->id}", 'executeSql', $updateQuery, $updateQueryParams);
    }
    return TRUE;
  }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4201(): bool {
  //   $this->ctx->log->info('Applying update 4201');
  //   // this path is relative to the extension base dir
  //   $this->executeSqlFile('sql/upgrade_4201.sql');
  //   return TRUE;
  // }


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4202(): bool {
  //   $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

  //   $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //   $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //   $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //   return TRUE;
  // }
  // public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  // public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  // public function processPart3($arg5) { sleep(10); return TRUE; }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4203(): bool {
  //   $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

  //   $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //   $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //   for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //     $endId = $startId + self::BATCH_SIZE - 1;
  //     $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //       1 => $startId,
  //       2 => $endId,
  //     ));
  //     $sql = '
  //       UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
  //       WHERE id BETWEEN %1 and %2
  //     ';
  //     $params = array(
  //       1 => array($startId, 'Integer'),
  //       2 => array($endId, 'Integer'),
  //     );
  //     $this->addTask($title, 'executeSql', $sql, $params);
  //   }
  //   return TRUE;
  // }

}
