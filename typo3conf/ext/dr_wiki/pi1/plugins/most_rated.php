<?php
/**
 *  Plugin to show most rated pages
 *  Author: Fernando Arconada
 *  email: fernando.arconada at gmail.com
 */

class tx_drwiki_pi1_mostrated {
		
	function getDefaultParams(){
		return array(10);
	}
	
	function main($object, $params){
		$maxNumRows = t3lib_div::intval_positive($params[0]);
		
		if(!$object->ffConf['enableMostRatedPlugin']){
			//is it the plugin disabled? Yes, then go out
	  		return '';
		}
		
		// Rating Config 
		$conf=$object->ratingsApiObj->getDefaultConfig();
		$conf['storagePid'] = $object->ffConf['ratingsStoragePid'];
		$conf['templateFile'] = $object->ffConf['ratingsTemplateFile'];
		$conf['mode'] = 'static';

		// Get the records from DB
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('rating/vote_count as rate,rating,vote_count,reference',
				'tx_ratings_data',
				'pid=' . intval($conf['storagePid']) .
				' AND reference like \'tx_drwiki_pages_%\'' . $object->cObj->enableFields('tx_ratings_data'),
				null,
				'rate desc, vote_count',
				$maxNumRows
		);
		//render content in template
		$templateCode = $object->cObj->fileResource('EXT:dr_wiki/res/most_rated_plugin.html');
		# Get the parts out of the template
		$template['total'] = $object->cObj->getSubpart($templateCode,'###TEMPLATE###');
		$template['item'] = $object->cObj->getSubpart($template['total'],'###ITEM###');
		
		//language management
		$lang = t3lib_div::makeInstance('language');
		$lang->init($object->LLkey);
	
		//process each row
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$markerArray['###PAGE###'] = $object->linkKeyword(str_replace('tx_drwiki_pages_','',$row['reference']));             
 				$markerArray['###RATE###'] = $lang->sL('LLL:EXT:dr_wiki/pi1/plugins/locallang_mostrated.xml:mostrated.rated').$row['rate']; 
 				$markerArray['###VOTECOUNT###'] = $lang->sL('LLL:EXT:dr_wiki/pi1/plugins/locallang_mostrated.xml:mostrated.vote_count').$row['vote_count'];
				$content_item .= $object->cObj->substituteMarkerArrayCached($template['item'], $markerArray);
		}
		$subpartArray['###CONTENT###'] = $content_item;
		$content = $object->cObj->substituteMarkerArrayCached($template['total'], array(), $subpartArray);
		return $content;

	}
}
?>