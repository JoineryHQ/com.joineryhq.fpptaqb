{if $rows|count}
<table id="fpptaqbHeldItems" class="row-highlight">
  <thead>
  <tr>
    <th>{ts}Inv. No.{/ts}</th>
    <th>{ts}Contrib. ID{/ts}</th>
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
      <td><a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view/contribution" q="reset=1&action=view&cid=`$row.contact_id`&id=`$row.id`"}">{if $row.invoice_number}{$row.invoice_number}{else}[none]{/if}</a></td>
      <td>{$row.id}</td>
      <td>{$row.receive_date}</td>
      <td>
        {if $row.organizationCid}
          <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view" q="reset=1&cid=`$row.organizationCid`"}">{$row.organizationName}</a>
        {else}
          {$row.organizationName}
        {/if}
      </td>
      <td><a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view" q="reset=1&cid=`$row.contact_id`"}">{$row.display_name}</a></td>
      <td>{$row.total_amount|crmMoney}</td>
      <td>
        <a href="{crmURL fb=1 p="civicrm/fpptaqb/itemaction" q="itemaction=load&type=inv&id=`$row.id`"}" class="action-item crm-hover-button crm-popup" title="Load Invoice">Load</a>
        <a href="{crmURL fb=1 p="civicrm/fpptaqb/itemaction" q="itemaction=unhold&type=inv&id=`$row.id`"}" class="action-item crm-hover-button" title="Un-hold">Un-hold</a>
      </td>      
    </tr>
  {/foreach}
   </tbody>
</table>

{else}
  <p class="status">{ts}There are no held invoices.{/ts}</p>
{/if}
{crmButton fb=1 p="civicrm/fpptaqb/stepthru/inv" title="Return to step-thru invoice sync" icon=""}Return to step-thru invoice sync{/crmButton}
{crmButton fb=1 p="civicrm/fpptaqb/stepthru" title="Return to step-thru invoice sync" icon=""}Return to step-thru dashboard{/crmButton}
