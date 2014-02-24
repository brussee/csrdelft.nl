{**
 * ubbhulp.tpl
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 *}
<div id="ubbhulpverhaal" class="outer-shadow dragobject" style="top: {$ubbtop}px; left: {$ubbleft}px;">
	<span id="ubbsluiten" onclick="$('#ubbhulpverhaal').fadeOut();" title="Opmaakhulp verbergen">&times;</span>
	<h2>Tekst opmaken</h2>
	<p>U kunt uw berichten opmaken met een simpel opmaaktaaltje wat ubb genoemd wordt. Het lijkt wat op html, maar dan met vierkante haken:</p>
	<ul>
		<li>[b]...[/b] voor <strong>dikgedrukte tekst</strong></li>
		<li>[i]...[/i] voor <em>cursieve tekst</em></li>
		<li>[u]...[/u] voor <span style="text-decoration: underline;">onderstreepte tekst</span></li>
		<li>[s]...[/s] voor <span style="text-decoration: line-through;">doorgestreepte tekst</span></li>
		<li>[email=e@mail.nl]Stuur email[/email] voor een email-link</li>
		<li>[url=http://csrdelft.nl]Webstek van C.S.R.[/url] voor een externe link</li>
		<li>[citaat][/citaat] voor een citaat. [citaat=<em>lidnummer</em>][/citaat] voor een citaat van een lid.</li>
		<li>[lid]<em>lidnummer</em>[/lid] voor een link naar het profiel van een lid of oudlid</li>
		<li>[offtopic]...[/offtopic] voor een stukje tekst van-het-onderwerp.</li>
		<li>[ubboff]...[/ubboff] voor een stukje met ubb-tags zonder dat ze ge&iuml;nterpreteerd worden</li>
	</ul>
	<h2>Elementen invoegen</h2>
	<ul>
		<li>[img]http://csrdelft.nl/plaetje.jpg[/img] voor een plaetje</li>
		<li>[video]<em>url</em>[/video], de url van een youtube, vimeo, dailymotion, 123video of godtube voor een filmpje direct in je post</li>
		<li>[document]<em>documentid</em>[/document] nummer van document</li>
		<li>[peiling=<em>peilingid</em>] nummer van peiling</li>
		<li>[groep]<em>groepid</em>[/groep] nummer van de ketzer / groep / commissie</li>
	</ul>
	In de beperking toont zich de meester!<br />
	<a href="http://csrdelft.nl/wiki/cie:diensten:forum" target="_blank">meer info...</a>
</div>