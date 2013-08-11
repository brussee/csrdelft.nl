<?php

require_once 'configuratie.include.php';
require_once 'lid/profiel.class.php';

if(!($loginlid->hasPermission('P_LOGGED_IN') AND $loginlid->hasPermission('P_OUDLEDEN_READ'))){
	# geen rechten
	echo 'false';
	exit;
}

$lid = LidCache::getLid($_GET['id']);

print_r($lid);

echo '{
    "user": ' . json_encode(array("id" => $lid->getUid(), "name" => $lid->getNaam(), "mobile" => $lid->getProperty('mobiel'), "phone" => $lid->getProperty('telefoon'))) . '
}';

?>