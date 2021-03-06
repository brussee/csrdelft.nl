<?php

/**
 * InstantSearchForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class InstantSearchForm extends Formulier {

	public function __construct() {
		parent::__construct(null, '/ledenlijst?status=ALL');
		$this->post = false;
		$fields[] = new ZoekInputGroup('q');
		$this->addFields($fields);
	}

}

class ZoekInputGroup extends TextField {

	public $type = 'search';

	public function __construct($name) {
		parent::__construct($name, null, null);
		$this->css_classes[] = 'form-control';
		$this->css_classes[] = 'clicktogo';
		$this->placeholder = 'Zoek op titel';
		$this->onkeydown = <<<JS

if (event.keyCode === 13) { // enter
	$(this).trigger('typeahead:selected');
}
else if (event.keyCode === 191 || event.keyCode === 220) { // forward and backward slash
	event.preventDefault();
}
JS;
		$this->typeahead_selected = <<<JS

if (suggestion) {
	window.location.href = suggestion.url;
}
else {
	form_submit(event);
}
JS;
		if (LoginModel::mag('P_LEDEN_READ')) {

			if (LidInstellingen::get('zoeken', 'favorieten') === 'ja') {
				$this->addSuggestions(MenuModel::instance()->getMenu(LoginModel::getUid())->getChildren());
			}
			if (LidInstellingen::get('zoeken', 'menu') === 'ja') {
				$this->addSuggestions(MenuModel::instance()->flattenMenu(MenuModel::instance()->getMenu('main')));
			}

			$instelling = LidInstellingen::get('zoeken', 'leden');
			if ($instelling !== 'nee') {
				$this->suggestions['Leden'] = '/tools/naamsuggesties/leden/?status=' . $instelling . '&q=';
			}

			// TODO: bundelen om simultane verbindingen te sparen
			foreach (array('commissies', 'kringen', 'onderverenigingen', 'werkgroepen', 'woonoorden', 'groepen') as $option) {
				if (LidInstellingen::get('zoeken', $option) === 'ja') {
					$this->suggestions[ucfirst($option)] = '/groepen/' . $option . '/zoeken/?q=';
				}
			}

			if (LidInstellingen::get('zoeken', 'agenda') === 'ja') {
				$this->suggestions['Agenda'] = '/agenda/zoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'forum') === 'ja') {
				$this->suggestions['Forum'] = '/forum/titelzoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'fotoalbum') === 'ja') {
				$this->suggestions['Fotoalbum'] = '/fotoalbum/zoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'wiki') === 'ja') {
				$this->suggestions['Wiki'] = '/tools/wikisuggesties/?q=';
			}

			if (LidInstellingen::get('zoeken', 'documenten') === 'ja') {
				$this->suggestions['Documenten'] = '/documenten/zoeken/?q=';
			}

			if (LidInstellingen::get('zoeken', 'boeken') === 'ja') {
				$this->suggestions['Boeken'] = '/bibliotheek/zoeken/?q=';
			}

			// Favorieten en menu tellen niet
			$max = 6;
			if (isset($this->suggestions[''])) {
				$max++;
			}
			if (count($this->suggestions) > $max) {
				setMelding('Meer dan 6 zoekbronnen tegelijk wordt niet ondersteund', 0);
			}
		}
	}

	private function addSuggestions(array $list) {
		foreach ($list as $item) {
			if ($item->magBekijken()) {
				$parent = $item->getParent();
				if ($parent AND $parent->tekst != 'main') {
					if ($parent->tekst == LoginModel::getUid()) { // werkomheen
						$parent->tekst = 'Favorieten';
					}
					$label = $parent->tekst;
				} else {
					$label = 'Menu';
				}
				$this->suggestions[''][] = array(
					'url'	 => $item->link,
					'label'	 => $label,
					'value'	 => $item->tekst
				);
			}
		}
	}

	public function view() {
		$html = '';
		foreach (array('favorieten', 'menu', 'leden', 'commissies', 'kringen', 'onderverenigingen', 'werkgroepen', 'woonoorden', 'groepen', 'agenda', 'forum', 'fotoalbum', 'wiki', 'documenten', 'boeken') as $option) {
			$html .= '<li><a href="#">';
			$instelling = LidInstellingen::get('zoeken', $option);
			if ($instelling !== 'nee') {
				$html .= '<span class="fa fa-check"></span> ';
				if ($option === 'leden') {
					$html .= ucfirst(strtolower($instelling)) . '</a></li>';
					continue;
				}
			} else {
				$html .= '<span style="margin-right: 18px;"></span> ';
			}
			$html .= ucfirst($option) . '</a></li>';
		}
		?>
		<div class="input-group">
			<div class="input-group-btn">
				<?= parent::getHtml() ?>
				<button id="cd-zoek-engines" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
					<span class="fa fa-search"></span>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right" role="menu">
					<li><a onclick="window.location.href = '/ledenlijst?status=OUDLEDEN&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Oudleden</a></li>
					<li><a onclick="window.location.href = '/ledenlijst?status=ALL&q=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Iedereen</a></li>
					<li><a onclick="window.location.href = '/forum/zoeken/' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Forum reacties</a></li>
					<li><a onclick="window.location.href = '/wiki/hoofdpagina?do=search&id=' + encodeURIComponent($('#<?= $this->getId() ?>').val());">Wiki inhoud</a></li>
					<li class="divider"></li>
					<li class="dropdown-submenu">
						<a href="#">Snelzoeken</a>
						<ul class="dropdown-menu">
							<li><a href="/instellingen#lidinstellingenform-tab-Zoeken">Aanpassen...</a></li>
							<li class="divider"></li>
								<?= $html; ?>
						</ul>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

}
