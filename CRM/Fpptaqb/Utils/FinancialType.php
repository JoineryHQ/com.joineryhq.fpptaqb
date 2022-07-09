<?php

class CRM_Fpptaqb_Utils_FinancialType {

  public static function getItemOptions() {
    $options = [];
    $sync = CRM_Fpptaqb_Util::getSyncObject();
    $activeItems = $sync->fetchActiveItemsList();
    foreach ($activeItems as $activeItem) {
      $options[$activeItem['Id']] = $activeItem['FullyQualifiedName'];
    }
    return $options;
  }


}
