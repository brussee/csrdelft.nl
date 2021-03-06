{*
	beheer_maaltijd_archief.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="maaltijd-row-{$maaltijd->getMaaltijdId()}">
	<td>{$maaltijd->getDatum()|date_format:"%d-%m-%Y"} {$maaltijd->getTijd()|date_format:"%H:%M"}</td>
	<td>{$maaltijd->getTitel()}</td>
	<td class="lichtgrijs">{$maaltijd->getMaaltijdId()}</td>
	<td>&euro; {$maaltijd->getPrijsFloat()|string_format:"%.2f"}</td>
	<td>
		<div class="aanmeldingen-{$maaltijd->getMaaltijdId()} inline">
			<a title="Toon aanmeldingen" class="btn" onclick="$('div.aanmeldingen-{$maaltijd->getMaaltijdId()}').toggle();">{icon get="eye"} Toon <span class="dikgedrukt">{$maaltijd->getAantalAanmeldingen()}</span></a>
		</div>
		<div class="aanmeldingen-{$maaltijd->getMaaltijdId()} verborgen">
			<a title="Toon aanmeldingen" class="btn" onclick="$('div.aanmeldingen-{$maaltijd->getMaaltijdId()}').toggle();">{icon get="eye"} Verberg <span class="dikgedrukt">{$maaltijd->getAantalAanmeldingen()}</span></a>
		{foreach from=$maaltijd->getAanmeldingenArray() item=aanmelding}
			<li>
				{if $aanmelding[0] === 'gast'}Gast van
				{else}{ProfielModel::getLink($aanmelding[0], Instellingen::get('corvee', 'weergave_ledennamen_beheer'))}
				{/if}
				{if $aanmelding[1] === 'abo'} (abo)
				{elseif $aanmelding[0] !== $aanmelding[1]}
					{if $aanmelding[0] !== 'gast'} door
					{/if}
					&nbsp;{ProfielModel::getNaam($aanmelding[1], Instellingen::get('corvee', 'weergave_ledennamen_beheer'))}
				{/if}
			</li>
		{/foreach}
		</div>
	</td>
</tr>
{/strip}