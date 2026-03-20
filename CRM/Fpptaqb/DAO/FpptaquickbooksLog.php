<?php

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id
 * @property string $created
 * @property string $contact_id
 * @property string $unique_request_id
 * @property string $sync_session_id
 * @property string $entity_id_param
 * @property string $entity_id
 * @property string $api_entity
 * @property string $api_action
 * @property string $api_params
 * @property string $api_output
 * @property string $api_output_text
 * @property string $api_output_error_code
 * @property string $reason
 */
class CRM_Fpptaqb_DAO_FpptaquickbooksLog extends CRM_Fpptaqb_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'civicrm_fpptaquickbooks_log';

}
