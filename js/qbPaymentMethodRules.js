/*global CRM, ts */
CRM.$(function ($) {

  fpptaqbQbPaymentMethodRules = {

    // Counter to help ensure unique row id.
    ruleCounter: 0,

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
      tr.attr('id', 'qbPaymentMethodRule-' + (++fpptaqbQbPaymentMethodRules.ruleCounter) );

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
      var i = 0;
      var rules = JSON.parse($('#fpptaqb_qb_payment_method_rules').val());
      for (i in rules) {
        // Append one rule to represent this row.
        var tr = fpptaqbQbPaymentMethodRules.appendRuleRow();
        // Set attributes and values for fields in this row.
        // crmPaymentMethod:
        tr.find('select#crmPaymentMethod')
          .attr({
            name: 'crmPaymentMethod[' + i + ']',
            id: 'crmPaymentMethod-' + i
          })
          .val(rules[i].crmPaymentMethod)
          .change(this.updateRules);
        // cardType:
        tr.find('select#cardType')
          .attr({
            name: 'cardType[' + i + ']',
            id: 'cardType-' + i
          })
          .val(rules[i].cardType)
          .change(this.updateRules);
        // qbPaymentMethod
        tr.find('select#qbPaymentMethod')
          .attr({
            name: 'qbPaymentMethod[' + i + ']',
            id: 'qbPaymentMethod-' + i,
          })
          .addClass('qbPaymentMethod')
          .val(rules[i].qbPaymentMethod)
          .change(this.updateRules);
      }
      fpptaqbQbPaymentMethodRules.ruleCounter = rules.length;
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