<?php
// returns a list of the last changed pages
class tx_drwiki_pi1_templatelist {
		
	function getDefaultParams(){
		return array();
	}
	
	function main($object, $params){
        
		$results = $object->getWikiInfos("keyword" ,TRUE, FALSE, TRUE, " AND tx_drwiki_pages.keyword LIKE 'template:%'");
        
        if ($results){
            $content = '<ul>';
            foreach ($results as $result) {
                $content .= '<li> [['.$result["keyword"].']] </li>';
            }
            $content .= "</ul>";
        }
	
	  	return $content;
	}
}
	
?>
