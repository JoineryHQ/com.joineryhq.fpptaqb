/**
 * Javascript for the page CRM_Fpptaqb_Page_StepthruInvSync.
 */
CRM.fpptaqbStepthru = {
  isLoading: false,
  lastResult: {},
  debugEnabled: (CRM.vars.fpptaqb.debug_enabled * 1),
  apiEntity: '',
  showButtonOptions: {},

  showButtons: function showButtons(result, action){
    if (!Object.keys(this.showButtonOptions).length) {
      this.logDebug('CRM.fpptaqbStepthru.showButtonOptions is empty; is this object defined, as it should be, in *sync.js?');
      return;
    }

    // First hide all buttons.
    CRM.$('a.button.fpptaqb-sync-button').hide();

    var showButtonIds = ['fpptaqb-button-exit'];
    var extraButtonIds = [];
    var actionButtonOptions = this.showButtonOptions[action];

    if (result.is_error) {
      if (result.error_code && actionButtonOptions.error[result.error_code]) {
        extraButtonIds = actionButtonOptions.error[result.error_code];
      }
      else if (actionButtonOptions.error.default) {
        extraButtonIds = actionButtonOptions.error.default;
      }
    }
    else {
      if (result.values && result.values.statusCode && actionButtonOptions.status[result.values.statusCode]) {
        extraButtonIds = actionButtonOptions.status[result.values.statusCode];
      }
      else if (actionButtonOptions.status.default) {
        extraButtonIds = actionButtonOptions.status.default;
      }
    }

    // Concat extra button IDs onto show button IDs.
    showButtonIds = showButtonIds.concat(extraButtonIds);

    for (i in showButtonIds) {
      this.logDebug('show button', '#' + showButtonIds[i]);
      CRM.$('#' + showButtonIds[i]).show();
    }

  },

  /**
   * If civicrm debug is enabled, print a message to the console.
   */
  logDebug: function logDebug() {
    if (this.debugEnabled) {
      console.log.apply(console, arguments);
    }
  },

  setLoading: function setLoading(buttonId) {
    if (this.isLoading) {
      this.logDebug('loading, please wait.');
      return false;
    }
    this.isLoading = true;
    CRM.$('div#fpptaqb-sync-log-loading-wrapper').css('visibility', 'visible');
    CRM.$('a.button.fpptaqb-sync-button').hide();
    return true;
  },

  unsetLoading: function unsetLoading() {
    this.logDebug('unsetLoading');
    this.isLoading = false;
    CRM.$('div#fpptaqb-sync-log-loading-wrapper').css('visibility', 'hidden');
  },

  updateStatistics: function updateStatistics(result) {
    if (result.values && result.values.statistics) {
      if (result.values.statistics.countReady != undefined) {
        CRM.$('#fpptaqb-statistics-countItemsToSync').html(result.values.statistics.countReady);
      }
      if (result.values.statistics.countHeld != undefined) {
        CRM.$('#fpptaqb-statistics-countItemsHeld').html(result.values.statistics.countHeld);
        if (result.values.statistics.countHeld > 0) {
          CRM.$('span#fpptaqb-review-held').show();
        }
      }

      if (result.values.statistics.isMock != undefined) {
        if (result.values.statistics.isMock) {
          CRM.$('#fpptaqb-mock-warning').show();
        }
        else {
          CRM.$('#fpptaqb-mock-warning').hide();
        }
      }
    }
  },

  processResult: function processResult(result, action, viewer) {
    this.logDebug('processResult', 'result', result, 'action', action, 'viewer', viewer);

    if (viewer == 'dialog') {
      this.clearDialog();
      displayFunc = this.appendToDialog;
    }
    else {
      displayFunc = this.appendToSyncLog;
    }
    var text;
    var textClass = '';
    if (result.status == 500) {
      displayFunc('Fatal error: ' + result.responseText, 'crm-error', true);
      displayFunc("Fatal error in CiviCRM. Reload page to continue.", 'crm-error');
      CRM.$('a.button.fpptaqb-sync-button').hide();
      unsetLoading();
      return;
    }

    // store received data for reference in next button click.
    this.lastResult = result;

    if (result.is_error) {
      textClass = 'crm-error';
      text = ts('Error') + ': ' + result.error_message;
    }
    else {
      if(result.values && result.values.text) {
        text = result.values.text;
      }
      if(result.values && result.values.statusCode == 201) {
        textClass = 'status';
      }
    }
    displayFunc(text, textClass);

    // remove "isLoading" lock and show appropriate buttons
    this.unsetLoading();
    this.showButtons(result, action);

    // Update statistics if possible.
    this.updateStatistics(result);

    if (!result.is_error) {
      switch (action) {
        case 'hold':
        case 'sync':
          // we're handling a 'hold' or 'sync' response, and there's no error;
          // so click the "load next" button.
          this.logDebug('hold/sync response received, now clicking "next".');
          CRM.$('a.button.fpptaqb-sync-button#fpptaqb-button-next').click();
          break;
      }
    }
  },

  handleActionButtonClick: function handleActionButtonClick(e) {
    CRM.fpptaqbStepthru.logDebug('previous result: ', CRM.fpptaqbStepthru.lastResult);
    var action = CRM.$(e.currentTarget).prop('action');
    var lastResultApiParams = CRM.$(e.currentTarget).prop('lastResultApiParams');
    var myApiParams = CRM.$(e.currentTarget).prop('myApiParams');
    var viewer = CRM.$(e.currentTarget).prop('viewer');
    var apiParams = {};

    if (lastResultApiParams != undefined) {
      for (i in lastResultApiParams) {
        apiParams[i] = CRM.fpptaqbStepthru.lastResult['values'][lastResultApiParams[i]];
      }
    }
    else if (myApiParams != undefined) {
      for (i in myApiParams) {
        apiParams[i] = myApiParams[i];
      }
    }
    CRM.fpptaqbStepthru.logDebug('clicked action', action)
    // If any dialog exists, clear it.
    CRM.fpptaqbStepthru.clearDialog();
    if (!action) {
      // no action; use default behavior.
      CRM.fpptaqbStepthru.logDebug('still loading previous action; skip');
      return true;
    }

    if (!CRM.fpptaqbStepthru.setLoading()) {
      // We're still in the midst of loading something, so just silently do nothing
      // (i.e., avoid double-click)
      return false;
    }

    CRM.fpptaqbStepthru.logDebug('calling api', action, 'params', apiParams);
    CRM.api3(CRM.fpptaqbStepthru.apiEntity, action, apiParams).then(function (result) {
      CRM.fpptaqbStepthru.processResult(result, action, viewer);
    }, function (error) {
      CRM.fpptaqbStepthru.processResult(error, action, viewer);
    });


    // In the end, ignore default click action.
    return false;
  },

  appendToSyncLog: function appendToSyncLog(text, textClass, isDebug) {
    // If this is a debug message, and debugging is not enabled, just do nothing and return.
    if (isDebug && (! this.debugEnabled )) {
      return;
    }
    // Append the message with the given CSS class.
    CRM.$('div#fpptaqb-sync-log-loading-wrapper').before('<hr><div class="' + textClass + '">' + text + '</div>');
    // Scroll buttons to bottom of viewport.
    CRM.$('html').animate({
      scrollTop: CRM.$('div.action-link').offset().top
        + (CRM.$('div.action-link').height() * 2)
        - CRM.$(window).height()
    }, 50);
  },

  appendToDialog: function appendToDialog(text, textClass, isDebug) {
    // If this is a debug message, and debugging is not enabled, just do nothing and return.
    if (isDebug && (! this.debugEnabled )) {
      return;
    }
    // Append the message with the given CSS class.
    CRM.$('div#fpptaqb-sync-dialog').append('<div class="' + textClass + '">' + text + '</div>');
    CRM.$('div#fpptaqb-sync-dialog').dialog({
      'height': 100,
      'maxHeight': 500,
      'width': '70%',
      'modal': true
    });
  },

  clearDialog: function clearDialog() {
    if (CRM.$('div#fpptaqb-sync-dialog').dialog("instance")) {
      CRM.$('div#fpptaqb-sync-dialog').dialog("close");
      CRM.$('div#fpptaqb-sync-dialog').dialog("destroy");
    }
    CRM.$('div#fpptaqb-sync-dialog').remove();
    // Create a div for the dialog.
    CRM.$('body').append('<div id="fpptaqb-sync-dialog" style="display: none"></div>');
    // Populate that div with a tabbable link that does nothing and displays off
    // the page. This is a workaround to defeat ui-dialog's unstoppable desire
    // to place focus on the first link (which will cause the dialog to scroll,
    // thus obscuring the top of the content).
    CRM.$('div#fpptaqb-sync-dialog').append('<a href="#"  style="position: relative; top: -500px; display: block; height: 10px; margin-bottom: -10px;">&nbsp;</a>');
  }
};

/*global CRM, ts */
CRM.$(function ($) {
  $('a.button.fpptaqb-sync-button').click(CRM.fpptaqbStepthru.handleActionButtonClick);
});