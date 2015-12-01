<?php
// returns anb index of the latest pages
class tx_drwiki_pi1_indexlist {
		
	function getDefaultParams(){
		return array(14);
	}
	
	function main($object, $params){
		
		$content .= $object->showList(""  ,FALSE);
		$content .= "<br>";
			
	  	return $content;
	}
}
	
?>