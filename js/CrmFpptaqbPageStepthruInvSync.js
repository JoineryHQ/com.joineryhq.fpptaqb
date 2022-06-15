/**
 * Javascript for the page CRM_Fpptaqb_Page_StepthruInvSync.
 */

/*global CRM, ts */
CRM.$(function ($) {

  var isLoading = false;
  var lastResult = {};

  /**
   * If civicrm debug is enabled, print a message to the console.
   */
  var logDebug = function logDebug() {
    if (CRM.vars.fpptaqb.debug_enabled * 1) {
      console.log.apply(console, arguments);
    }
  }

  var setLoading = function setLoading(buttonId) {
    if (isLoading) {
      logDebug('loading, please wait.');
      return false;
    }
    isLoading = true;
    $('div#fpptaqb-sync-log-loading-wrapper').css('visibility', 'visible');
    $('a.button.fpptaqb-sync-button').hide();
    return true;
  }

  var unsetLoading = function unsetLoading() {
    logDebug('unsetLoading');
    isLoading = false;
    $('div#fpptaqb-sync-log-loading-wrapper').css('visibility', 'hidden');
  }

  var showButtons = function showButtons(result, action) {
    logDebug('showButtons', 'result', result, 'action', action);
    // First hide all buttons.
    $('a.button.fpptaqb-sync-button').hide();

    var showButtonIds = ['fpptaqb-button-exit'];
    // Show correct buttons based on action and result
    switch (action) {
      case 'load':
        if (result.is_error) {
          switch (result.error_code) {
            case 'fpptaqb-404':
              showButtonIds.push('fpptaqb-button-next');
              break;
            case 'fpptaqb-500':
              showButtonIds.push('fpptaqb-button-hold');
              showButtonIds.push('fpptaqb-button-reload');
              break;
          }
        } else {
          showButtonIds.push('fpptaqb-button-reload');
          showButtonIds.push('fpptaqb-button-sync');
        }
        break;
      case 'sync':
        if (result.is_error) {
          switch (result.error_code) {
            case 'fpptaqb-400':
            case 'fpptaqb-404':
              showButtonIds.push('fpptaqb-button-next');
              break;
            case 'fpptaqb-409':
              showButtonIds.push('fpptaqb-button-reload');
              break;
            case 'fpptaqb-503':
              showButtonIds.push('fpptaqb-button-hold');
              showButtonIds.push('fpptaqb-button-reload');
              break;
            default:
              showButtonIds.push('fpptaqb-button-hold');
              showButtonIds.push('fpptaqb-button-reload');
              break;
          }
        } else {
          showButtonIds.push('fpptaqb-button-next');
        }
        break;
      case 'hold':
        if (result.is_error) {
          showButtonIds.push('fpptaqb-button-next');
        }
        break;
    }
    for (i in showButtonIds) {
      logDebug('show button', '#' + showButtonIds[i]);
      $('#' + showButtonIds[i]).show();
    }

  }

  var processResult = function processResult(result, action) {
    logDebug('processResult', 'result', result, 'action', action);

    // store received data for reference in next button click.
    lastResult = result;

    var text;
    if (result.is_error) {
      text = ts('Error') + ': ' + result.error_message;
    } else {
      if(result.values && result.values.text) {
        text = result.values.text;
      }
    }
    appendToSyncLog('<div>' + text + '</div>');

    // remove "isLoading" lock and show appropriate buttons
    unsetLoading();
    showButtons(result, action);

    if (!result.is_error) {
      switch (action) {
        case 'hold':
        case 'sync':
          // we're handling a 'hold' or 'sync' response, and there's no error;
          // so click the "load next" button.
          logDebug('hold/sync response received, now clicking "next".');
          $('a.button.fpptaqb-sync-button#fpptaqb-button-next').click();
          break;
      }
    }
  }

  var handleActionButtonClick = function handleActionButtonClick(e) {
    logDebug('previous result: ', lastResult);
    var action = $(e.currentTarget).prop('action');
    var lastResultApiParams = $(e.currentTarget).prop('lastResultApiParams');
    var apiParams = {};

    if (lastResultApiParams != undefined) {
      for (i in lastResultApiParams) {
        apiParams[i] = lastResult['values'][lastResultApiParams[i]];
      }
    }
    logDebug('clicked action', action)
    if (!action) {
      // no action; use default behavior.
      logDebug('still loading previous action; skip');
      return true;
    }

    if (!setLoading()) {
      // We're still in the midst of loading something, so just silently do nothing
      // (i.e., avoid double-click)
      return false;
    }

    logDebug('calling api', action, 'params', apiParams);
    CRM.api3('FpptaqbStepthruInvoice', action, apiParams).then(function (result) {
      processResult(result, action);
    }, function (error) {
      processResult(error, action);
    });


    // In the end, ignore default click action.
    return false;
  }

  var appendToSyncLog = function appendToSyncLog(text) {
    $('div#fpptaqb-sync-log-loading-wrapper').before('<hr>' + text);
    // Scroll buttons to bottom of viewport.
    $('html').animate({
      scrollTop: $('div.action-link').offset().top
        + $('div.action-link').height()
        - $(window).height()
    }, 50);
  }

  // Assign click handler to all action buttons
  $('a.button.fpptaqb-sync-button').click(handleActionButtonClick);

  // Define action and apiParam properties for each button.
  $('a.button.fpptaqb-sync-button#fpptaqb-button-begin').prop({
    action: 'load'
  });
  $('a.button.fpptaqb-sync-button#fpptaqb-button-reload').prop({
    action: 'load',
    lastResultApiParams: {id: 'id'}
  });
  $('a.button.fpptaqb-sync-button#fpptaqb-button-hold').prop({
    action: 'hold',
    lastResultApiParams: {id: 'id'}
  });
  $('a.button.fpptaqb-sync-button#fpptaqb-button-sync').prop({
    action: 'sync',
    lastResultApiParams: {id: 'id', hash: 'hash'}
  });
  $('a.button.fpptaqb-sync-button#fpptaqb-button-sync-retry').prop({
    action: 'sync',
    lastResultApiParams: {id: 'id', hash: 'hash'}
  });
  $('a.button.fpptaqb-sync-button#fpptaqb-button-next').prop({
    action: 'load'
  });

});