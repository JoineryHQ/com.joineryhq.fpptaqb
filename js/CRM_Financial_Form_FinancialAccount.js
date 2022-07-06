CRM.$(function($) {
  // Give the bhfe elements table an id so we can handle it later.
  $('select#fpptaqb_quickbooks_id').closest('table').attr('id', 'bhfe_table');

  // Move all bhfe table rows into the main table after 'financial_account_type_id'
  var tr = $('#financial_account_type_id').closest('tr');
  $('table#bhfe_table tr').insertAfter(tr);
  
  // Append desriptions after fields.
  for (id in CRM.vars.fpptaqb.descriptions) {
    $('#' + id).after('<br><span class="description">' + CRM.vars.fpptaqb.descriptions[id] + '</span>');
  }
  
});




