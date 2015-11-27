<?php

/*
 * Xajax-Anwendung
 */
session_save_path("/tmp/");
@ session_start();
require_once (t3lib_extMgm :: extPath('xajax') . 'class.tx_xajax.php');
header("Content-Type: text/html; charset=utf-8");
function putGNDValues($gndid, $divid_gnd_id, $person, $divid_person) {

	$response = new tx_xajax_response();

	$response->assign($divid_gnd_id, 'value', $gndid);
	$response->assign($divid_person, 'value', $person);

	return $response;
}

function getGNDData($person, $div_id, $divid_gnd_id, $divid_person) {
	$debug = false;
	$content = "";
	$response = new tx_xajax_response();
	$erg = gndSearch($person);
	//echo '<xmp>' . print_r($erg, 1) . '</xmp>';
 foreach ($erg as $values) {
		//id="'.$div_id.'" onMouseOver="document.getElementById(\''.$div_id.'\').style.backgroundColor=\'orange\'"
		$dividtmp = md5($values['name']);
		$content .= '<div id="' . $dividtmp . '" onMouseOut="document.getElementById(\'' . $dividtmp . '\').style.backgroundColor=\'#769AFC\'"  onMouseOver="document.getElementById(\'' . $dividtmp . '\').style.backgroundColor=\'orange\'" style="height:11px;padding:5px;border: 1px solid gray;background-color:#769AFC" onClick="xajax_putGNDValues(\'' . $values["id"] . '\',\'' . $divid_gnd_id . '\',\'' . $values["name"] . '\',\'' . $divid_person . '\');document.getElementById(\'' . $div_id . '\').style.marginTop=\'10px\';document.getElementById(\'' . $div_id . '\').style.display=\'none\';">' . $values['name'] . '</div>';
	}
	$response->assign($div_id, 'style.display', 'none');
	$response->assign($div_id, 'innerHTML', $content);
	$response->assign($div_id, 'style.display', 'block');

	return $response;
}

function gndSearch($person) {
	$person = trim($person);
	header("Content-Type: text/html; charset=utf-8");
	$debug = false;
	$name = trim($_GET['name']);
	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->formatOutput = true;
	$doc->preserveWhiteSpace = true;
	$doc->load('http://textgridlab.org/pndsearch/pndquery.xql?ac=' . $person);
	//echo $doc->saveXML();
	//die();
	$names = $doc->getElementsByTagName("name");
	$nodeListLength = $names->length;
	// echo $nodeListLength;
	$erg = array ();

	for ($i = 0; $i < $nodeListLength; $i++) {
		$erg[$i]['id'] = preg_replace("|pnd:|", "", $doc->getElementsByTagName("person")->item($i)->getAttribute("id"));
		//$erg[$i]['name'] = $doc->getElementsByTagName("name")->item($i)->nodeValue;

		// get Person Data from gnd-id:    
		$tmp_doc = new DOMDocument('1.0', 'utf-8');
		$tmp_doc->formatOutput = true;
		$tmp_doc->preserveWhiteSpace = true;
		$tmp_doc->load('http://textgridlab.org/pndsearch/pndquery.xql?id=' . $erg[$i]['id']);

		if ($debug)
			echo $tmp_doc->saveXML();

		$preferredNameForThePerson = $tmp_doc->getElementsByTagName("preferredNameForThePerson")->item(0)->nodeValue;
		$dateOfBirth = $tmp_doc->getElementsByTagName("dateOfBirth")->item(0)->nodeValue;
		$dateOfDeath = $tmp_doc->getElementsByTagName("dateOfDeath")->item(0)->nodeValue;
		$placeOfBirth = $tmp_doc->getElementsByTagName("placeOfBirth")->item(0)->nodeValue;
		$adddata = "(" . ($dateOfBirth == "" ? "?" : $dateOfBirth) . "-" . ($dateOfDeath == "" ? "?" : $dateOfDeath) . "), Geb.Ort: " . ($placeOfBirth == "" ? "?" : $placeOfBirth);

		unset ($tmp_doc);
		$erg[$i]['name'] = $preferredNameForThePerson . " (" . $adddata . ")";
	}
	unset ($doc);
	return $erg;
}
?>
