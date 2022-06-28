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
      <td>{$row.loadedInvoice.receive_date}</td>
      <td>{$row.loadedInvoice.organizationName}</td>
      <td>{$row.loadedInvoice.display_name}</td>
      <td>{$row.loadedInvoice.total_amount}</td>
      <td>
        <a data-title="foobar" href="{crmURL p="civicrm/fpptaqb/loaditem/inv" q="id=`$row.loadedInvoice.id`"}" class="action-item crm-hover-button crm-popup" title="Load Invoice">Load</a>
      </td>      
    </tr>
  {/foreach}
   </tbody>
</table>
