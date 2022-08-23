<?php

require_once 'CRM/Core/Form.php';
use CRM_Fpptaqb_ExtensionUtil as E;

/**
 * Form controller class for extension Settings form.
 * Borrowed heavily from
 * https://github.com/eileenmcnaughton/nz.co.fuzion.civixero/blob/master/CRM/Civixero/Form/XeroSettings.php
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Fpptaqb_Form_QbPaymentMethodRules extends CRM_Core_Form {

  public function buildQuickForm() {

    $url = CRM_Utils_System::url('civicrm/admin/fpptaqb/settings', "reset=1");
    $breadCrumb = [['title' => ts('FPPTA QuickBooks Sync: Settings'), 'url' => $url]];
    CRM_Utils_System::appendBreadCrumb($breadCrumb);

    $this->controller->_destination = $this->controller->_entryURL; // Ensure redirection to self after submit.

    $this->add('hidden', 'fpptaqb_qb_payment_method_rules', ts('fpptaqb_qb_payment_method_rules'), ['id' => 'fpptaqb_qb_payment_method_rules']);
//    $this->add('textarea', 'fpptaqb_qb_payment_method_rules', ts('fpptaqb_qb_payment_method_rules'), ['rows' => 10, 'cols' => 40]);

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
    $cardTypeOptions = CRM_Financial_BAO_FinancialTrxn::buildOptions('card_type_id');
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
      CRM_Fpptaqb_Utils_Quickbooks::getPaymentMethodOptions(),
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

    parent::buildQuickForm();
  }

  /**
   * You need to write custom code for this function to validate the data in your settings fields
   */
  public function validate() {
    $error = parent::validate();
    $fields = $this->exportValues();

    if (!empty($fields['fpptaqb_minimum_date'])) {
      $yyyymmdd = CRM_Utils_Date::customFormat($fields['fpptaqb_minimum_date'], '%Y-%m-%d');
      if ($yyyymmdd != $fields['fpptaqb_minimum_date']) {
        $thisYear = CRM_Utils_Date::getToday(NULL, 'Y');
        $this->_errors['fpptaqb_minimum_date'] = E::ts('Please specify a date in the format "YYYY-MM-DD" (e.g. "%1-12-01" for Dec. 1 this year.)', ['1' => $thisYear]);
      }
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
    $vars = [
      'fpptaqb_qb_payment_method_rules' => (json_decode(Civi::settings()->get('fpptaqb_qb_payment_method_rules')) ?? []),
    ];
    CRM_Core_Resources::singleton()->addVars('fpptaqb', $vars);

    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.fpptaqb', 'js/qbPaymentMethodRules.js');
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