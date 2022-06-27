<H3>Processing payment ID: {$payment.id} &nbsp; <a target="_blank" href="{crmURL p="civicrm/contact/view/contribution" q="reset=1&action=view&cid=`$payment.contributionCid`&id=`$payment.contribution_id`"}">View</a></H3>
<pre>
Status: {$payment.contribution_status}
Attributed to Organization: {$payment.organizationName} (id={$payment.organizationCid})   <a target="_blank" href="{crmURL p="civicrm/contact/view" q="reset=1&cid=`$payment.organizationCid`"}">View</a>
Found QuickBooks customer "{$payment.qbCustomerName}" (id={$payment.qbCustomerId})
{"$payment.lineItems"|count} line items found:
{foreach from=$payment.lineItems key=lineItemId item=lineItem}
  - "{$lineItem.label}" {$lineItem.qty} @ {$lineItem.unit_price|crmMoney} (Financial Type: {$lineItem.financialType} => QuickBooks GL: {$lineItem.qbGlCode} "{$lineItem.qbGlDescription}")
{/foreach}
</pre>

<h4>Will create QuickBooks invoice:</h4>
<pre>
QuickBooks customer "{$payment.qbCustomerName}" (id={$payment.qbCustomerId})
Date: {$payment.receive_date|crmDate:'%Y-%m-%d'} 
Invoice Number: {$payment.qbInvNumber} 
Line items: 
{foreach from=$payment.lineItems key=lineItemId item=lineItem}
  - {$lineItem.qty} @ {$lineItem.unit_price|crmMoney} {$lineItem.qbGlCode} "{$lineItem.qbGlDescription}"
{/foreach}
Note:
{$payment.qbNote}
</pre>

