{if $rows|count}
<table id="fpptaqbHeldItems" class="row-highlight">
  <thead>
  <tr>
    <th>{ts}ID{/ts}</th>
    <th>{ts}Received Date{/ts}</th>
    <th>{ts}Attributed To{/ts}</th>
    <th>{ts}Payor{/ts}</th>
    <th>{ts}Amount{/ts}</th>
    <th></th>
  </tr>
  </thead>
  <tbody>
  {foreach from=$rows item=row}
    <tr id="fpptaqbHeldItem-{$row.id}" class="crm-entity {cycle values="odd-row,even-row"}">
      <td>{$row.id}</td>
      <td>{$row.receive_date}</td>
      <td>{$row.organizationName}</td>
      <td>{$row.display_name}</td>
      <td>{$row.total_amount|crmMoney}</td>
      <td>
        <a href="{crmURL p="civicrm/fpptaqb/itemaction" q="itemaction=load&type=inv&id=`$row.id`"}" class="action-item crm-hover-button crm-popup" title="Load Invoice">Load</a>
        <a href="{crmURL p="civicrm/fpptaqb/itemaction" q="itemaction=unhold&type=inv&id=`$row.id`"}" class="action-item crm-hover-button" title="Un-hold">Un-hold</a>
      </td>      
    </tr>
  {/foreach}
   </tbody>
</table>

{else}
<p class="status">{ts}There are no held invoices.{/ts}</p>
{crmButton href="/civicrm/fpptaqb/stepthru/inv" title="Return to step-thru invoice sync" icon=""}Return to step-thru invoice sync{/crmButton}

{/if}