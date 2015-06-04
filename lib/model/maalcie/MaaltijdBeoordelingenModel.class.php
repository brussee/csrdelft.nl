<?php

/**
 * MaaltijdBeoordelingenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MaaltijdBeoordelingenModel extends PersistenceModel {

	const orm = 'MaaltijdBeoordeling';

	protected static $instance;

	protected function __construct() {
		parent::__construct('maalcie/');
	}

	public function nieuw(Maaltijd $maaltijd) {
		$b = new MaaltijdBeoordeling();
		$b->maaltijd_id = $maaltijd->getMaaltijdId();
		$b->uid = LoginModel::getUid();
		$b->kwantiteit = null;
		$b->kwaliteit = null;
		$this->create($b);
		return $b;
	}

	public function getNormalizedBeoordelingen(Maaltijd $maaltijd) {
		$beoordelingen = $this->find('maaltijd_id = ?', array($maaltijd->getMaaltijdId()));
		foreach ($beoordelingen as $b) {
			$normalize = Database::sqlSelect(array('AVG(kwantiteit)', 'AVG(kwaliteit)'), $this->orm->getTableName(), 'uid = ?', array($b->uid));
			foreach ($normalize as $avg) {
				$b->kwantiteit /= $avg[0];
				$b->kwaliteit /= $avg[1];
			}
		}
		return $beoordelingen;
	}

}
