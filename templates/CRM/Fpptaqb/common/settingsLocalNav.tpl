{if $moreSettingsLinks}
<div id="mainTabContainer" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
  <ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
    {foreach from=$moreSettingsLinks item=moreSettingsLink}
      {if $moreSettingsLink.active}
        <li class="ui-corner-all ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-labelledby="{$moreSettingsLink.id}" aria-selected="true" aria-expanded="true">
          <a title="{$moreSettingsLink.label}" href="{$moreSettingsLink.url}" tabindex="-1" class="ui-tabs-anchor" id="{$moreSettingsLink.id}">{$moreSettingsLink.label}</a>
        </li>
      {else}
        <li class="ui-corner-all ui-tabs-tab ui-corner-top ui-state-default ui-tab" role="tab" tabindex="-1" aria-labelledby="{$moreSettingsLink.id}" aria-selected="false" aria-expanded="false">
          <a title="{$moreSettingsLink.label}" href="{$moreSettingsLink.url}" tabindex="-1" class="ui-tabs-anchor" id="{$moreSettingsLink.id}">{$moreSettingsLink.label}</a>
        </li>
      {/if}
    {/foreach}
  </ul>
</div>
{/if}

