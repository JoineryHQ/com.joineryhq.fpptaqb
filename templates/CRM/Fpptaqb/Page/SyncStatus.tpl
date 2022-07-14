{if $apiError}
  <div class="crm-error">{$apiError}</div>
{else}
  {if $qbInvId}
    <div class="help">
      <p>{ts}This contribution has been synced to a QuickBooks invoice.{/ts}</p>
    </div>
  {/if}
  <table class="crm-info-panel">
      <tr>
        <td class="label">{ts}Invoice Number{/ts}</td>
        <td>{$contribution.invoice_number}</td>
      </tr>
      <tr>
        <td class="label">{ts}Payor / CiviCRM contact{/ts}</td>
        <td>{$contribution.display_name}</td>
      </tr>
      <tr>
        <td class="label">{ts}Received{/ts}</td>
        <td>{$contribution.receive_date}</td>
      </tr>
      <tr>
        <td class="label">{ts}Total Amount{/ts}</td>
        <td>{$contribution.total_amount|crmMoney}</td>
      </tr>
      <tr>
        <td class="label">{ts}Payments{/ts}</td>
        <td>{$paymentRows|@count} payments found in CiviCRM.
          {if $paymentRows && $paymentRows|@count}
            <br/>
            <table>
              <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Synced to QB?</th>
              </tr>
              {foreach from=$paymentRows item=paymentRow}
                <tr>
                  <td>{$paymentRow.trxn_date}</td>
                  <td>{$paymentRow.total_amount|crmMoney}</td>
                  <td>{if $paymentRow.qbPmtId}Yes{else}Not yet{/if}</td>
                </tr>
              {/foreach}
            </table>
          {/if}
        </td>
      </tr>
  </table>


  <table class="crm-info-panel">
    <tr>
      <td class="label">{ts}Synced:{/ts}</td>
      <td>
        {if $qbInvId}
          To QuickBooks invoice id={$qbInvId}
        {else}
          Not yet.
        {/if}
      </td>
    </tr>

    {if $qbInvId && $isMock}
      <tr>
        <td class="label crm-error" scope="row">{ts}Undo mock sync{/ts}</td>
        <td class="">
          <p>This was synced with the "Mock" placeholder sync rather than a live QuickBooks account. You may undo that sync if you'd like to try it again.</p>
          {crmButton fb=1 p="civicrm/fpptaqb/itemaction" q="type=inv&itemaction=unsync&id=$id" title="Undo mock sync" icon=""}Undo mock sync{/crmButton}
        </td>
      </tr>
    {/if}
  </table>  

  {if !$qbInvId}
    <div class="help">
      <p>{ts}This contribution has not yet been synced to QuickBooks. Below is a preview of the output that would appear for this contribution and its various payments (if any) in the Step-Thru interfaces for Invoices and Payments.{/ts}</p>
    </div>

    <h2>{ts}Contribution{/ts}:</h2>
    {$invLoadText}

    <h2>{ts}Payment(s){/ts}:</h2>
    {if $pmtLoadTexts|@count}
      {foreach from=$pmtLoadTexts item=pmtLoadText}
        {$pmtLoadText}
      {/foreach}
    {else}
    <p>{ts}No payments found for this contribution.{/ts}
    {/if}

  {/if}
{/if}