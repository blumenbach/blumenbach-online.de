<?php

class tx_drwiki_pi1_keyword_index {
	
	var $object;
			
	function getDefaultParams(){
		return array('');
	}
	
	function main($object, $params){

     	$this->object = $object;
		$results = $object->getWikiInfos("keyword" ,TRUE, '', TRUE, ' ORDER BY keyword ASC');

        if ($results){
	        foreach($results as $item) {        	
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