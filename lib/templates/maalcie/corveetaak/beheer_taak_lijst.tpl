{*
	beheer_taak_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="corveetaak-row-{$taak->getTaakId()}" class="taak-datum-{$taak->getDatum()}
{if ($taak->getBeginMoment() < strtotime('-1 day') and !isset($maaltijd)) or $taak->getIsVerwijderd()} taak-oud{/if}
{if !isset($show) and !$prullenbak} verborgen{/if}">
	<td>
{if $taak->getIsVerwijderd()}
		<a href="{$smarty.const.maalcieUrl}/herstel/{$taak->getTaakId()}" title="Corveetaak herstellen" class="btn post">{icon get="arrow_undo"}</a>
{else}
		<a href="{$smarty.const.maalcieUrl}/bewerk/{$taak->getTaakId()}" title="Taak wijzigen" class="btn post popup">{icon get="pencil"}</a>
	{if $taak->getCorveeRepetitieId()}
		<a href="/corveerepetities/beheer/{$taak->getCorveeRepetitieId()}" title="Wijzig gekoppelde corveerepetitie" class="btn popup">{icon get="calendar_edit"}</a>
	{else}
		<div class="inline" style="width: 28px;"></div>
	{/if}
{/if}
{if !isset($maaltijd) and $taak->getMaaltijdId()}
	<a href="/corveebeheer/maaltijd/{$taak->getMaaltijdId()}" title="Beheer maaltijdcorvee" class="btn">{icon get="cup_link"}</a>
{/if}
	</td>
	<td class="text-center" style="width: 50px;">
{assign var=aantal value=$taak->getAantalKeerGemaild()}
{if !$taak->getIsVerwijderd() and (!isset($maaltijd) or !$maaltijd->getIsVerwijderd())}
	{assign var="wijzigbaar" value="true"}
	{if $taak->getUid()}
		{$aantal}x
	{/if}
	<div class="float-right">
	{if $taak->getUid()}
		<a href="{$smarty.const.maalcieUrl}/email/{$taak->getTaakId()}" title="Verstuur een (extra) herinnering voor deze taak" class="btn post confirm">
	{/if}
{/if}
{if $taak->getIsTelaatGemaild()}
			{icon get="email_error" title="Laatste herinnering te laat verstuurd!&#013;"|cat:$taak->getWanneerGemaild()}
{elseif $aantal < 1}
	{if $taak->getUid()}
			{icon get="email" title="Niet gemaild"}
	{/if}
{elseif $aantal === 1}
			{icon get="email_go" title=$taak->getWanneerGemaild()}
{elseif $aantal > 1}
			{icon get="email_open" title=$taak->getWanneerGemaild()}
{/if}
{if isset($wijzigbaar)}
	{if $taak->getUid()}
		</a>
	{/if}
	</div>
{/if}
	</td>
	<td>{$taak->getDatum()|date_format:"%a %e %b"}</td>
	<td style="width: 100px;">{$taak->getCorveeFunctie()->naam}</td>
	<td class="niet-dik taak-{if $taak->getUid()}toegewezen{elseif  $taak->getBeginMoment() < strtotime(Instellingen::get('corvee', 'waarschuwing_taaktoewijzing_vooraf'))}warning{else}open{/if}">
{if isset($wijzigbaar)}
		<a href="{$smarty.const.maalcieUrl}/toewijzen/{$taak->getTaakId()}" id="taak-{$taak->getTaakId()}" title="Deze taak toewijzen aan een lid&#013;Sleep om te ruilen" class="btn post popup dragobject ruilen" style="position: static;"{if $taak->getUid()} uid="{$taak->getUid()}">{icon get="user_green"}{else}>{icon get="user_red"}{/if}</a>
{/if}
{if $taak->getUid()}
		&nbsp;{ProfielModel::getLink($taak->getUid(), Instellingen::get('corvee', 'weergave_ledennamen_beheer'))}
{/if}
	</td>
	<td{if $taak->getUid() and ($taak->getPunten() !== $taak->getPuntenToegekend() or $taak->getBonusMalus() !== $taak->getBonusToegekend()) and $taak->getBeginMoment() < strtotime(Instellingen::get('corvee', 'waarschuwing_puntentoewijzing_achteraf'))} class="taak-warning"{/if}>
		{$taak->getPuntenToegekend()}
{if $taak->getBonusToegekend() > 0}
	+
{/if}
{if $taak->getBonusToegekend() !== 0}
	{$taak->getBonusToegekend()}
{/if}
&nbsp;van {$taak->getPunten()}
{if $taak->getBonusMalus() > 0}
	+
{/if}
{if $taak->getBonusMalus() !== 0}
	{$taak->getBonusMalus()}
{/if}
{if isset($wijzigbaar) and $taak->getUid()}
		<div class="float-right">
	{if $taak->getWanneerToegekend()}
		<a href="{$smarty.const.maalcieUrl}/puntenintrekken/{$taak->getTaakId()}" title="Punten intrekken" class="btn post">{icon get="medal_silver_delete"}</a>
	{else}
		<a href="{$smarty.const.maalcieUrl}/puntentoekennen/{$taak->getTaakId()}" title="Punten toekennen" class="btn post">{icon get="award_star_add"}</a>
	{/if}
{/if}
		</div>
	</td>
	<td class="col-del">
{if $taak->getIsVerwijderd()}
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$taak->getTaakId()}" title="Corveetaak definitief verwijderen" class="btn post confirm range"><input type=checkbox id="box-{$taak->getTaakId()}" name="del-taak" />{icon get="cross"}</a>
{else}
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$taak->getTaakId()}" title="Corveetaak naar de prullenbak verplaatsen" class="btn post range"><input type=checkbox id="box-{$taak->getTaakId()}" name="del-taak" />{icon get="bin_closed"}</a>
{/if}
	</td>
</tr>
{/strip}
