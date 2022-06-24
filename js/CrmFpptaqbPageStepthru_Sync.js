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
      if (actionButtonOptions.error[result.error_code]) {
        extraButtonIds = actionButtonOptions.error[result.error_code];
      }
      else if (actionButtonOptions.error.default) {
        extraButtonIds = actionButtonOptions.error.default;
      }
    }
    else {
      if (actionButtonOptions.status[result.values.statusCode]) {
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
    if (result.values.statistics) {
      if (result.values.statistics.countReady != undefined) {
        CRM.$('#fpptaqb-statistics-countItemsToSync').html(result.values.statistics.countReady);
      }
      if (result.values.statistics.countHeld != undefined) {
        CRM.$('#fpptaqb-statistics-countItemsHeld').html(result.values.statistics.countHeld);
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

  processResult: function processResult(result, action) {
    this.logDebug('processResult', 'result', result, 'action', action);

    var text;
    var textClass = '';
    if (result.status == 500) {
      appendToSyncLog('Fatal error: ' + result.responseText, 'crm-error', true);
      appendToSyncLog("Fatal error in CiviCRM. Reload page to continue.", 'crm-error');
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
    this.appendToSyncLog(text, textClass);

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
    var apiParams = {};

    if (lastResultApiParams != undefined) {
      for (i in lastResultApiParams) {
        apiParams[i] = CRM.fpptaqbStepthru.lastResult['values'][lastResultApiParams[i]];
      }
    }
    CRM.fpptaqbStepthru.logDebug('clicked action', action)
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
      CRM.fpptaqbStepthru.processResult(result, action);
    }, function (error) {
      CRM.fpptaqbStepthru.processResult(error, action);
    });


    // In the end, ignore default click action.
    return false;
  },

  appendToSyncLog: function appendToSyncLog(text, textClass, isDebug) {
    // If this is a debug message, and debugging is not enabled, just do nothing and return.
    if (isDebug && (! debugEnabled )) {
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
  }
};

/*global CRM, ts */
CRM.$(function ($) {
  $('a.button.fpptaqb-sync-button').click(CRM.fpptaqbStepthru.handleActionButtonClick);
});