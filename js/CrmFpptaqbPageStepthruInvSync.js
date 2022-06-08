/**
 * Javascript for the page CRM_Fpptaqb_Page_StepthruInvSync.
 */

/*global CRM, ts */
CRM.$(function($) {
  
  var buttonFadeTime = 500;
  var isLoading = false;
  var apiParams = {};
  
  var setLoading = function setLoading(buttonId) {
    if (isLoading) {
      console.log('loading, please wait.');
      return false;
    }
    isLoading = true;
    $('i#fpptaqb-sync-log-loading').css('visibility', 'visible');
    $('a.button.fpptaqb-sync-button').fadeOut(buttonFadeTime);
    return true;
  }
  
  var unsetLoading = function unsetLoading() {
    console.log('unsetLoading');
    isLoading = false;
    $('i#fpptaqb-sync-log-loading').css('visibility', 'hidden');
  }
  
  var showButtons = function showButtons(result, action) {
    console.log('showButtons', 'result', result, 'action', action);
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
        }
        else {
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
          }
        }
        else {
          showButtonIds.push('fpptaqb-button-next');                    
        }
        break;
      case 'hold':
        if (result.is_error) {
          switch (result.error_code) {
            case 'fpptaqb-400':
            case 'fpptaqb-404':
              showButtonIds.push('fpptaqb-button-next');
              break;
          }
        }
        else {
          showButtons(result, 'load');
        }
        break;
    }
    for (i in showButtonIds) {
      console.log('show button', '#' + showButtonIds[i]);
      $('#' + showButtonIds[i]).show();
    }
    
  }
  
  var processResult = function processResult(result, action) {
    console.log('processResult', 'result', result, 'action', action);
    
    // remove "isLoading" lock, but wait a little to ensure button has time to fadeout
    // (lazy way to prevent button double-click even on super-fast ajax returns.
    setTimeout(unsetLoading, buttonFadeTime);
    setTimeout(showButtons, buttonFadeTime, result, action);
    
    var text;
    if (result.is_error) {
      text = ts('Error') + ': ' + result.error_message;
    }
    else {
      text = result.values.text;
    }
    $('i#fpptaqb-sync-log-loading').before('<div>' + text + '</div>');
  }
  
  var handleActionButtonClick = function handleActionButtonClick(e) {
    var action = $(e.currentTarget).prop('action');
    if (!action) {
      // no action; use default behavior.
      return true;
    }
    
    if (!setLoading()) {
      return false;
    }
    CRM.api3('FpptaqbStepthruInvoice', action, apiParams).then(function(result) {
      processResult(result, action);
    }, function(error) {
      processResult(error, action);
    });
  }
  
  $('a.button.fpptaqb-sync-button').click(handleActionButtonClick);
  $('a.button.fpptaqb-sync-button#fpptaqb-button-begin').prop('action', 'load');
  $('a.button.fpptaqb-sync-button#fpptaqb-button-reload').prop('action', 'load');
  $('a.button.fpptaqb-sync-button#fpptaqb-button-hold').prop('action', 'hold');
  $('a.button.fpptaqb-sync-button#fpptaqb-button-sync').prop('action', 'sync');
  $('a.button.fpptaqb-sync-button#fpptaqb-button-sync-retry').prop('action', 'sync');
  $('a.button.fpptaqb-sync-button#fpptaqb-button-next').prop('action', 'load');
  
});