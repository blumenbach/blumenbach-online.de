<?php
// Returns a list of the pages that are in this category (default)
// or the category defined in the parameter.
// Alphabetical listing was inspired by MediaWiki's code

class tx_drwiki_pi1_categoryindex {
	
	var $object;
	
	function getDefaultParams(){
		return array('');
	}
	
	function main($object, $params){
    $entrykey = $params[0];
    $this->object = $object;
    if ($entrykey=='') { 
    	$entrykey = $object->piVars["keyword"];
    } else {
    	$entrykey = 'Category:'.$entrykey;
    }
     	
     	//get Category entries
	 	$pidList = $this->object->pi_getPidList($this->object->conf["pidList"], $this->object->conf["recursive"]);
	 	$enabledFields = $this->object->cObj->enableFields("tx_drwiki_pages");
	 	
	 	$newestEntries = $this->getNewestEntries();

	 	
	 	
	 	$whereString = "tx_drwiki_pages.pid IN (".$pidList.") ". $enabledFields .
	 				   " AND tx_drwiki_pages.uid IN (".$newestEntries.")".
	                   " AND tx_drwiki_pages.body LIKE '%[[".$GLOBALS['TYPO3_DB']->quoteStr($entrykey,'tx_drwiki_pages')."%'";	
	                   
	 	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			"keyword,uid",
			"tx_drwiki_pages",
	        $whereString
	    );
	    
