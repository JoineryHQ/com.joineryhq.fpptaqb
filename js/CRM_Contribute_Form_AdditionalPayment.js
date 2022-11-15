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

  // Move all bhfe table rows into the main table after 'from_email_address'
  var tr = $('#from_email_address').closest('tr');
  $('table#bhfe_table tr').insertAfter(tr);
  
  // Append desriptions after fields.
  for (id in CRM.vars.fpptaqb.descriptions) {
    $('#' + id).after('<br><span class="description">' + CRM.vars.fpptaqb.descriptions[id] + '</span>');
  }
  
  $('input#fpptaqb_is_creditmemo').change(fpptaqbIsCreditmemoChange);
  
  // Go head and fire the on-change handler for is_credit_memo.
  $('input#fpptaqb_is_creditmemo').change();
});




