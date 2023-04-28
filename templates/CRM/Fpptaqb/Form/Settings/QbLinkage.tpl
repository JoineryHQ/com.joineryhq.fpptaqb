{include file="CRM/Fpptaqb/common/settingsLocalNav.tpl"}

{* FIELDS (AUTOMATIC LAYOUT) *}

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}<div class="description">{$descriptions.$elementName}</div></div>
    {* Add authorization details after the expiryDate information *}
    {if $elementName == 'fpptaqb_quickbooks_shared_secret'}
      {if $showClientKeysMessage}
          <p class="content help">
            The Client ID and Client Secret are part of the QuickBooks Online App configuration.
            To find the values for these, please
            <a href="https://developer.intuit.com/app/developer/qbo/docs/develop/authentication-and-authorization/oauth-2.0#obtain-oauth2-credentials-for-your-app" target="_blank">
              follow the instructions on the Intuit site
            </a>.
          </p>
      {/if}
      {if $isRefreshTokenExpired}
        <div class="content messages status no-popup crm-error">
          Your QuickBooks Refresh Token has expired. You must
          <a class="redirect_url" href="{$redirect_url}" title="Authorize Quickbooks Application">Reauthorize</a>
          the QuickBooks application. Otherwise, contacts and contributions updates cannot be synced to QuickBooks. (Application authorization requires QBO credentials for an "Admin" User Type.)
        </div>
      {else}
        <div class="content messages status no-popup crm-not-you-message">
          <strong>
            {if $isNotYetAuthorized}
              Authorize your QuickBooks connection:
            {else}
              Reauthorize your QuickBooks connection:
            {/if}
          </strong><br>
          {if $isNotYetAuthorized}
            Once a Consumer Key and Shared Secret have been configured, you will need to
            <a class="redirect_url" href="{$redirect_url}" title="Authorize Quickbooks Application">Authorize</a>
            the QuickBooks application. (Application authorization requires QBO credentials for an "Admin" User Type.)
            <br>
            <br>
            You must add this Redirect URI to your application:
            <br>
            {$redirect_url}
          {else}
            At any time, you may
            <a class="redirect_url" href="{$redirect_url}" title="Authorize Quickbooks Application">Reauthorize</a>
            the QuickBooks application. (Application authorization requires QBO credentials for an "Admin" User Type.)
          {/if}
        </div>
      {/if}
    {/if}
    <div class="clear"></div>
  </div>
{/foreach}

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>