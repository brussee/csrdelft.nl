<?php

require_once 'model/maalcie/CorveeVoorkeurenModel.class.php';
require_once 'view/maalcie/BeheerVoorkeurenView.class.php';

/**
 * BeheerVoorkeurenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		} else {
			$this->acl = array(
				'inschakelen'	 => 'P_CORVEE_MOD',
				'uitschakelen'	 => 'P_CORVEE_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function beheer() {
		$matrix_repetities = CorveeVoorkeurenModel::getVoorkeurenMatrix();
		$this->view = new BeheerVoorkeurenView($matrix_repetities[0], $matrix_repetities[1]);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function inschakelen($crid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$voorkeur = CorveeVoorkeurenModel::inschakelenVoorkeur((int) $crid, $uid);
		$voorkeur->setVanUid($voorkeur->getUid());
		$this->view = new BeheerVoorkeurView($voorkeur);
	}

	public function uitschakelen($crid, $uid) {
		if (!ProfielModel::existsUid($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		CorveeVoorkeurenModel::uitschakelenVoorkeur((int) $crid, $uid);
		$voorkeur = new CorveeVoorkeur((int) $crid, null);
		$voorkeur->setVanUid($uid);
		$this->view = new BeheerVoorkeurView($voorkeur);
	}

}
