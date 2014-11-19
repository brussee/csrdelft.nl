{*
	beheer_vrijstelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="vrijstelling-row-{$vrijstelling->getUid()}">
	<td>
		<a href="{$smarty.const.maalcieUrl}/bewerk/{$vrijstelling->getUid()}" title="Vrijstelling wijzigen" class="btn rounded post modal">{icon get="pencil"}</a>
	</td>
	<td>{Lid::naamLink($vrijstelling->getUid(), Instellingen::get('corvee', 'weergave_ledennamen_beheer'), Instellingen::get('corvee', 'weergave_link_ledennamen'))}</td>
	<td>{$vrijstelling->getBeginDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getEindDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getPercentage()}%</td>
	<td>{$vrijstelling->getPunten()}</td>
	<td class="col-del">
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$vrijstelling->getUid()}" title="Vrijstelling definitief verwijderen" class="btn rounded post confirm">{icon get="cross"}</a>
	</td>
</tr>