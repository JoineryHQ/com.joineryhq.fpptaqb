{include file="CRM/Fpptaqb/common/settingsLocalNav.tpl"}
<table id="fpptaqbFinancialType" class="row-highlight">
  <thead>
  <tr>
    <th>{ts}ID{/ts}</th>
    <th>{ts}Financial Type{/ts}</th>
    <th>{ts}Linked QuickBooks Item{/ts}</th>
    <th></th>
  </tr>
  </thead>
  <tbody>
  {foreach from=$rows item=row}
    <tr id="fpptaqbgFinancialType-{$row.id}" class="crm-entity {cycle values="odd-row,even-row"}">
      <td>{$row.id}</td>
      <td>{$row.name}</td>
      <td>{$row.qbItemName}</td>
      <td>
        <a href="{crmURL fb=1 p="civicrm/admin/financial/financialType" q="action=update&reset=1&id=`$row.id`"}" class="action-item crm-hover-button crm-popup" title="Edit Financial Type">Edit</a>
      </td>      
    </tr>
  {/foreach}
   </tbody>
</table>

{crmButton fb=1 p="civicrm/admin/financial/financialType" class="no-popup" q="reset=1" title="Return to Financial Types list" icon=""}Return to Financial Types list{/crmButton}
