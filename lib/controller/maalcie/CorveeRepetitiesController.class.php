<?php

require_once 'model/maalcie/CorveeRepetitiesModel.class.php';
require_once 'model/maalcie/MaaltijdRepetitiesModel.class.php';
require_once 'view/maalcie/CorveeRepetitiesView.class.php';
require_once 'view/maalcie/forms/CorveeRepetitieForm.class.php';

/**
 * CorveeRepetitiesController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class CorveeRepetitiesController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer'	 => 'P_CORVEE_MOD',
				'maaltijd'	 => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'nieuw'		 => 'P_CORVEE_MOD',
				'bewerk'	 => 'P_CORVEE_MOD',
				'opslaan'	 => 'P_CORVEE_MOD',
				'verwijder'	 => 'P_CORVEE_MOD',
				'bijwerken'	 => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$crid = null;
		if ($this->hasParam(3)) {
			$crid = (int) $this->getParam(3);
		}
		parent::performAction(array($crid));
	}

	public function beheer($crid = null, $mrid = null) {
		$modal = null;
		$maaltijdrepetitie = null;
		if (is_int($crid) && $crid > 0) {
			$this->bewerk($crid);
			$modal = $this->view;
			$repetities = CorveeRepetitiesModel::getAlleRepetities();
		} elseif (is_int($mrid) && $mrid > 0) {
			$repetities = CorveeRepetitiesModel::getRepetitiesVoorMaaltijdRepetitie($mrid);
			$maaltijdrepetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		} else {
			$repetities = CorveeRepetitiesModel::getAlleRepetities();
		}
		$this->view = new CorveeRepetitiesView($repetities, $maaltijdrepetitie);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
		$this->view->modal = $modal;
	}

	public function maaltijd($mrid) {
		$this->beheer(null, $mrid);
	}

	public function nieuw($mrid = null) {
		$repetitie = new CorveeRepetitie(0, $mrid);
		$this->view = new CorveeRepetitieForm($repetitie->getCorveeRepetitieId(), $repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getFunctieId(), null, $repetitie->getStandaardAantal(), $repetitie->getIsVoorkeurbaar()); // fetches POST values itself
	}

	public function bewerk($crid) {
		$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
		$this->view = new CorveeRepetitieForm($repetitie->getCorveeRepetitieId(), $repetitie->getMaaltijdRepetitieId(), $repetitie->getDagVanDeWeek(), $repetitie->getPeriodeInDagen(), $repetitie->getFunctieId(), $repetitie->getStandaardPunten(), $repetitie->getStandaardAantal(), $repetitie->getIsVoorkeurbaar()); // fetches POST values itself
	}

	public function opslaan($crid) {
		if ($crid > 0) {
			$this->bewerk($crid);
		} else {
			$this->nieuw();
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$mrid = empty($values['mlt_repetitie_id']) ? null : (int) $values['mlt_repetitie_id'];
			$voorkeurbaar = empty($values['voorkeurbaar']) ? false : (bool) $values['voorkeurbaar'];
			$repetitie_aantal = CorveeRepetitiesModel::saveRepetitie($crid, $mrid, $values['dag_vd_week'], $values['periode_in_dagen'], intval($values['functie_id']), $values['standaard_punten'], $values['standaard_aantal'], $voorkeurbaar);
			$maaltijdrepetitie = null;
			if (endsWith($_SERVER['HTTP_REFERER'], maalcieUrl . '/maaltijd/' . $mrid)) { // state of gui
				$maaltijdrepetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
			}
			$this->view = new CorveeRepetitieView($repetitie_aantal[0], $maaltijdrepetitie);
			if ($repetitie_aantal[1] > 0) {
				setMelding($repetitie_aantal[1] . ' voorkeur' . ($repetitie_aantal[1] !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
			}
		}
	}

	public function verwijder($crid) {
		$aantal = CorveeRepetitiesModel::verwijderRepetitie($crid);
		if ($aantal > 0) {
			setMelding($aantal . ' voorkeur' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.', 2);
		}
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		echo '<tr id="repetitie-row-' . $crid . '" class="remove"></tr>';
		exit;
	}

	public function bijwerken($crid) {
		$this->opslaan($crid);
		if ($this->view instanceof CorveeRepetitieView) { // opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$aantal = CorveeTakenModel::updateRepetitieTaken($this->view->getModel(), $verplaats);
			if ($aantal['update'] < $aantal['day']) {
				$aantal['update'] = $aantal['day'];
			}
			setMelding(
					$aantal['update'] . ' corveeta' . ($aantal['update'] !== 1 ? 'ken' : 'ak') . ' bijgewerkt waarvan ' .
					$aantal['day'] . ' van dag verschoven.', 1);
			$aantal['datum'] += $aantal['maaltijd'];
			setMelding(
					$aantal['datum'] . ' corveeta' . ($aantal['datum'] !== 1 ? 'ken' : 'ak') . ' aangemaakt waarvan ' .
					$aantal['maaltijd'] . ' maaltijdcorvee.', 1);
		}
	}

}
