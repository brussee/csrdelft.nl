<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# Pagina's weergeven uit het fotoalbum
# -------------------------------------------------------------------

require_once 'include.config.php';

require_once 'class.fotoalbum.php';
require_once 'class.fotoalbumcontent.php';

$fotoalbum = new Fotoalbum(urldecode(substr($_SERVER['REQUEST_URI'], 19)), 'Fotoalbum');
$fotoalbumcontent = new FotoalbumContent($fotoalbum);
$fotoalbumcontent->setActie('album');


$pagina=new csrdelft($fotoalbumcontent);
$pagina->addStylesheet('fotoalbum.css');
$pagina->addStylesheet('lightbox.css');
$pagina->addScript('prototype.js');
$pagina->addScript('scriptaculous.js?load=effects,builder');
$pagina->addScript('fastlightbox.js');
$pagina->view();

?>