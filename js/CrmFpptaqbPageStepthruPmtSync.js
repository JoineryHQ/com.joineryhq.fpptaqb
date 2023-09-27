/**
 * Javascript for the page CRM_Fpptaqb_Page_StepthruPmtSync.
 */

/*global CRM, ts */
CRM.$(function ($) {
  CRM.fpptaqbStepthru.apiEntity = 'FpptaqbStepthruPayment';

  CRM.fpptaqbStepthru.showButtonOptions = {
    load: {
      error: {
        'fpptaqb-404': [
          'fpptaqb-button-next',
          'fpptaqb-button-list'
        ],
        default: [
          'fpptaqb-button-hold',
          'fpptaqb-button-reload',
          'fpptaqb-button-list'
        ]
      },
      'status': {
        204: [],
        default: [
          'fpptaqb-button-hold',
          'fpptaqb-button-reload',
          'fpptaqb-button-sync',
          'fpptaqb-button-list'
        ]
      }
    },
    sync: {
      error: {
        'fpptaqb-404': [
          'fpptaqb-button-next',
          'fpptaqb-button-list'
        ],
        'fpptaqb-409': [
          'fpptaqb-button-reload',
          'fpptaqb-button-list'
        ],
        'fpptaqb-503': [
          'fpptaqb-button-hold',
          'fpptaqb-button-reload',
          'fpptaqb-button-sync-retry',
          'fpptaqb-button-list'
        ],
        default: [
          'fpptaqb-button-hold',
          'fpptaqb-button-reload',,
          'fpptaqb-button-list'
        ]
      },
      'status': {
        default: [
          'fpptaqb-button-next',
          'fpptaqb-button-list'
        ]
      }
    },
    list: {
      error: {
        default: [
          'fpptaqb-button-next',
          'fpptaqb-button-list'
        ]
      },
      'status': {
        default: [
          'fpptaqb-button-next',
          'fpptaqb-button-list'
        ]
      }
    },
    hold: {
      error: {
        default: [
          'fpptaqb-button-next',
          'fpptaqb-button-list'
        ]
      },
      'status': {
        default: [
        ]
      }
    }
  };

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
  $('a.button.fpptaqb-sync-button#fpptaqb-button-list').prop({
    action: 'list',
    viewer: 'dialog'
  });
  $('a.button.fpptaqb-sync-button#fpptaqb-button-next').prop({
    action: 'load'
  });

});