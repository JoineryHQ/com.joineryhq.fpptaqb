<div class="help">
<p>{ts}Configure rules to determine the correct QuickBooks Payment Method when syncing payments.{/ts}</p>
<p>{ts}Rules are processed in order from top to bottom, as configured here. The first matching rule will be used.{/ts}</p>
</div>

<div id="fpptaqb-no-rules-message" class="status message">No rules found.</div>

<table id="qbPaymentMethodRules">
<thead>
<tr>
  <th></th>
  <th>{$form.crmPaymentMethod.label}</th>
  <th>{$form.cardType.label}</th>
  <th>{$form.qbPaymentMethod.label}</th>
  <th></th>
</tr>
</thead>

<tbody>
<tr id="qbPaymentMethodRule-template" style="display: none">
  <td>
    <a href="#" class="fpptaqb-move-up" id="moveUp">{icon icon="fa-chevron-up"}{ts}Move up{/ts}{/icon}</a>
    <a href="#" class="fpptaqb-move-down" id="moveDown">{icon icon="fa-chevron-down"}{ts}Move down{/ts}{/icon}</a>
  </td>
  <td>{$form.crmPaymentMethod.html}</td>
  <td>{$form.cardType.html}</td>
  <td>{$form.qbPaymentMethod.html}</td>
  <td><a href="#" id="deleteRule">{icon icon="fa-trash"}{ts}Delete rule{/ts}{/icon}</a></td>
</tr>
</tbody>
</table>
<a id="addRule" href="#">{icon icon="fa-plus-circle"}{ts}Add rule{/ts}{/icon} Add rule</a>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>