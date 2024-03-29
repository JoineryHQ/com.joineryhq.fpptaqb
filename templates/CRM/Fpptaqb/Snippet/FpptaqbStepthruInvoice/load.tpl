<H3>Processing contribution ID: {$contribution.id} &nbsp; <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view/contribution" q="reset=1&action=view&cid=`$contribution.contact_id`&id=`$contribution.id`" fb=1}">View</a></H3>
<pre>
Status: {$contribution.contribution_status}
Attributed to Organization: {$contribution.organizationName} (id={$contribution.organizationCid})   <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view" q="reset=1&cid=`$contribution.organizationCid`"}">View</a>
Found QuickBooks customer "{$contribution.qbCustomerName}" (id={$contribution.qbCustomerId})
{$contribution.lineItems|@count} line items found:
{foreach from=$contribution.lineItems key=lineItemId item=lineItem}
  - "{$lineItem.label}" {$lineItem.qty} @ {$lineItem.unit_price|crmMoney} (Financial Type: {$lineItem.financialType} => QuickBooks Item: "{$lineItem.qbItemDetails.FullyQualifiedName}")
{/foreach}
</pre>

<h4>Will create QuickBooks invoice:</h4>
<pre>
QuickBooks customer "{$contribution.qbCustomerName}" (id={$contribution.qbCustomerId})
Date: {$contribution.receive_date|crmDate:'%Y-%m-%d'} 
Invoice Number: {$contribution.qbInvNumber} 
Line items: 
{foreach from=$contribution.qbLineItems key=qbLineItemId item=qbLineItem}
  - {$qbLineItem.qty} @ {$qbLineItem.unit_price|crmMoney} "{$qbLineItem.qbItemDetails.FullyQualifiedName}" "{$qbLineItem.label}"
{/foreach}
Note:
{$contribution.qbNote}
</pre>

