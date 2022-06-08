<p>Ready to sync: {$countItemsToSync}</p>
<p>Held: {$countItemsHeld} [FIXME: VIEW HELD]</p>

<div id="fpptaqb-sync-log">
<i id="fpptaqb-sync-log-loading" class="crm-i fa-spinner fa-spin"></i>
</div>

{crmButton class="fpptaqb-sync-button" href="#" id="fpptaqb-button-begin" title="Begin step-thru sync process" icon="fa-rocket"}Begin{/crmButton}
{crmButton class="fpptaqb-sync-button" href="#" id="fpptaqb-button-reload" title="Try loading this again" icon="fa-refresh"}Reload this item{/crmButton}
{crmButton class="fpptaqb-sync-button" href="#" id="fpptaqb-button-hold" title="Mark this item \"held\" and move on" icon="fa-ban"}Skip this item, and load next{/crmButton}
{crmButton class="fpptaqb-sync-button" href="#" id="fpptaqb-button-sync" title="Sync this invoice to QuickBooks" icon="fa-paper-plane"}Sync this item to QuickBooks{/crmButton}
{crmButton class="fpptaqb-sync-button" href="#" id="fpptaqb-button-sync-retry" title="Try again to sync this invoice to QuickBooks" icon="fa-paper-plane"}Re-try sync this item to QuickBooks{/crmButton}
{crmButton class="fpptaqb-sync-button" href="#" id="fpptaqb-button-next" title="Load the next invoice" icon="fa-chevron-right"}Load next item{/crmButton}
{crmButton class="fpptaqb-sync-button" href="/civicrm/fpptaqb/stepthru/inv" id="fpptaqb-button-exit" title="Exit this process" icon="fa-times"}Exit the step-through process{/crmButton}
