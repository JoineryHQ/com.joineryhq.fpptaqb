# FPPTA QuickBooks Sync
## com.joineryhq.fpptaqb

Custom QuickBooks Online integration for FPPTA.

The extension is licensed under [GPL-3.0](LICENSE.txt).

## Requirements
This extension requires the extension [FPPTA QuickBooks Helper](https://github.com/JoineryHQ/com.joineryhq.fpptaqbhelper).

## Configuration
After installation, you'll need to perform a few important configuration steps
to get this working:

1. Visit _Administer_ > _CiviContribute_ > _FPPTA QuickBooks Settings_ to 
   establish authentication with QuickBooks Online and configure other important
   settings.
2. Visit _Administer_ > _CiviContribute_ > _Financial Types_ ; for each
   Financial Type, edit the Financial Financial and find the
   field named "QuickBooks: Linked item"; set this field to the appropriate
   QuickBooks product or service matching this Financial Type. (Line items
   will be entered on invoices using the appropriate QuickBooks product or service,
   based on the line item's Financial Type.)

## Usage

### Step-thru manual sync

Navigate to _Contributions_ > _FPPTA QuickBooks Sync_ to access a dashboard for
manual step-thru synchronization of Contributions (which will be synced to 
QuickBooks invoices) and Payments (which will be synced to QuickBooks payments 
against the appropriate invoice.

This manual step-thru sync interface will allow you to preview each invoice 
and/or payment, one-at-a-time, and then choose either to sync it to QuickBooks, 
or to place it on hold for later processing (which may be handy if, for example, 
you wish to modify the Contribution record first).

### Scheduled automated sync
Configure the CiviCRM Scheduled Jobs "Sync QuickBooks Invoices" and "Sync 
QuickBooks Payments" on the schedule you prefer; these jobs will perform the 
same sync steps as in the step-thru manual sync, on a scheduled automated basis.


## Support
![screenshot](/images/joinery-logo.png)

Joinery provides services for CiviCRM including custom extension development,
training, data migrations, and more. We aim to keep this extension in good
working order, and will do our best to respond appropriately to issues reported
on its [github issue queue](https://github.com/JoineryHQ/com.joineryhq.fpptaqb/issues).
However, we place a priority on the needs of our clients, and we make no
guarantees of support here. If you require urgent or highly customized
improvements to this extension, we may suggest conducting a fee-based project
under our standard commercial terms, based on availability.  In any case, the
place to start is the [github issue queue](https://github.com/JoineryHQ/com.joineryhq.fpptaqb/issues) --
let us hear what you need and we'll be glad to help where we can.

And, if you need help with any other aspect of CiviCRM -- from hosting to custom
development to strategic consultation and more -- please contact us directly via
https://joineryhq.com