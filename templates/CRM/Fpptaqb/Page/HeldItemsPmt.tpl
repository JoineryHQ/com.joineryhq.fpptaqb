{if $rows|count}
<table id="fpptaqbHeldItems" class="row-highlight">
  <thead>
  <tr>
    <th>{ts}ID{/ts}</th>
    <th>{ts}Contribution ID{/ts}</th>
    <th>{ts}Attributed To{/ts}</th>
    <th>{ts}Payment Date{/ts}</th>
    <th>{ts}Method{/ts}</th>
    <th>{ts}Amount{/ts}</th>
    <th></th>
  </tr>
  </thead>
  <tbody>
  {foreach from=$rows item=row}
    <tr id="fpptaqbHeldItem-{$row.id}" class="crm-entity {cycle values="odd-row,even-row"}">
      <td>{$row.id}</td>
      <td>{$row.contributionId}</td>
      <td>{$row.organizationName}</td>
      <td>{$row.trxn_date}</td>
      <td>{$row.paymentInstrumentLabel}</td>
      <td>{$row.total_amount|crmMoney}</td>
      <td>
        <a href="{crmURL p="civicrm/fpptaqb/itemaction" q="itemaction=load&type=pmt&id=`$row.id`"}" class="action-item crm-hover-button crm-popup" title="Load Payment">Load</a>
        <a href="{crmURL p="civicrm/fpptaqb/itemaction" q="itemaction=unhold&type=pmt&id=`$row.id`"}" class="action-item crm-hover-button" title="Un-hold">Un-hold</a>
      </td>      
    </tr>
  {/foreach}
   </tbody>
</table>

{else}
  <p class="status">{ts}There are no held payments.{/ts}</p>
{/if}
{crmButton href="/civicrm/fpptaqb/stepthru/pmt" title="Return to step-thru payment sync" icon=""}Return to step-thru payment sync{/crmButton}
