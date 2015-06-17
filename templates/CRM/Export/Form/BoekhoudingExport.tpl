<div class="help">Geef in dit formulier de export parameters op en druk vervolgens op exporteren</div>
{if $errorMessage } <div class="crm-error">{ $errorMessage }</div>{/if}
{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">
		{if $form.$elementName.name eq 'periode_start' OR $form.$elementName.name eq 'periode_eind'}
			{include file="CRM/common/jcalendar.tpl" elementName=$form.$elementName.name}
		{else}
			{$form.$elementName.html}
		{/if}
	</div>
    <div class="clear"></div>
  </div>
{/foreach}

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
