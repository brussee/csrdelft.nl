<?php

require_once 'model/maalcie/CorveeVrijstellingenModel.class.php';
require_once 'view/maalcie/BeheerVrijstellingenView.class.php';
require_once 'view/maalcie/forms/VrijstellingForm.class.php';

/**
 * BeheerVrijstellingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerVrijstellingenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'nieuw'		 => 'P_CORVEE_MOD',
				'bewerk'	 => 'P_CORVEE_MOD',
				'opslaan'	 => 'P_CORVEE_MOD',
				'verwijder'	 => 'P_CORVEE_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$uid = null;
		if ($this->hasParam(3)) {
			$uid = $this->getParam(3);
		}
		parent::performAction(array($uid));
	}

	public function beheer() {
		$vrijstellingen = CorveeVrijstellingenModel::getAlleVrijstellingen();
		$this->view = new BeheerVrijstellingenView($vrijstellingen);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function nieuw() {
		$vrijstelling = new CorveeVrijstelling();
		$this->view = new VrijstellingForm($vrijstelling->getUid(), $vrijstelling->getBeginDatum(), $vrijstelling->getEindDatum(), $vrijstelling->getPercentage()); // fetches POST values itself
	}

	public function bewerk($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$vrijstelling = CorveeVrijstellingenModel::getVrijstelling($uid);
		$this->view = new VrijstellingForm($vrijstelling->getUid(), $vrijstelling->getBeginDatum(), $vrijstelling->getEindDatum(), $vrijstelling->getPercentage()); // fetches POST values itself
	}

	public function opslaan($uid = null) {
		if ($uid !== null) {
			$this->bewerk($uid);
		} else {
			$this->nieuw();
		}
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$vrijstelling = CorveeVrijstellingenModel::saveVrijstelling($values['uid'], $values['begin_datum'], $values['eind_datum'], $values['percentage']);
			$this->view = new BeheerVrijstellingView($vrijstelling);
		}
	}

	public function verwijder($uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		CorveeVrijstellingenModel::verwijderVrijstelling($uid);
		echo '<tr id="vrijstelling-row-' . $uid . '" class="remove"></tr>';
		exit;
	}

}