	    $results = array();
	
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	        $results[] = $row;
	    }

		// old result set --> deprectated
		//$results = $object->getWikiInfos("keyword" ,TRUE, FALSE, TRUE, " AND tx_drwiki_pages.body LIKE '%[[".$GLOBALS['TYPO3_DB']->quoteStr($entrykey,'tx_drwiki_pages')."%'");
        
        if ($results){
	        $isNameSpace = $this->object->isNameSpace($this->object->getNameSpace($this->object->piVars["keyword"]));
	        foreach($results as $item) {        	
	        	// prevent adding Category into itself
	        	if ($item["keyword"] == $this->object->piVars["keyword"] AND $isNameSpace) continue;
	        	$getNS = preg_match_all( '/(.*)(:)(.*)/', $item["keyword"], $matchesNS );
	        	// get Namespace entry
	            $catNS = $matchesNS[1][0];
	            //get actual keyword, depending on active Namespace
	            if ($matchesNS[3][0]) $catWord = $matchesNS[3][0];
	            	else $catWord = $item["keyword"];
	            //build array
	            $rowArray[] = array("keyword" => $catWord, "namespace" =>$catNS);
	            
	            // get array containing the kewords w/o Namespace for sorting
				foreach ($rowArray as $key => $row) {
				    $keywordArr[$key]    = $row['keyword'];
				}	            
	            
	        }
	        	// make keword array w/o Namespace lowercase, so case-in-sensitive sorting
	        	// is bossible, as array_multisort sorts case-sensitive
	        	$array_lowercase = array_map('strtolower', $keywordArr);
	        	array_multisort($array_lowercase, SORT_ASC, $rowArray);
	        	$content = $this->formatList($rowArray);
        }
	
	  	return $content;
	}
	
	/*
	 * getNewestEntries
	 * @return: Comma seperated list of entries (uids) of the latest pages
	 */
	function getNewestEntries() {
		    	
     	//get Category entries
	 	$pidList = $this->object->pi_getPidList($this->object->conf["pidList"], $this->object->conf["recursive"]);
	 	$enabledFields = $this->object->cObj->enableFields("tx_drwiki_pages");
	 	
	 	// get newest entires of database
	 	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			"MAX(uid) AS uid",
			"tx_drwiki_pages",
	        "tx_drwiki_pages.pid IN (".$pidList.")".$enabledFields." GROUP by keyword ORDER BY uid DESC"
	    );
	    
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	        $results[] = $row["uid"];
	    }
	    return implode(",",$results);
	}

	function createLink($keyword, $namespace) {
		if ($namespace) {
			$linkTitle = $keyword . ' (' . $namespace .')';
			$linkKeyword = $namespace . ':' . $keyword;
		} else {
			$linkTitle = $keyword;
			$linkKeyword = $keyword;
		}

		return $link =$this->object->pi_linkTP_keepPIvars(htmlspecialchars($linkTitle), array("keyword" => htmlspecialchars($linkKeyword), "showUid" => ""), 1, 0);

	}
	
	/**
	 * Format a list of articles chunked by letter, either as a
	 * bullet list or a columnar format, depending on the length.
	 *
	 * @param array $articles
	 * @param int   $cutoff
	 * @return string
	 * @private
	 */
	function formatList( $articles, $cutoff = 6 ) {
		if ( count ( $articles ) > $cutoff ) {
			return $this->columnList( $articles );
		} elseif ( count($articles) > 0) {
			// for short lists of articles in categories.
			return $this->shortList( $articles );
		}
		return '';
	}

	/**
	 * Format a list of articles chunked by letter in a three-column
	 * list, ordered vertically.
	 *
	 * @param array $articles
	 * @return string
	 * @private
	 */
	function columnList( $articles ) {
		// divide list into three equal chunks
		$chunk = (int) (count ( $articles ) / 3);

		// get and display header
		$r = '<table width="100%"><tr valign="top">';

		$prev_start_char = 'none';

		// loop through the chunks
		for($startChunk = 0, $endChunk = $chunk, $chunkIndex = 0;
			$chunkIndex < 3;
			$chunkIndex++, $startChunk = $endChunk, $endChunk += $chunk + 1)
		{
			$r .= "<td>\n";
			$atColumnTop = true;

			// output all articles in category
			for ($index = $startChunk ;
				$index < $endChunk && $index < count($articles);
				$index++ )
			{
				// check for change of starting letter or begining of chunk
				if ( ($index == $startChunk) ||
					 (strtoupper($articles[$index]["keyword"]{0}) != strtoupper($articles[$index-1]["keyword"]{0})) )

				{
					if( $atColumnTop ) {
						$atColumnTop = false;
					} else {
						$r .= "</ul>\n";
					}
					$cont_msg = "";
					if ( strtoupper($articles[$index]["keyword"]{0}) == strtoupper($prev_start_char) )
						$cont_msg = " " . "<small>(continued)</small>";
					$r .= "<h4>" . htmlspecialchars( strtoupper($articles[$index]["keyword"]{0}) ) . "$cont_msg</h4>\n<ul>";
					$prev_start_char = strtoupper($articles[$index]["keyword"]{0});
				}

				$r .= "<li>".$this->createLink($articles[$index]["keyword"],$articles[$index]["namespace"])."</li>\n";
			}
			if( !$atColumnTop ) {
				$r .= "</ul>\n";
			}
			$r .= "</td>\n";


		}
		$r .= "</tr></table>";
		return $r;
	}

	/**
	 * Format a list of articles chunked by letter in a bullet list.
	 * @param array $articles
	 * @return string
	 * @private
	 */
	function shortList( $articles ) {
		$r = "<h4>" . htmlspecialchars( strtoupper($articles[0]["keyword"]{0}) ) . "</h4>\n";
		$r .= "<ul><li>".$this->createLink($articles[0]["keyword"],$articles[0]["namespace"])."</li>\n";
		for ($index = 1; $index < count($articles); $index++ )
		{
			if (strtoupper($articles[$index]["keyword"]{0}) != strtoupper($articles[$index-1]["keyword"]{0}))
			{
				$r .= "</ul><h4>" . htmlspecialchars( strtoupper($articles[$index]["keyword"]{0}) ) . "</h4>\n<ul>";
			}

			$r .= "<li>".$this->createLink($articles[$index]["keyword"],$articles[$index]["namespace"])."</li>\n";
		}
		$r .= "</ul>";
		return $r;
	}

}
	
?>