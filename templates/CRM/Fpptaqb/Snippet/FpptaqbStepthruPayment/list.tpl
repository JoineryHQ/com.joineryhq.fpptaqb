<h3>Payments ready to sync</h3>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Contribution ID</th>
      <th>Payor</th>
      <th>Transaction Date</th>
      <th>Total</th>
      <th>Payment Method</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$payments item=pmt}
    <tr class="{cycle values="odd-row,even-row"}">
      <td>{$pmt.id}</td>
      <td><a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view/contribution" q="reset=1&action=view&id=`$pmt.contributionId`"}">{$pmt.contributionId}</a></td>
      <td><a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view" q="reset=1&cid=`$pmt.contactId`"}">{$pmt.sort_name}</a></td>
      <td>{$pmt.trxn_date}</td>
      <td>{$pmt.total_amount|crmMoney}</td>
      <td>{$pmt.paymentInstrumentLabel}</td>
      <td><a href="#{$pmt.id}" id="list-load-{$pmt.id}-{$uniqid}" onclick="CRM.fpptaqbStepthru.handleActionButtonClick(event);">Load</a></td>
    </tr>
  {/foreach}
  </tbody>
</table>


<script type="text/javascript">
  var myApiParams;
  {foreach from=$payments item=pmt}
    {literal}myApiParams = {};{/literal}
    myApiParams.id = "{$pmt.id}";
    CRM.$('a#list-load-{$pmt.id}-{$uniqid}').prop('action', 'load');
    CRM.$('a#list-load-{$pmt.id}-{$uniqid}').prop('myApiParams', myApiParams);
  {/foreach}
</script>
