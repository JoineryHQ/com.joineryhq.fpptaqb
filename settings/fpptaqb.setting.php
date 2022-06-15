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
);