<?php
/**
 *  Plugin to show ratings bar in wiki pages
 *  Author: Fernando Arconada
 *  email: fernando.arconada at gmail.com
 */
class tx_drwiki_pi1_ratings {
		
	function getDefaultParams(){
		return array();
	}
	
	function main($object, $params){
		if($object->ffConf['enableRatingsPlugin']){
			$conf=$object->ratingsApiObj->getDefaultConfig();
			$conf['storagePid'] = $object->ffConf['ratingsStoragePid'];
			$conf['templateFile'] = $object->ffConf['ratingsTemplateFile'];
			return $object->ratingsApiObj->getRatingDisplay('tx_drwiki_pages_' . $object->internal["currentRow"]['keyword'], $conf);
		}
			
	  	return '';
	}
}
?>