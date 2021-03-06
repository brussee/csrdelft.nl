<?php

/**
 * ToewijzenForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier om een corveetaak toe te wijzen aan een lid.
 * 
 */
class ToewijzenForm extends ModalForm {

	public function __construct(CorveeTaak $taak, array $suggesties) {
		parent::__construct(null, maalcieUrl . '/toewijzen/' . $taak->getTaakId());

		if (!is_int($taak->getTaakId()) || $taak->getTaakId() <= 0) {
			throw new Exception('invalid tid');
		}
		$this->titel = 'Taak toewijzen aan lid';
		$this->css_classes[] = 'PreventUnchanged';

		$fields[] = new LidField('uid', $taak->getUid(), 'Naam of lidnummer', 'leden');
		$fields[] = new SuggestieLIjst($suggesties, $taak);
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

class SuggestieLijst extends SmartyTemplateView implements FormElement {

	private $taak;
	private $voorkeurbaar;
	private $voorkeur;
	private $recent;

	public function __construct(array $suggesties, CorveeTaak $taak) {
		parent::__construct($suggesties);
		$this->taak = $taak;

		$crid = $taak->getCorveeRepetitieId();
		if ($crid !== null) {
			$this->voorkeurbaar = CorveeRepetitiesModel::getRepetitie($crid)->getIsVoorkeurbaar();
		}

		if ($taak->getCorveeFunctie()->kwalificatie_benodigd) {
			$this->voorkeur = Instellingen::get('corvee', 'suggesties_voorkeur_kwali_filter');
			$this->recent = Instellingen::get('corvee', 'suggesties_recent_kwali_filter');
		} else {
			$this->voorkeur = Instellingen::get('corvee', 'suggesties_voorkeur_filter');
			$this->recent = Instellingen::get('corvee', 'suggesties_recent_filter');
		}
	}

	public function getHtml() {
		$this->smarty->assign('suggesties', $this->model);
		$this->smarty->assign('jongsteLichting', LichtingenModel::getJongsteLidjaar());
		$this->smarty->assign('voorkeur', $this->voorkeur);
		$this->smarty->assign('recent', $this->recent);
		if (isset($this->voorkeurbaar)) {
			$this->smarty->assign('voorkeurbaar', $this->voorkeurbaar);
		}
		$this->smarty->assign('kwalificatie_benodigd', $this->taak->getCorveeFunctie()->kwalificatie_benodigd);

		return $this->smarty->fetch('maalcie/corveetaak/suggesties_lijst.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getTitel() {
		return $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

	public function getJavascript() {
		$js = <<<JS

/* {$this->getTitel()} */
taken_color_suggesties();

JS;
		if (isset($this->voorkeurbaar) and $this->voorkeur) {
			$js .= "taken_toggle_suggestie('geenvoorkeur');";
		}
		if ($this->recent) {
			$js .= "taken_toggle_suggestie('recent');";
		}
		return $js;
	}

}
