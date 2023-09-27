<h3>Invoices ready to sync</h3>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Payor</th>
      <th>Received Date</th>
      <th>Total</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$invoices item=inv}
    <tr class="{cycle values="odd-row,even-row"}">
      <td>{$inv.id}</td>
      <td>{$inv.sort_name}</td>
      <td>{$inv.receive_date}</td>
      <td>{$inv.total_amount|crmMoney}</td>
      <td><a href="#{$inv.id}" id="list-load-{$inv.id}-{$uniqid}" onclick="CRM.fpptaqbStepthru.handleActionButtonClick(event);">Load</a></td>
    </tr>
  {/foreach}
  </tbody>
</table>


<script type="text/javascript">
  var myApiParams;
  {foreach from=$invoices item=inv}
    {literal}myApiParams = {};{/literal}
    myApiParams.id = "{$inv.id}";
    CRM.$('a#list-load-{$inv.id}-{$uniqid}').prop('action', 'load');
    CRM.$('a#list-load-{$inv.id}-{$uniqid}').prop('myApiParams', myApiParams);
  {/foreach}
</script>
