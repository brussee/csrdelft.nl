{* lidinstelling.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
<p class="instelling">
	<label class="instelling" for="inst_{$module}_{$id}">
		{if $reset}
			<a href="/instellingen/reset/{$module}/{$id}" class="btn post confirm ReloadPage vooriedereen" title="Voor iedereen deze instelling resetten naar de standaardwaarde: {ucfirst($default)}&#013;(Zie LidInstellingenModel.class.php)">{icon get=arrow_rotate_anticlockwise}</a>
		{/if}
		{$label}
	</label>
	{if $type === T::Enumeration}
		<select type="select" id="inst_{$module}_{$id}" name="{$module}_{$id}" class="FormElement SelectField" origvalue="{$waarde}">
			{foreach from=$opties key=value item=optie}
				<option {strip}

{if is_int($value)}
	value="{$optie}"{if $optie === $waarde} selected="selected"{/if}
{else}
	value="{$value}"{if $value === $waarde} selected="selected"{/if}
{/if}
				{/strip}>{ucfirst($optie)}</option>
			{/foreach}
		</select>
	{else}
		<input type="{if $type === T::String}text{elseif $type === T::Integer}number{/if}" id="inst_{$module}_{$id}" name="{$module}_{$id}" class="FormElement {if $type === T::String}InputField{elseif $type === T::Integer}IntField{/if}" value="{$waarde}" origvalue="{$waarde}"{if $type === T::String} maxlength="{$opties[1]}"{/if} />
	{/if}
	&nbsp;({ucfirst(strtolower($default))})
</p>