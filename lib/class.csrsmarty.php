<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrSmarty.php
# -------------------------------------------------------------------

// load Smarty library
require_once('smarty/libs/Smarty.class.php');

class Smarty_csr extends Smarty {

	function Smarty_csr(){
   	$this->Smarty();
    
    $this->template_dir = SMARTY_TEMPLATE_DIR;
		$this->compile_dir = SMARTY_COMPILE_DIR;
 		//$this->config_dir = ;
		$this->cache_dir = SMARTY_CACHE_DIR;
		$this->caching = true;
  }

}
?>
