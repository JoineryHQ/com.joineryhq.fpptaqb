<H3>Processing contribution ID: {$contribution.id} &nbsp; <a target="_blank" href="{crmURL p="civicrm/contact/view/contribution" q="reset=1&action=view&cid=`$contribution.contact_id`&id=`$contribution.id`"}">View</a></H3>
<pre>
Attributed to Organization: {$contribution.organizationName} (id={$contribution.organizationCid})   <a target="_blank" href="{crmURL p="civicrm/contact/view" q="reset=1&cid=`$contribution.organizationCid`"}">View</a>
Found QuickBooks customer "{$contribution.qbCustomerName}" (id={$contribution.qbCustomerId})
{"$contribution.lineItems"|count} line items found:
{foreach from=$contribution.lineItems key=lineItemId item=lineItem}
  - "{$lineItem.label}" {$lineItem.qty} @ {$lineItem.unit_price|crmMoney} (Financial Type: {$lineItem.financialType} => QuickBooks GL: {$lineItem.qbGlCode} "{$lineItem.qbGlDescription}")
{/foreach}
</pre>

<h4>Will create QuickBooks invoice:</h4>
<pre>
QuickBooks customer "{$contribution.qbCustomerName}" (id={$contribution.qbCustomerId})
Date: {$contribution.receive_date|crmDate:'%Y-%m-%d'} 
Invoice Number: {$contribution.invoice_number} 
Line items: 
{foreach from=$contribution.lineItems key=lineItemId item=lineItem}
  - {$lineItem.qty} @ {$lineItem.unit_price|crmMoney} {$lineItem.qbGlCode} "{$lineItem.qbGlDescription}"
{/foreach}
Note:
{$contribution.qbNote}
</pre>

