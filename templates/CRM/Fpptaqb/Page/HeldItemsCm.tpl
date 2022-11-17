{if $rows|count}
<table id="fpptaqbHeldItems" class="row-highlight">
  <thead>
  <tr>
    <th>{ts}ID{/ts}</th>
    <th>{ts}Contribution ID{/ts}</th>
    <th>{ts}Refund Date{/ts}</th>
    <th>{ts}Attributed To{/ts}</th>
    <th>{ts}Refund Amount{/ts}</th>
    <th></th>
  </tr>
  </thead>
  <tbody>
  {foreach from=$rows item=row}
    <tr id="fpptaqbHeldItem-{$row.id}" class="crm-entity {cycle values="odd-row,even-row"}">
      <td>{$row.id}</td>
      <td><a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view/contribution" q="reset=1&action=view&id=`$row.contributionId`"}">{$row.contributionId}</a></td>
      <td>{$row.trxn_date}</td>
      <td>
        {if $row.organizationCid}
          <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view" q="reset=1&cid=`$row.organizationCid`"}">{$row.organizationName}</a></td>
        {else}
          {$row.organizationName}
        {/if}
      </td>
      <td>{$row.total_amount|crmMoney}</td>
      <td>
        <a href="{crmURL fb=1 p="civicrm/fpptaqb/itemaction" q="itemaction=load&type=cm&id=`$row.id`"}" class="action-item crm-hover-button crm-popup" title="Load Credit Memo">Load</a>
        <a href="{crmURL fb=1 p="civicrm/fpptaqb/itemaction" q="itemaction=unhold&type=cm&id=`$row.id`"}" class="action-item crm-hover-button" title="Un-hold">Un-hold</a>
      </td>      
    </tr>
  {/foreach}
   </tbody>
</table>

{else}
  <p class="status">{ts}There are no held credit memos.{/ts}</p>
{/if}
{crmButton fb=1 p="civicrm/fpptaqb/stepthru/cm" title="Return to step-thru credit memo sync" icon=""}Return to step-thru credit memo sync{/crmButton}
{crmButton fb=1 p="civicrm/fpptaqb/stepthru" title="Return to step-thru dashboard" icon=""}Return to step-thru dashboard{/crmButton}
