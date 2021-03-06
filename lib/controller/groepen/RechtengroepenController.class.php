<?php

/**
 * RechtengroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor rechten-groepen. Kleine letter g vanwege groepen-router.
 */
class RechtengroepenController extends AbstractGroepenController {

	public function __construct($query) {
		parent::__construct($query, RechtenGroepenModel::instance());
	}

}
