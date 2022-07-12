<h3>Settings <a style="margin-left: 3em" class="action-item crm-hover-button" href="{crmURL fb=1 p="civicrm/admin/fpptaqbhelper/settings" q="reset=1"}">Edit settings</a></h3>
<table class="report-layout statistics-table">
  {if $isMock}
    <tr>
      <th class="statistics" scope="row">{ts}Live sync status{/ts}</th>
      <td class="crm-error">
        ALERT: The system is configured to use a placeholder sync and is not syncing with a live QuickBooks account.
      </td>
    </tr>
  {/if}
    <tr>
      <th class="statistics" scope="row">{ts}Minimum invoice/payment date{/ts}</th>
      <td>{$fpptaqb_minimum_date}</td>
    </tr>
    <tr>
      <th class="statistics" scope="row">{ts}Days to wait before syncing an invoice or payment{/ts}</th>
      <td>{$fpptaqb_sync_wait_days}</td>
    </tr>
</table>

<h3 style="margin-top: 2em">Sync data</h3>
<table id="fpptaqbSyncDashboard" class="row-highlight">
  <thead>
    <tr>
      <th></th>
      <th>Ready to Sync</th>
      <th>Held</th>
      <th></th>
    </tr>
  <thead>
  <tbody>
    {foreach from=$rows key=key item=row}
      <tr class="crm-entity {cycle values="odd-row,even-row"}">
        <td>{$row.label}</td>
        <td>{$row.statistics.countReady}</td>
        <td>{$row.statistics.countHeld}</td>
        <td>
          <a class="action-item crm-hover-button" href="{crmURL fb=1 p="civicrm/fpptaqb/stepthru/`$key`"}">Step-thru sync</a>
          {if $row.statistics.countHeld}<a class="action-item crm-hover-button" href="{crmURL fb=1 p="civicrm/fpptaqb/helditems/`$key`"}">Review held items</a>{/if}
      </td>
      </tr>
    {/foreach}
  </tbody>
</table>