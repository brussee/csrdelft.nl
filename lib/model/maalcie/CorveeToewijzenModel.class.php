<?php

require_once 'model/maalcie/KwalificatiesModel.class.php';
require_once 'model/maalcie/CorveeVrijstellingenModel.class.php';
require_once 'model/maalcie/CorveePuntenModel.class.php';
require_once 'model/maalcie/CorveeVoorkeurenModel.class.php';

/**
 * CorveeToewijzenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeToewijzenModel {

	/**
	 * Bepaald de suggesties voor het toewijzen van een corveetaak.
	 * Als er een kwalificatie benodigd is worden alleen de
	 * gekwalificeerde leden teruggegeven.
	 *
	 * @param CorveeTaak $taak
	 * @return array
	 * @throws Exception
	 */
	public static function getSuggesties(CorveeTaak $taak) {
		$vrijstellingen = CorveeVrijstellingenModel::getAlleVrijstellingen(true); // grouped by uid
		$functie = $taak->getCorveeFunctie();
		if ($functie->kwalificatie_benodigd) { // laad alleen gekwalificeerde leden
			$lijst = array();
			$avg = 0;
			foreach ($functie->getKwalificaties() as $kwali) {
				$uid = $kwali->uid;
				$profiel = ProfielModel::get($uid); // false if lid does not exist
				if (!$profiel) {
					throw new Exception('Lid bestaat niet: $uid =' . $uid);
				}
				if (!$profiel->isLid()) {
					continue; // geen oud-lid of overleden lid
				}
				if (array_key_exists($uid, $vrijstellingen)) {
					$vrijstelling = $vrijstellingen[$uid];
					$datum = $taak->getBeginMoment();
					if ($datum >= strtotime($vrijstelling->getBeginDatum()) && $datum <= strtotime($vrijstelling->getEindDatum())) {
						continue; // taak valt binnen vrijstelling-periode: suggestie niet weergeven
					}
				}
				$lijst[$uid] = CorveePuntenModel::loadPuntenVoorLid($profiel, array($functie->functie_id => $functie));
				$lijst[$uid]['aantal'] = $lijst[$uid]['aantal'][$functie->functie_id];
				$avg += $lijst[$uid]['aantal'];
			}
			$avg /= sizeof($lijst);
			foreach ($lijst as $uid => $punten) {
				$lijst[$uid]['relatief'] = $lijst[$uid]['aantal'] - (int) $avg;
			}
			$sorteer = 'sorteerKwali';
		} else {
			$lijst = CorveePuntenModel::loadPuntenVoorAlleLeden();
			foreach ($lijst as $uid => $punten) {
				if (array_key_exists($uid, $vrijstellingen)) {
					$vrijstelling = $vrijstellingen[$uid];
					$datum = $taak->getBeginMoment();
					if ($datum >= strtotime($vrijstelling->getBeginDatum()) && $datum <= strtotime($vrijstelling->getEindDatum())) {
						unset($lijst[$uid]); // taak valt binnen vrijstelling-periode: suggestie niet weergeven
					}
					// corrigeer prognose in suggestielijst vóór de aanvang van de vrijstellingsperiode
					if ($vrijstelling !== null && $datum < strtotime($vrijstelling->getBeginDatum())) {
						$lijst[$uid]['prognose'] -= $vrijstelling->getPunten();
					}
				}
			}
			$sorteer = 'sorteerPrognose';
		}
		foreach ($lijst as $uid => $punten) {
			$lijst[$uid]['laatste'] = CorveeTakenModel::getLaatsteTaakVanLid($uid);
			if ($lijst[$uid]['laatste'] !== null && $lijst[$uid]['laatste']->getBeginMoment() >= strtotime(Instellingen::get('corvee', 'suggesties_recent_verbergen'), $taak->getBeginMoment())) {
				$lijst[$uid]['recent'] = true;
			} else {
				$lijst[$uid]['recent'] = false;
			}
			if ($taak->getCorveeRepetitieId() !== null) {
				$lijst[$uid]['voorkeur'] = CorveeVoorkeurenModel::getHeeftVoorkeur($taak->getCorveeRepetitieId(), $uid);
			} else {
				$lijst[$uid]['voorkeur'] = false;
			}
		}
		uasort($lijst, array('self', $sorteer));
		return $lijst;
	}

	static function sorteerKwali($a, $b) {
		if ($a['laatste'] !== null && $b['laatste'] !== null) {
			$a = $a['laatste']->getBeginMoment();
			$b = $b['laatste']->getBeginMoment();
		} elseif ($a['laatste'] === null) {
			return -1;
		} elseif ($b['laatste'] === null) {
			return 1;
		} else {
			$a = $a['aantal'];
			$b = $b['aantal'];
		}
		if ($a === $b) {
			return 0;
		} elseif ($a < $b) { // < ASC
			return -1;
		} else {
			return 1;
		}
	}

	static function sorteerPrognose($a, $b) {
		$a = $a['prognose'];
		$b = $b['prognose'];
		if ($a === $b) {
			return 0;
		} elseif ($a < $b) { // < ASC
			return -1;
		} else {
			return 1;
		}
	}

}

?>