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

  // Create an accordion with table below the main form section to contain our bhfe fields.
  $('div#payment-edit-section').after('\
    <div class="crm-accordion-wrapper" id="fpptaqb_creditmemo_accordion">\n\
      <div class="crm-accordion-header">Credit Memo</div>\
      <div class="crm-accordion-body">\n\
        <table id="fpptaqb_creditmemo_fields" class="form-layout-compressed"></table>\n\
      </div>\n\
    </div>\n\
  ');
  // Append sync message, if any, to accordion body.
  if (CRM.vars.fpptaqb.syncMessage) {
    $('table#fpptaqb_creditmemo_fields').before('<div class="help">' + CRM.vars.fpptaqb.syncMessage + '</div>');
  }

  // Move all our bhfe table rows into our table in accordion.
  // Note: we use the label as an identifier here, because HTML_QuickForm elements may be
  // frozen, in which case there are no fields with classnames for those elements.
  $('table#bhfe_table label[for^="fpptaqb_"').closest('tr').appendTo('table#fpptaqb_creditmemo_fields');
  
  // Append desriptions after fields.
  for (id in CRM.vars.fpptaqb.descriptions) {
    $('#' + id).after('<br><span class="description">' + CRM.vars.fpptaqb.descriptions[id] + '</span>');
  }
  
  $('input#fpptaqb_is_creditmemo').change(fpptaqbIsCreditmemoChange);
  
  // Go head and fire the on-change handler for is_credit_memo.
  $('input#fpptaqb_is_creditmemo').change();
});




