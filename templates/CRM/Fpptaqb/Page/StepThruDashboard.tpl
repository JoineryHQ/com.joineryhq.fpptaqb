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
          <a class="action-item crm-hover-button" href="{crmURL p="civicrm/fpptaqb/stepthru/`$key`"}">Step-thru sync</a>
          {if $row.statistics.countHeld}<a class="action-item crm-hover-button" href="{crmURL p="civicrm/fpptaqb/helditems/`$key`"}">Review held items</a>{/if}
      </td>
      </tr>
    {/foreach}
  </tbody>
</table>