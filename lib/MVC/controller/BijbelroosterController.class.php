<?php

require_once 'MVC/model/BijbelroosterModel.class.php';
require_once 'MVC/view/BijbelroosterView.class.php';

/**
 * BijbelroosterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van het bijbelrooster.
 */
class BijbelroosterController extends AclController {

	public function __construct($query) {
		parent::__construct($query, BijbelroosterModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'bekijken' => 'P_PUBLIC'
			);
		} else {
			$this->acl = array(
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'bekijken';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function bekijken() {
		$body = new BijbelroosterView($this->model);
		$this->view = new CsrLayoutPage($body);
	}

}
