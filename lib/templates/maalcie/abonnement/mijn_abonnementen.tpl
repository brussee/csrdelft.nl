{*
	mijn_abonnementen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u abonnementen in- en uitschakelen voor periodieke maaltijden op Confide door op de knop te klikken in de kolom "Ingeschakeld".
Onderstaande tabel toont alle abonnementen die u aan of uit kunt zetten.
De kolom "Ingeschakeld" geeft aan of uw abonnement is ingeschakeld met "Aan", of is uitgeschakeld met "Uit".
</p>
<p>
N.B. Bij het inschakelen van een abonnement zult u automatisch worden aangemeld voor alle bijbehorende maaltijden waarvoor u zich nog niet had aangemeld, dus ook als u zich hiervoor al had afgemeld!
En bij het uitschakelen van een abonnement zullen handmatige aanmeldingen behouden blijven en zult u dus alleen worden afgemeld bij alle maaltijden waar dit abonnement u automatisch voor had aangemeld.
</p>
<table id="maalcie-tabel" class="maalcie-tabel">
	<thead>
		<tr>
			<th>Ingeschakeld</th>
			<th>Omschrijving</th>
			<th>Dag v/d week</th>
			<th>Periode</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$abonnementen item=abonnement}
	{include file='maalcie/abonnement/mijn_abonnement_lijst.tpl' abonnement=$abonnement}
{/foreach}
	</tbody>
</table>