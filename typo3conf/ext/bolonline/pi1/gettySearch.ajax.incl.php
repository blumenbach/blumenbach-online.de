<?php
/*
 * Xajax-Anwendung
 */
session_save_path("/tmp/");
@ session_start();
require_once (t3lib_extMgm :: extPath('xajax') . 'class.tx_xajax.php');
header("Content-Type: text/html; charset=utf-8");
function putGettyValues($gettyid,$divid_getty_id,$ort,$divid_ort,$koordinaten,$divid_koordinaten){
	
	$response = new tx_xajax_response();
	
	
	$response->assign($divid_getty_id, 'value', $gettyid);
	$response->assign($divid_ort, 'value', $ort);
	$response->assign($divid_koordinaten, 'value', $koordinaten);
	
	return $response;
}

function getGettyData($ort,$div_id,$divid_getty_id,$divid_ort,$divid_koordinaten){
	$debug = false;
	$content = "";
	$response = new tx_xajax_response();
	$erg = gettySearch($ort);
	//echo '<xmp>' . print_r($erg, 1) . '</xmp>';
	$content = '<div style="background-color:#555555;padding: 5px;color:white;border: 1px solid black;border-bottom:0px;font-size:0.9em"><u onClick="document.getElementById(\'' . $div_id . '\').style.display=\'none\'">schließen</u></div>';
	
	foreach($erg as $values){
	//id="'.$div_id.'" onMouseOver="document.getElementById(\''.$div_id.'\').style.backgroundColor=\'orange\'"
		$dividtmp = md5($values['name']);
		$content .= '<div id="'.$dividtmp.'" onMouseOut="document.getElementById(\''.$dividtmp.'\').style.backgroundColor=\'#769AFC\'"  onMouseOver="document.getElementById(\''.$dividtmp.'\').style.backgroundColor=\'orange\'" style="height:11px;padding:5px;border: 1px solid gray;background-color:#769AFC" onClick="xajax_putGettyValues(\''.$values["id"].'\',\''.$divid_getty_id.'\',&quot;'.$values["name"].'&quot;,\''.$divid_ort.'\',\''.preg_replace("|'|","\'",$values["coordinates"]).'\',\''.$divid_koordinaten.'\');document.getElementById(\''.$div_id.'\').style.marginTop=\'10px\';document.getElementById(\''.$div_id.'\').style.display=\'none\';">'.$values['name'].'</div>';
		
	//id="'.$div_id.'" onMouseOver="document.getElementById(\''.$div_id.'\').style.backgroundColor=\'orange\'"
		
//		$content .= '<div style="height:11px;padding:5px;border: 1px solid gray;background-color:lightgrey" onClick="xajax_putGettyValues(\''.$values["id"].'\',\''.$divid_getty_id.'\',\''.$values["name"].'\',\''.$divid_ort.'\',\''.preg_replace("|'|","\'",$values["coordinates"]).'\',\''.$divid_koordinaten.'\');document.getElementById(\''.$div_id.'\').style.display=\'none\';">'.$values['name'].'</div>';
		
		// auswahlmenue wieder ausblenden: document.getElementById(\''.$div_id.'_autofill\').style.display=\'none\'">
	}
	$response->assign($div_id, 'style.display', 'none');
	$response->assign($div_id, 'innerHTML', $content);
	$response->assign($div_id, 'style.display', 'block');
	
	return $response;
}

function gettySearch($ort){
	$ort = trim($ort);
	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->formatOutput = true;
	$doc->preserveWhiteSpace = true;
	$doc->load('http://textgridlab.org/tgnsearch/tgnquery.xql?ac='.$ort);
	//echo $doc->saveXML();
	
	$names = $doc->getElementsByTagName("name");
	$nodeListLength = $names->length;
	// echo $nodeListLength;
	$erg = array();
	
	for ($i = 0; $i < $nodeListLength; $i++) {
	    $pure_id = preg_replace("|tgn:|", "", $doc->getElementsByTagName("term")->item($i)->getAttribute("id"));
     $erg[$i]['id'] = $doc->getElementsByTagName("term")->item($i)->getAttribute("id");
	    $erg[$i]['name'] = $doc->getElementsByTagName("name")->item($i)->nodeValue;
	
	    $erg[$i]['name'] .= ", ".preg_replace("#\|#", "-", $doc->getElementsByTagName("path")->item($i)->nodeValue);
	
	    // get Geolocation-Data from tgn-id:
	    $coordinates = "";
	    $tmp_doc = new DOMDocument('1.0', 'utf-8');
	    $tmp_doc->formatOutput = true;
	    $tmp_doc->preserveWhiteSpace = true;
	    $tmp_doc->load('http://textgridlab.org/tgnsearch/tgnquery.xql?id=' . $pure_id);
	
	
	    if($debug) echo $tmp_doc->saveXML();
	
	    $latitude = $tmp_doc->getElementsByTagName("Latitude");
	    $_nodeListLength = $latitude->length;
	    for ($ii = 0; $ii < $_nodeListLength; $ii++) {
	        $coordinates .= $latitude->item($ii)->getElementsByTagName("Degrees")->item($ii)->nodeValue;
	        $coordinates .= "° ";
	        $coordinates .= $latitude->item($ii)->getElementsByTagName("Minutes")->item($ii)->nodeValue;
	        $coordinates .= "' ";
	        $coordinates .= $latitude->item($ii)->getElementsByTagName("Seconds")->item($ii)->nodeValue;
	        $coordinates .= "'' ";
	        $coordinates .= substr($latitude->item($ii)->getElementsByTagName("Direction")->item($ii)->nodeValue, 0, 1);
	        $coordinates .= ", ";
	    }
	
	
	    $longitude = $tmp_doc->getElementsByTagName("Longitude");
	    $_nodeListLength = $longitude->length;
	    for ($ii = 0; $ii < $_nodeListLength; $ii++) {
	        $coordinates .= $longitude->item($ii)->getElementsByTagName("Degrees")->item($ii)->nodeValue;
	        $coordinates .= "° ";
	        $coordinates .= $longitude->item($ii)->getElementsByTagName("Minutes")->item($ii)->nodeValue;
	        $coordinates .= "' ";
	        $coordinates .= $longitude->item($ii)->getElementsByTagName("Seconds")->item($ii)->nodeValue;
	        $coordinates .= "'' ";
	        $coordinates .= substr($longitude->item($ii)->getElementsByTagName("Direction")->item($ii)->nodeValue, 0, 1);
	    }
	
	    unset($tmp_doc);
	    $erg[$i]['coordinates'] = $coordinates;
	    $coordinates = "";
	}
	unset($doc);
	return $erg;
}
?>
