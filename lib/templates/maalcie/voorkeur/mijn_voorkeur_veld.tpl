{*
	mijn_voorkeur_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<td id="voorkeur-row-{$crid}" {if isset($uid)}class="voorkeur-ingeschakeld">
	<a href="{$smarty.const.maalcieUrl}/uitschakelen/{$crid}" class="btn post voorkeur-ingeschakeld"><input type="checkbox" checked="checked" /> Ja</a>
{else}class="voorkeur-uitgeschakeld">
	<a href="{$smarty.const.maalcieUrl}/inschakelen/{$crid}" class="btn post voorkeur-uitgeschakeld"><input type="checkbox" /> Nee</a>	
{/if}
</td>