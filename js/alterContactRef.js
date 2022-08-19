(function($){
  $(window).load(function(){
    if (CRM.vars.fpptaqb.contactRefCustomFieldId != undefined) {
      var selector = '#custom_' + CRM.vars.fpptaqb.contactRefCustomFieldId;
      var opts = $(selector).data('select2').opts;
      opts.ajax.data=function(term){return {isFpptaqbContactRef: '1', term: term}};
      $(selector).crmSelect2(opts);
    };
  });
})(cj || CRM.$ || jQuery);
