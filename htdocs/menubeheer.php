<?php
/**
 * menu.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Entry point van het menu-beheer.
 * 
 */
try {
	require_once 'configuratie.include.php';
	require_once 'menu/beheer/BeheerMenusController.class.php';
	
	$query = $_SERVER['REQUEST_URI'];
	echo $query .'<br />';
	$controller = new BeheerMenusController($query);
	$controller->getContent()->view();
}
catch (\Exception $e) {
	$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($protocol . ' 500 '. $e->getMessage(), true, 500);
	
	if (defined('DEBUG') && (\LoginLid::instance()->hasPermission('P_ADMIN') || \LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

?>