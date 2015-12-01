<?php
// returns anb index of the latest pages
class tx_drwiki_pi1_author {
		
	function getDefaultParams(){
		return array(14);
	}
	
	function main($object, $params){
		
		$content = "[[". $object->showList(""  ,2,array("author")) ."]]";
			
	  	return $content;
	}
}
	
?>