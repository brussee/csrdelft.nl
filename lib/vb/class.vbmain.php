<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrdelft.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezooid
# -------------------------------------------------------------------


require_once('class.csrdelft.php');

class vbmain extends csrdelft {

	
	function vbmain($body){
		parent::__construct($body,'vb/vb',99);
		$this->addStylesheet('vb.css');
		$this->addScript('../vb/jsonencode.js');
		require_once('class.menu.php');
	}

function view() {
		header('Content-Type: text/html; charset=UTF-8');
		$csrdelft=new Smarty_csr();
		$csrdelft->assign_by_ref('csrdelft', $this);
		
		$csrdelft->caching=false;
		$csrdelft->display('vb/vbcsrdelft.tpl');
		
		if(defined('DEBUG') AND Lid::get_lid()->hasPermission('P_ADMIN')){
			$db=MySql::get_MySql();
			echo '<pre>'.$db->getDebug().'</pre>';
		}
		//als er een error is geweest, die unsetten...
		if(isset($_SESSION['auth_error'])){ unset($_SESSION['auth_error']); }
	}

}

?>
