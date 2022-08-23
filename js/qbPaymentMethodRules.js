/*global CRM, ts */
CRM.$(function ($) {

  fpptaqbQbPaymentMethodRules = {
    ruleCount: 0,

    // On load, we store the saved rules in this var. We'll modify this on-page
    // per user actions.
    liveRules: CRM.vars.fpptaqb.fpptaqb_qb_payment_method_rules,

    formatRows: function formatRows() {
      $('table#qbPaymentMethodRules tbody tr:visible').each(function(idx, tr) {
        $(tr).removeClass('odd-row');
        $(tr).removeClass('even-row');
        var rowClass = 'odd-row';
        if(idx % 2 == 0) {
          rowClass = 'even-row';
        }
        $(tr).addClass(rowClass);
        CRM.$('a.fpptaqb-move-up, a.fpptaqb-move-down').css('visibility', 'visible');
        CRM.$('table#qbPaymentMethodRules tbody tr:visible:first a.fpptaqb-move-up').css('visibility', 'hidden');
        CRM.$('table#qbPaymentMethodRules tbody tr:visible:last a.fpptaqb-move-down').css('visibility', 'hidden');
      });
    },

    showHideRulesTable: function showHideRulesTable() {
      if ($('table#qbPaymentMethodRules tbody tr:visible').length) {
        $('#fpptaqb-no-rules-message').hide();
        $('table#qbPaymentMethodRules').show();
      }
      else {
        $('#fpptaqb-no-rules-message').show();
        $('table#qbPaymentMethodRules').hide();
      }
    },

    appendRuleRow: function appendRuleRow() {
      var tr = $('tr#qbPaymentMethodRule-template').clone(true, true).show();
      tr.attr('id', 'qbPaymentMethodRule-' + i);

      tr.find('select#crmPaymentMethod').addClass('crmPaymentMethod');
      tr.find('select#cardType').addClass('cardType');
      tr.find('select#qbPaymentMethod').addClass('qbPaymentMethod');
      tr.appendTo('table#qbPaymentMethodRules tbody');
      fpptaqbQbPaymentMethodRules.formatRows();
      $('table#qbPaymentMethodRules').show();
      fpptaqbQbPaymentMethodRules.showHideRulesTable();

      return tr;
    },

    renderRules: function renderRules() {
      i = 0;

      for (i in this.liveRules) {

        var tr = fpptaqbQbPaymentMethodRules.appendRuleRow();

        tr.find('select#crmPaymentMethod')
          .attr({
            name: 'crmPaymentMethod[' + i + ']',
            id: 'crmPaymentMethod-' + i
          })
          .val(this.liveRules[i]['crmPaymentMethod'])
          .change(this.updateRules);

        tr.find('select#cardType')
          .attr({
            name: 'cardType[' + i + ']',
            id: 'cardType-' + i
          })
          .val(this.liveRules[i]['cardType'])
          .change(this.updateRules);

        tr.find('select#qbPaymentMethod')
          .attr({
            name: 'qbPaymentMethod[' + i + ']',
            id: 'qbPaymentMethod-' + i,
          })
          .addClass('qbPaymentMethod')
          .val(this.liveRules[i]['qbPaymentMethod'])
          .change(this.updateRules);
      }
      fpptaqbQbPaymentMethodRules.rulesCount = ( (i *  1) + 1);
    },

    updateRules: function updateRules() {
      var updatedRules = [];
      $('table#qbPaymentMethodRules tbody tr:visible').each(function(idx, tr) {
        updatedRules.push({
          crmPaymentMethod: $(tr).find('select.crmPaymentMethod').val(),
          cardType: $(tr).find('select.cardType').val(),
          qbPaymentMethod: $(tr).find('select.qbPaymentMethod').val()
        });
      });
      this.liveRules = updatedRules;
      $('#fpptaqb_qb_payment_method_rules').val(JSON.stringify(updatedRules));
    },

    moveUpClick: function moveUpClick(e) {
      e.preventDefault();
      var ruleTr = $(e.target).closest('tr');
      var previousTr = ruleTr.prev();
      previousTr.before(ruleTr);
      fpptaqbQbPaymentMethodRules.formatRows();
      fpptaqbQbPaymentMethodRules.updateRules();
    },

    moveDownClick: function moveDownClick(e) {
      e.preventDefault();
      var ruleTr = $(e.target).closest('tr');
      var nextTr = ruleTr.next();
      nextTr.after(ruleTr);
      fpptaqbQbPaymentMethodRules.formatRows();
      fpptaqbQbPaymentMethodRules.updateRules();
    },

    deleteRuleClick: function deleteRuleClick(e) {
      e.preventDefault();
      var ruleTr = $(e.target).closest('tr');
      ruleTr.remove();
      fpptaqbQbPaymentMethodRules.formatRows();
      fpptaqbQbPaymentMethodRules.updateRules();
      fpptaqbQbPaymentMethodRules.showHideRulesTable();
    },

    addRuleClick: function addRuleClick(e) {
      e.preventDefault();
      var tr = fpptaqbQbPaymentMethodRules.appendRuleRow();
      fpptaqbQbPaymentMethodRules.updateRules();
    }
  };

  $('a#moveUp').on('click', fpptaqbQbPaymentMethodRules.moveUpClick);
  $('a#moveDown').on('click', fpptaqbQbPaymentMethodRules.moveDownClick);
  $('a#deleteRule').on('click', fpptaqbQbPaymentMethodRules.deleteRuleClick);
  $('a#addRule').on('click', fpptaqbQbPaymentMethodRules.addRuleClick);
  fpptaqbQbPaymentMethodRules.renderRules();
  fpptaqbQbPaymentMethodRules.showHideRulesTable();

});