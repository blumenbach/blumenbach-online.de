<?php
class tx_drwiki_pi1_search {
		
	function getDefaultParams(){
		return array();
	}
	
	function main($object, $params){
		if($object->piVars["pluginSEARCH"]["sword"]){
            
            //unset variables
            $searchWord = $object->piVars["pluginSEARCH"]["sword"];
            
            $searchString = "body like '%" . $searchWord . "%' OR keyword like '%" . $searchWord . "%'";

            $addSearchParam = $params[0];
            if ($addSearchParam) $searchString.= " AND (body like '%" . $GLOBALS['TYPO3_DB']->fullQuoteStr($addSearchParam,'tx_drwiki_pages') . "%' OR keyword like '%" . $GLOBALS['TYPO3_DB']->fullQuoteStr($addSearchParam,'tx_drwiki_pages') . "%')";
            
            $object->piVars["pluginSEARCH"]["sword"] = "";
            $object->piVars["pluginSEARCH"]["submit"] = "";          
			$content .= $object->showList($searchString,FALSE);
			

            $content .= "<p class='wiki-box-red'>Is the article \"[[$searchWord]]\" you are looking for not in that list? ".$object->pi_linkTP_keepPIvars("Click here to add ".$searchWord, array("keyword" => $searchWord, "showUid" => ""), 1, 0)."</p>";

            
		}
		$content .= '<form name="'.$object->prefixId. '_SearchForm" method="post" action="'.$object->pi_linkTP_keepPIvars_url(array(),1,0).'">';
		$content .= '<input type="text" name="'.$object->prefixId.'[pluginSEARCH][sword]" size="30" value="'.$object->piVars["pluginSEARCH"]["sword"].'" />';
	  	$content .= '&nbsp;<input type="Submit" name="'.$object->prefixId.'[pluginSEARCH][submit]" value="Search" /></form>';
	  	
	  	return $content;
	}
}
	
?>
