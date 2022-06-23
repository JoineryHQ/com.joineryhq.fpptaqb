<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_StepthruInvSync extends CRM_Core_Page {

  public function run() {
    // Example: Assign a variable for use in a template
    $statistics = CRM_Fpptaqb_Utils_Invoice::getStepthruStatistics();
    
    $this->assign('countItemsToSync', $statistics['countReady']);
    $this->assign('countItemsHeld', $statistics['countHeld']);
    $this->assign('isMock', Civi::settings()->get('fpptaqb_use_sync_mock'));
    
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/CrmFpptaqbPageStepthruInvSync.js');
    CRM_Core_Resources::singleton()->addStyleFile('com.joineryhq.fpptaqb', 'css/CrmFpptaqbPageStepthruInvSync.css');

    $vars = [
      'debug_enabled' => Civi::settings()->get('debug_enabled'),
    ];
    CRM_Core_Resources::singleton()->addVars('fpptaqb', $vars);
    
    parent::run();
  }

}
