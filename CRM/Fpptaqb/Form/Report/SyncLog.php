<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Form_Report_SyncLog extends CRM_Report_Form {
  function __construct() {
    $this->_columns = array(
      'civicrm_contact' => array(
        'fields' => array(
          'sort_name' => array(
            'title' => E::ts('Performed by'),
            'default' => TRUE,
          ),
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
        'filters' => array(
          'sort_name' => array(
            'title' => E::ts('Performed by'),
            'type' => CRM_Utils_Type::T_STRING,
            'default_op' => 'nll',
          ),          
        )
      ),
      'civicrm_fpptaquickbooks_log' => array(
        'dao' => 'CRM_Fpptaqb_DAO_FpptaquickbooksLog',
        'fields' => array(
          'created' => array(
            'title' => E::ts('Date/Time'),
            'default' => TRUE,
          ),
          'action' => array(
            'title' => E::ts('Action'),
            'default' => TRUE,
            'dbAlias' => '"REPLACED IN alterDisplay()"',
          ),
          'api_entity' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'api_action' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'entity_id' => array(
            'title' => E::ts('Entity ID'),
            'default' => TRUE,
          ),
          'api_params' => array(
            'title' => E::ts('API parameters'),
          ),
          'api_output' => array(
            'title' => E::ts('Raw result'),
          ),
          'api_output_text' => array(
            'title' => E::ts('Result text'),
            'default' => TRUE,
          ),
          'api_output_error_code' => array(
            'title' => E::ts('Error Code'),
            'default' => TRUE,
          ),
          'reason' => array(
            'title' => E::ts('Reason for action'),
            'default' => TRUE,
          ),
        ),
        'filters' => array(
          'created' => array(
            'title' => E::ts('Date/Time'),
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'action' => array(
            'title' => E::ts('Action'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Fpptaqb_Util::createApiActionOptionsList(),
            'dbAlias' => 'concat(fpptaquickbooks_log_civireport.api_entity , ".", fpptaquickbooks_log_civireport.api_action)',
          ),
          'entity_id' => array(
            'title' => E::ts('Entity ID'),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'api_output_text' => array(
            'title' => E::ts('Result text'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'api_output_error_code' => array(
            'title' => E::ts('Error Code'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
        'order_bys' => array(
          'created' => array(
            'title' => E::ts('Created'),
            'default' => TRUE,
            'default_weight' => 0,
            'default_order' => 'DESC',
          ),
        ),
      ),
    );
    parent::__construct();
    unset($this->_columns['civicrm_contact']['fields']['exposed_id']);
  }

  function from() {
    $this->_from = "
      FROM  
        civicrm_fpptaquickbooks_log {$this->_aliases['civicrm_fpptaquickbooks_log']}
        LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} ON {$this->_aliases['civicrm_fpptaquickbooks_log']}.contact_id = {$this->_aliases['civicrm_contact']}.id
    ";
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows
    $entryFound = FALSE;
    $checkList = array();
    foreach ($rows as $rowNum => $row) {

      if (!empty($this->_noRepeats) && $this->_outputMode != 'csv') {
        // not repeat any value set as no_repeat if it matches with the one
        // in previous row
        $repeatFound = FALSE;
        foreach ($row as $colName => $colVal) {
          if (CRM_Utils_Array::value($colName, $checkList) &&
            is_array($checkList[$colName]) &&
            in_array($colVal, $checkList[$colName])
          ) {
            $rows[$rowNum][$colName] = "";
            $repeatFound = TRUE;
          }
          if (in_array($colName, $this->_noRepeats)) {
            $checkList[$colName][] = $colVal;
          }
        }
      }

      // Display human-readable action label
      if (array_key_exists('civicrm_fpptaquickbooks_log_action', $row)) {
        $entryFound = TRUE;
        $rows[$rowNum]['civicrm_fpptaquickbooks_log_action'] = CRM_Fpptaqb_Util::formatApiAction($row['civicrm_fpptaquickbooks_log_api_entity'], $row['civicrm_fpptaquickbooks_log_api_action']);
      }

      // Provide a link to the relevant entity, if any.
      if (array_key_exists('civicrm_fpptaquickbooks_log_entity_id', $row)) {
        $entryFound = TRUE;
        // For contributions:
        if (in_array(strtolower($row['civicrm_fpptaquickbooks_log_api_entity']), [
          'fpptaquickbookscontributioninvoice',
          'fpptaqbstepthruinvoice'
        ])) {
          $url = CRM_Utils_System::url("civicrm/contact/view/contribution",
            "reset=1&action=view&id={$row['civicrm_fpptaquickbooks_log_entity_id']}",
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_fpptaquickbooks_log_entity_id_link'] = $url;
          $rows[$rowNum]['civicrm_fpptaquickbooks_log_entity_id_hover'] = E::ts("View this Contribution");
        }
        // For contacts:
        elseif (strtolower($row['civicrm_fpptaquickbooks_log_api_entity']) == 'fpptaquickbookscontactcustomer') {
          $url = CRM_Utils_System::url("civicrm/contact/view",
            "reset=1&cid={$row['civicrm_fpptaquickbooks_log_entity_id']}",
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_fpptaquickbooks_log_entity_id_link'] = $url;
          $rows[$rowNum]['civicrm_fpptaquickbooks_log_entity_id_hover'] = E::ts("View this Contact");
        }
        // For financial types:
        elseif (strtolower($row['civicrm_fpptaquickbooks_log_api_entity']) == 'fpptaquickbooksfinancialtypeitem') {
          $url = CRM_Utils_System::url("civicrm/admin/financial/financialType",
            "reset=1&action=update&id={$row['civicrm_fpptaquickbooks_log_entity_id']}",
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_fpptaquickbooks_log_entity_id_link'] = $url;
          $rows[$rowNum]['civicrm_fpptaquickbooks_log_entity_id_hover'] = E::ts("Edit this Financial Type");
        }
      }
      
      if (array_key_exists('civicrm_contact_sort_name', $row)) {
        $entryFound = TRUE;

        if (!empty($row['civicrm_contact_id'])) {
          $url = CRM_Utils_System::url("civicrm/contact/view",
            'reset=1&cid=' . $row['civicrm_contact_id'],
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
          $rows[$rowNum]['civicrm_contact_sort_name_hover'] = E::ts("View Contact Summary for this Contact.");
        }
        else {
          $rows[$rowNum]['civicrm_contact_sort_name'] = E::ts('(unattended)');
        }
      }

      if (!$entryFound) {
        break;
      }
    }
  }

}
