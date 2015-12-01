<?php
// returns a list of the last changed pages
class tx_drwiki_pi1_last_changed {
		
	function getDefaultParams(){
		return array(14);
	}
	
	function main($object, $params){
		$days = $params[0];
		
		$content .= $object->showList("tstamp>" . (time() - ($days*86400))  ,FALSE);
		$content .= "<br>";
			
	  	return $content;
	}
}
	
?>
