/**
 * Javascript for the page CRM_Fpptaqb_Page_StepthruInvSync.
 */

/*global CRM, ts */
CRM.$(function ($) {

  var isLoading = false;
  var lastResult = {};
  var debugEnabled = (CRM.vars.fpptaqb.debug_enabled * 1);

  /**
   * If civicrm debug is enabled, print a message to the console.
   */
  var logDebug = function logDebug() {
    if (debugEnabled) {
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
          // statusCode 204 ("No Content") means there are no more items
          // to process, so we can leave all buttons hidden.
          if (result.values.statusCode != 204) {
            showButtonIds.push('fpptaqb-button-hold');
            showButtonIds.push('fpptaqb-button-reload');
            showButtonIds.push('fpptaqb-button-sync');
          }
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
              showButtonIds.push('fpptaqb-button-sync-retry');
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
  
  var updateStatistics = function updateStatistics(result) {
    if (result.values.statistics) {
      if (result.values.statistics.countReady != undefined) {
        $('#fpptaqb-statistics-countItemsToSync').html(result.values.statistics.countReady);
      }
      if (result.values.statistics.countHeld != undefined) {
        $('#fpptaqb-statistics-countItemsHeld').html(result.values.statistics.countHeld);
      }
      
      if (result.values.statistics.isMock != undefined) {
        if (result.values.statistics.isMock) {
          $('#fpptaqb-mock-warning').show();
        }
        else {
          $('#fpptaqb-mock-warning').hide();
        }
      }
    }
  }

  var processResult = function processResult(result, action) {
    logDebug('processResult', 'result', result, 'action', action);

    var text;
    var textClass = '';
    if (result.status == 500) {
      appendToSyncLog('Fatal error: ' + result.responseText, 'crm-error', true);
      appendToSyncLog("Fatal error in CiviCRM. Reload page to continue.", 'crm-error');
      $('a.button.fpptaqb-sync-button').hide();
      unsetLoading();
      return;     
    }
    
    // store received data for reference in next button click.
    lastResult = result;
    
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
    appendToSyncLog(text, textClass);

    // remove "isLoading" lock and show appropriate buttons
    unsetLoading();
    showButtons(result, action);
    
    // Update statistics if possible.
    updateStatistics(result);

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

  var appendToSyncLog = function appendToSyncLog(text, textClass, isDebug) {
    // If this is a debug message, and debugging is not enabled, just do nothing and return.
    if (isDebug && (! debugEnabled )) {
      return;
    }
    // Append the message with the given CSS class.
    $('div#fpptaqb-sync-log-loading-wrapper').before('<hr><div class="' + textClass + '">' + text + '</div>');
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