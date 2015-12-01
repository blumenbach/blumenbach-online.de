<?php
// Returns a list of the pages that link to this wiki entry (default)
// or the entry defined in the parameter.

class tx_drwiki_pi1_backlink {
		
	function getDefaultParams(){
		return array('');
	}
	
	function main($object, $params){
	    $entrykey = $params[0];
	    if ($entrykey=='') $entrykey = $object->piVars["keyword"];
	      
	    if (stripos($entrykey,'Template:')===0) {
	    	$results = $object->getWikiInfos("keyword" ,TRUE, FALSE, TRUE, " AND tx_drwiki_pages.body LIKE '%{{".$GLOBALS['TYPO3_DB']->quoteStr(substr($entrykey,9),'tx_drwiki_pages')."}}%' ORDER BY keyword ASC");
	    } else {
			$results = $object->getWikiInfos("keyword" ,TRUE, FALSE, TRUE, " AND tx_drwiki_pages.body LIKE '%[[".$GLOBALS['TYPO3_DB']->quoteStr($entrykey,'tx_drwiki_pages')."%' ORDER BY keyword ASC");
	    }
	    
        if ($results) {
            $content = "<ul>\n";
            foreach ($results as $result) {
                $content .= "<li> [[".$result["keyword"]."]]</li>\n";
            }
            $content .= "</ul>\n";
        }
	
	  	return $content;
	}
}
	
?>