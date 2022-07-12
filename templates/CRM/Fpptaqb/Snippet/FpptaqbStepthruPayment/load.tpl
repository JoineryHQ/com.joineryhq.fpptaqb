<H3>Processing payment ID: {$payment.id}</H3>
<pre>
Payment on contribution ID: {$payment.contributionId} <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view/contribution" q="reset=1&action=view&cid=`$payment.contributionCid`&id=`$payment.contributionId`"}">View</a>
Attributed to Organization: {$payment.organizationName} (id={$payment.organizationCid})   <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view" q="reset=1&cid=`$payment.organizationCid`"}">View</a>
Found QuickBooks customer "{$payment.qbCustomerName}" (id={$payment.qbCustomerId})
Payment method: {$payment.paymentInstrumentLabel}
Payment date: {$payment.trxn_date}
Amount: {$payment.total_amount|crmMoney}
</pre>

<h4>Will create QuickBooks payment:</h4>
<pre>
QuickBooks customer "{$payment.qbCustomerName}" (id={$payment.qbCustomerId})
Invoice Number: {$payment.qbInvNumber} 
Payment Date: {$payment.trxn_date|crmDate:'%Y-%m-%d'} 
Payment method: {$payment.paymentInstrumentLabel}
Amount: {$payment.total_amount|crmMoney}
</pre>

