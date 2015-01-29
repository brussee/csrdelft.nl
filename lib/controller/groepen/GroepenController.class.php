<?php

require_once 'view/GroepenView.class.php';

/**
 * GroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor groepen.
 */
class GroepenController extends Controller {

	public function __construct($query, GroepenModel $model = null) {
		parent::__construct($query, $model);
		if ($model === null) {
			$this->model = GroepenModel::instance();
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht'; // default

		if ($this->hasParam(3)) { // id or action
			$this->action = $this->getParam(3);
		}
		switch ($this->action) {

			// Geen groep id vereist
			case 'overzicht':
			case A::Beheren:
			case A::Aanmaken:

				// Soort in param 3?
				if ($this->hasParam(4)) {
					$args['soort'] = $this->getParam(4);
				}

			case A::Wijzigen:
			case A::Verwijderen:
			case 'opvolging':
			case 'converteren':
				break;

			// Groep id vereist
			default:

				// Groep id in param 3?
				$id = (int) $this->action;
				$groep = $this->model->get($id);
				if (!$groep) {
					$this->geentoegang();
				}
				$args['groep'] = $groep;
				$this->action = A::Bekijken; // default
				$uid = null;

				// Actie in param 4?
				if ($this->hasParam(4)) {
					$this->action = $this->getParam(4);

					// Lidnummer in param 5?
					if ($this->hasParam(5)) {
						$uid = $this->getParam(5);
						$args['uid'] = $uid;
					}
				}

				// Controleer rechten
				if (!$groep->mag($this->action, $uid)) {
					$this->geentoegang();
				}
		}
		return parent::performAction($args);
	}

	/**
	 * Check permissions & valid params in performAction.
	 * 
	 * @return boolean
	 */
	protected function mag($action, array $args) {
		switch ($action) {

			case 'leden':
			case A::Beheren:
			case A::Wijzigen:
				return true;

			case 'overzicht':
			case A::Bekijken:
				return !$this->isPosted();

			case 'overzicht':
			case 'opvolging':
			case 'converteren':
			case 'omschrijving':
			case GroepTab::Pasfotos:
			case GroepTab::Lijst:
			case GroepTab::Statistiek:
			case GroepTab::Emails:
			case A::Aanmaken:
			case A::Verwijderen:
			case A::Aanmelden:
			case A::Afmelden:
			case A::Bewerken:
				return $this->isPosted();

			default:
				return false;
		}
	}

	public function overzicht($soort = null) {
		if ($soort) {
			$groepen = $this->model->find('status = ? AND soort = ?', array(GroepStatus::HT, $soort));
		} else {
			$groepen = $this->model->find('status = ?', array(GroepStatus::HT));
		}
		$body = new GroepenView($this->model, $groepen);
		$this->view = new CsrLayoutPage($body);
	}

	public function bekijken(Groep $groep) {
		$groepen = $this->model->find('familie = ?', array($groep->familie));
		$body = new GroepenView($this->model, $groepen);
		$this->view = new CsrLayoutPage($body);
	}

	public function omschrijving(Groep $groep) {
		$this->view = new GroepOmschrijvingView($groep);
	}

	public function pasfotos(Groep $groep) {
		$this->view = new GroepPasfotosView($groep);
	}

	public function lijst(Groep $groep) {
		$this->view = new GroepLijstView($groep);
	}

	public function stats(Groep $groep) {
		$this->view = new GroepStatistiekView($groep);
	}

	public function emails(Groep $groep) {
		$this->view = new GroepEmailsView($groep);
	}

	public function beheren($soort = null) {
		if ($this->isPosted()) {
			if ($soort) {
				$groepen = $this->model->find('soort = ?', array($soort));
			} else {
				$groepen = $this->model->find();
			}
			$this->view = new GroepenBeheerData($groepen);
		} else {
			$table = new GroepenBeheerTable($this->model);
			$this->view = new CsrLayoutPage($table);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function aanmaken($soort = null) {
		$groep = $this->model->nieuw($soort);
		$form = new GroepForm($groep, $this->model->getUrl() . $this->action);
		if ($form->validate()) {
			$this->model->create($groep);
			$this->view = new GroepenBeheerData(array($groep));
		} else {
			$this->view = $form;
		}
	}

	public function wijzigen(Groep $groep = null) {
		if ($groep) {
			$form = new GroepForm($groep, $groep->getUrl() . $this->action);
			if (!$this->isPosted()) {
				$this->beheren();
				$this->view->getBody()->filter = $groep->naam;
				$form->tableId = $this->view->getBody()->getTableId();
				$this->view->modal = $form;
			} elseif ($form->validate()) {
				$this->model->update($groep);
				$this->view = new GroepenBeheerData(array($groep));
			} else {
				$this->view = $form;
			}
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (isset($selection[0])) {
				$groep = $this->model->getUUID($selection[0]);
			} else {
				$groep = false;
			}
			if (!$groep OR ! $groep->mag($this->action)) {
				$this->geentoegang();
			}
			$form = new GroepForm($groep, $this->model->getUrl() . $this->action);
			if ($form->validate()) {
				$this->model->update($groep);
				$this->view = new GroepenBeheerData(array($groep));
			} else {
				$this->view = $form;
			}
		}
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$response = array();
		foreach ($selection as $UUID) {
			$groep = $this->model->getUUID($UUID);
			if (!$groep OR ! $groep->mag($this->action)) {
				continue;
			}
			$this->model->delete($groep);
			$response[] = $groep;
		}
		$this->view = new RemoveRowsResponse($response);
	}

	public function opvolging() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (empty($selection)) {
			$this->geentoegang();
		}
		$groep = $this->model->getUUID($selection[0]);
		$form = new GroepOpvolgingForm($groep, $this->model->getUrl() . $this->action);
		if ($form->validate()) {
			$values = $form->getValues();
			$response = array();
			foreach ($selection as $UUID) {
				$groep = $this->model->getUUID($UUID);
				if (!$groep OR ! $groep->mag(A::Wijzigen)) {
					continue;
				}
				$groep->familie = $values['familie'];
				$groep->status = $values['status'];
				$this->model->update($groep);
				$response[] = $groep;
			}
			$this->view = new GroepenBeheerData($response);
		} else {
			$this->view = $form;
		}
	}

	public function converteren() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (empty($selection)) {
			$this->geentoegang();
		}
		$groep = $this->model->getUUID($selection[0]);
		$form = new GroepConverteerForm($groep, $this->model);
		if ($form->validate()) {
			$model = $form->findByName('class')->getValue();
			if ($model === get_class($this->model)) {
				setMelding('Geen wijziging', 0);
				$this->view = $form;
				return;
			}
			$response = array();
			foreach ($selection as $UUID) {
				$groep = $this->model->getUUID($UUID);
				if (!$groep OR ! $groep->mag(A::Wijzigen)) {
					continue;
				}
				$nieuw = $model::instance()->converteer($groep, $this->model);
				if ($nieuw) {
					$response[] = $groep;
				}
			}
			$this->view = new RemoveRowsResponse($response);
		} else {
			$this->view = $form;
		}
	}

	public function leden(Groep $groep) {
		if ($this->isPosted()) {
			$this->view = new GroepLedenData($groep->getLeden());
		} else {
			$leden = $groep::leden;
			$this->view = new GroepLedenTable($leden::instance(), $groep);
		}
	}

	public function aanmelden(Groep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			$lid = $model->nieuw($groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $groep, $groep->getOpmerkingSuggesties(), $groep->keuzelijst);
			if ($form->validate()) {
				$model->create($lid);
				$this->view = new GroepPasfotosView($groep);
			} else {
				$this->view = $form;
			}
		}
		// beheren
		else {
			$lid = $model->nieuw($groep, $uid);
			$uids = array_keys(group_by_distinct('uid', $groep->getLeden()));
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . $this->action, $uids);
			if ($form->validate()) {
				$model->create($lid);
				$this->view = new GroepLedenData(array($lid));
			} else {
				$this->view = $form;
			}
		}
	}

	public function bewerken(Groep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			$lid = $model->get($groep, $uid);
			$form = new GroepBewerkenForm($lid, $groep, $groep->getOpmerkingSuggesties(), $groep->keuzelijst);
			if ($form->validate()) {
				$model->update($lid);
			}
			$this->view = $form;
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (!isset($selection[0])) {
				$this->geentoegang();
			}
			$lid = $model->getUUID($selection[0]);
			$form = new GroepLidBeheerForm($lid, $groep->getUrl() . $this->action);
			if ($form->validate()) {
				$model->update($lid);
				$this->view = new GroepLedenData(array($lid));
			} else {
				$this->view = $form;
			}
		}
	}

	public function afmelden(Groep $groep, $uid = null) {
		$leden = $groep::leden;
		$model = $leden::instance();
		if ($uid) {
			$lid = $model->get($groep, $uid);
			$model->delete($lid);
			$this->view = new GroepView($groep);
		}
		// beheren
		else {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$response = array();
			foreach ($selection as $UUID) {
				$lid = $model->getUUID($UUID);
				$model->delete($lid);
				$response[] = $lid;
			}
			$this->view = new RemoveRowsResponse($response);
		}
	}

}
