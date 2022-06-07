<?php
use CRM_Fpptaqb_ExtensionUtil as E;

class CRM_Fpptaqb_Page_StepthruInvSync extends CRM_Core_Page {

  public function run() {
    // Example: Assign a variable for use in a template
    $this->assign('countItemsToSync', count(CRM_Fpptaqb_Util::getInvToSyncIds()));
    $this->assign('countItemsHeld', count(CRM_Fpptaqb_Util::getInvHeldIds()));
    
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/CrmFpptaqbPageStepthruInvSync.js');
    CRM_Core_Resources::singleton()->addStyleFile('com.joineryhq.fpptaqb', 'css/CrmFpptaqbPageStepthruInvSync.css');

    parent::run();
  }

}
