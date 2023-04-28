<?php

require_once 'CRM/Core/Form.php';
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Form controller class for QuickBooks Payment Method Rules.
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Fpptaqb_Form_QbPaymentMethodRules extends CRM_Core_Form {

  public function buildQuickForm() {
    // Add breadcrumb to main settings.
    $url = CRM_Utils_System::url('civicrm/admin/fpptaqb/settings', "reset=1");
    $breadCrumb = [['title' => ts('FPPTA QuickBooks Sync: Settings'), 'url' => $url]];
    CRM_Utils_System::appendBreadCrumb($breadCrumb);

    // Ensure redirection to self after submit.
    $this->controller->_destination = $this->controller->_entryURL;

    try {
      $qbPaymentMethodOptions = CRM_Fpptaqb_Utils_Quickbooks::getPaymentMethodOptions();
    }
    catch (CRM_Fpptaqb_Exception $e) {
      CRM_Core_Session::setStatus('Error fetching QuickBooks payment methods. QuickBooks error: ' . $e->getMessage(), E::ts('Error'), 'no-popup');
      $this->assign('isQbError', TRUE);
      return;
    }

    $this->add('hidden', 'fpptaqb_qb_payment_method_rules', ts('fpptaqb_qb_payment_method_rules'), ['id' => 'fpptaqb_qb_payment_method_rules']);

    // Template fields for payment method rule
    // Template field for civi payment method
    $crmPaymentMethodOptions = CRM_Contribute_BAO_Contribution::buildOptions('payment_instrument_id');
    array_unshift($crmPaymentMethodOptions, E::ts('[N/A]'));
    $this->add(
      // field type
      'Select',
      // field name
      'crmPaymentMethod',
      // field label
      E::ts('If CiviCRM Payment Method is'),
      // Options
      $crmPaymentMethodOptions
    );

    // Template field for civi credit card type
    $cardTypeOptions = CRM_Core_BAO_FinancialTrxn::buildOptions('card_type_id');
    array_unshift($cardTypeOptions, E::ts('[N/A]'));
    $this->add(
      // field type
      'Select',
      // field name
      'cardType',
      // field label
      E::ts('And CiviCRM Credit Card Type is'),
      // Options
      $cardTypeOptions
    );
    // Template field for qb payment method
    $this->add(
      // field type
      'Select',
      // field name
      'qbPaymentMethod',
      // field label
      E::ts('Use QB Payment Method'),
      // Options
      $qbPaymentMethodOptions
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ),
    ));

    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/qbPaymentMethodRules.js');

    parent::buildQuickForm();
  }

  public function validate() {
    $error = parent::validate();
    $values = $this->exportValues();

    // Ensure rules config is a valid json string.
    $rulesArray = json_decode($values['fpptaqb_qb_payment_method_rules'], TRUE);

    if (json_encode($rulesArray) != $values['fpptaqb_qb_payment_method_rules']) {
      $this->_errors['fpptaqb_qb_payment_method_rules'] = E::ts('Invalid format of rules; please try again. Settings not saved.');
    }
    return (0 == count($this->_errors));
  }

  public function postProcess() {
    $values = $this->exportValues();
    _fpptaqb_civicrmapi('setting', 'create', [
      'fpptaqb_qb_payment_method_rules' => $values['fpptaqb_qb_payment_method_rules'],
    ]);
    CRM_Core_Session::setStatus(E::ts('QuickBooks Payment Method rules saved.'), E::ts('Saved'), 'success');
    parent::postProcess();
  }

  public function preProcess() {
    CRM_Fpptaqb_Util::assignSettingsLocalNavigationItems($this);
    parent::preProcess();
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  public function setDefaultValues() {
    $ret = [];
    $ret['fpptaqb_qb_payment_method_rules'] = Civi::settings()->get('fpptaqb_qb_payment_method_rules');
    return $ret;
  }

}
