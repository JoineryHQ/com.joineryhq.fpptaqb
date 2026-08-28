CRM.$(function($) {

  var fpptaqbIsCreditmemoChange = function fpptaqbIsCreditmemoChange() {
    console.log('fpptaqb_is_creditmemo', $('input#fpptaqb_is_creditmemo').is(':checked'));
    if($('input#fpptaqb_is_creditmemo').is(':checked')) {
      $('.fpptaqb_creditmemo_hide').closest('tr').show();
    }
    else {
      $('.fpptaqb_creditmemo_hide').closest('tr').hide();
    }
  }
  // Give the bhfe elements table an id so we can handle it later.
  $('input#fpptaqb_is_creditmemo').closest('table').attr('id', 'bhfe_table');

  // Move all our bhfe table rows into the main table after 'from_email_address' -- if there is such a table.
  var tr = $('input#total_amount').closest('table').find('tr:last-child');
  console.log('tr', tr.length);
  if (tr.length) {
    $('table#bhfe_table .fpptaqb_creditmemo_field').closest('tr').insertAfter(tr).addClass('fpptaqb_creditmemo_field-tr');
  }

  var mjwsharedCrmSection = $('form .crm-section').last();
  console.log('mjwsharedCrmSection', mjwsharedCrmSection.length);
  if (mjwsharedCrmSection.length) {
    mjwsharedCrmSection.after('<table id="fpptaqb_creditmemo_fields"></table>');
    $('table#bhfe_table .fpptaqb_creditmemo_field').closest('tr').appendTo('table#fpptaqb_creditmemo_fields');
  }

  // Append desriptions after fields.
  for (id in CRM.vars.fpptaqb.descriptions) {
    $('#' + id).after('<br><span class="description">' + CRM.vars.fpptaqb.descriptions[id] + '</span>');
  }

  $('input#fpptaqb_is_creditmemo').change(fpptaqbIsCreditmemoChange);

  // Go head and fire the on-change handler for is_credit_memo.
  $('input#fpptaqb_is_creditmemo').change();

  // FIXME: creditmemo handling needs attention to work properly with mjwshared;
  // for now, we just replace our 'creditmemo' controls with a warning.
  $('table#fpptaqb_creditmemo_fields, tr.fpptaqb_creditmemo_field-tr').hide();
  $('table#fpptaqb_creditmemo_fields').after('<div class="alert alert-warning"><i role="img" aria-hidden="true" class="crm-i fa-info-circle"></i> QuickBooks credit-memo handling is not currently available; you can record this refund here, but it will not be synced to QuickBooks.</div>');

});




