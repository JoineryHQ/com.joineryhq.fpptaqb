<?php

use CRM_Fpptaqb_ExtensionUtil as E;

return array(
  'fpptaqb_use_sync_mock' => array(
    'group_name' => 'Fpptaqb Settings',
    'group' => 'fpptaqb',
    'name' => 'fpptaqb_use_sync_mock',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'title' => E::ts('Use development mock instead of live QuickBooks sync?'),
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => 1,
    'html_type' => 'radio',
  ),
  'fpptaqb_sync_wait_days' => array(
    // FIXME: This setting not yet in use.
    'group_name' => 'Fpptaqb Settings',
    'group' => 'fpptaqb',
    'name' => 'fpptaqb_sync_wait_days',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Days to wait before syncing an invoice or payment'),
    'description' => 'Any contribution or payment will not be synced to QuickBooks until it is this many days old, in order to allow time for corrections. Set to 0 for no delay.',
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'default' => 7,
    'html_type' => 'text',
    'X_form_rules_args' => [
      [E::ts('The field "Days to wait before syncing an invoice or payment" is required. Set to 0 for no delay.'), 'required'],
    ],
  ),
  'fpptaqb_minimum_date' => array(
    'group_name' => 'Fpptaqb Settings',
    'group' => 'fpptaqb',
    'name' => 'fpptaqb_minimum_date',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Minimum invoice/payment date'),
    'description' => 'YYYY-MM-YDD: No invoice will be synced if its "Received Date" is less than this date.',
    'type' => 'String',
    'quick_form_type' => 'Element',
    'default' => '4000-01-01',
    'html_type' => 'text',
    'X_form_rules_args' => [
      [E::ts('The field "Minimum invoice/payment date" is required.'), 'required'],
    ],
  ),
);