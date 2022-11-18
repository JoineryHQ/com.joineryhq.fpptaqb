<H3>Processing refund ID: {$creditmemo.financial_trxn_id} &nbsp; <a target="_blank" href="{crmURL fb=1 p="civicrm/payment/edit" q="reset=1&id=`$creditmemo.financial_trxn_id`&contribution_id=`$creditmemo.contributionId`" fb=1}">Edit</a>
</H3>
<pre>
Refund on Contribution ID: {$creditmemo.contributionId}   <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view/contribution" q="reset=1&action=view&cid=`$creditmemo.contact_id`&id=`$creditmemo.contributionId`" fb=1}">View Contribution</a>
Transaction date: {$creditmemo.trxn_date|crmDate:'%Y-%m-%d'}
Attributed to Organization: {$creditmemo.organizationName} (id={$creditmemo.organizationCid})   <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view" q="reset=1&cid=`$creditmemo.organizationCid`"}">View</a>
Found QuickBooks customer "{$creditmemo.qbCustomerName}" (id={$creditmemo.qbCustomerId})
Total refund amount: {$creditmemo.total_amount|crmMoney}
Refund divided among the following financial types:
{foreach from=$creditmemo.lineItems key=lineItemId item=lineItem}
  - "{$lineItem.label}" {$lineItem.total_amount|crmMoney} (QuickBooks Item: "{$lineItem.qbItemDetails.FullyQualifiedName}")
{/foreach}
</pre>

<h4>Will create QuickBooks credit memo:</h4>
<pre>
QuickBooks customer "{$creditmemo.qbCustomerName}" (id={$creditmemo.qbCustomerId})
Date: {$creditmemo.trxn_date|crmDate:'%Y-%m-%d'}
Credit Memo Number: {$creditmemo.quickbooks_doc_number}
Line items:
{foreach from=$creditmemo.lineItems key=lineItemId item=lineItem}
  - 1 @ {$lineItem.total_amount|crmMoney} "{$lineItem.qbItemDetails.FullyQualifiedName}"
{/foreach}

Customer memo:
{$creditmemo.quickbooks_customer_memo}
</pre>

