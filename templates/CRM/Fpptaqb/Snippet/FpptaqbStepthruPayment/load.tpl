<H3>Processing payment ID: {$payment.id}</H3>
<pre>
Payment on contribution ID: {$payment.contributionId} <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view/contribution" q="reset=1&action=view&cid=`$payment.contributionCid`&id=`$payment.contributionId`"}">View</a>
Attributed to Organization: {$payment.organizationName} (id={$payment.organizationCid})   <a target="_blank" href="{crmURL fb=1 p="civicrm/contact/view" q="reset=1&cid=`$payment.organizationCid`"}">View</a>
Found QuickBooks customer "{$payment.qbCustomerName}" (id={$payment.qbCustomerId})
Payment method: {$payment.paymentInstrumentLabel}
{if $payment.payment_instrument_id == 5}{* EFT *}
EFT Transaction ID: {$payment.trxn_id}
{elseif $payment.payment_instrument_id == 4}{* check *}
Check Number: {$payment.check_number}
{elseif $payment.payment_instrument_id == 1 || $payment.payment_instrument_id == 2}{* Credit Card or Debit Card *}
Card Type: {$payment.cardTypeLabel}
Card Last-4: {$payment.pan_truncation}
{/if}
Payment date: {$payment.trxn_date}
Amount: {$payment.total_amount|crmMoney}
</pre>

<h4>Will create QuickBooks payment:</h4>
<pre>
QuickBooks customer "{$payment.qbCustomerName}" (id={$payment.qbCustomerId})
Invoice Number: {$payment.qbInvNumber}
Invoice Id: {$payment.qbInvId}
Reference no.: {$payment.qbReferenceNo}
Payment Date: {$payment.trxn_date|crmDate:'%Y-%m-%d'}
Payment method: {if $payment.qbPaymentMethodId}{$payment.qbPaymentMethodLabel}{else}<strong>{ts}WARNING: NONE FOUND{/ts}</strong>{/if} 
Amount: {$payment.total_amount|crmMoney}
Deposit to account: {$payment.qbDepositToAccountLabel}
</pre>

