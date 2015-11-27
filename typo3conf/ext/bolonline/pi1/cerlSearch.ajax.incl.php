<?php
/*
 * Xajax-Anwendung
*/
session_save_path("/tmp/");
@ session_start();
#require_once (t3lib_extMgm :: extPath('xajax') . 'class.tx_xajax.php');
header("Content-Type: text/html; charset=utf-8");
function putCERLValues($cerlid, $divid_cerl_id, $person, $divid_person) {

  $response = new tx_xajax_response();

  $response->assign($divid_cerl_id, 'value', $cerlid);
  $response->assign($divid_person, 'value', $person);

  return $response;
}

function getCERLData($person, $div_id, $divid_cerl_id, $divid_person) {
  $debug = false;
  $content = "";
  $response = new tx_xajax_response();
  $erg = cerlSearch($person);
  //echo '<xmp>' . print_r($erg, 1) . '</xmp>';
  $content = '<div style="background-color:#555555;padding: 5px;color:white;border: 1px solid black;border-bottom:0px;font-size:0.9em"><u onClick="document.getElementById(\'' . $div_id . '\').style.display=\'none\'">schließen</u></div>';
  foreach ($erg as $values) {
    //id="'.$div_id.'" onMouseOver="document.getElementById(\''.$div_id.'\').style.backgroundColor=\'orange\'"
    $dividtmp = md5($values['name']);
    $content .= '<div id="' . $dividtmp . '" onMouseOut="document.getElementById(\'' . $dividtmp . '\').style.backgroundColor=\'#769AFC\'"  onMouseOver="document.getElementById(\'' . $dividtmp . '\').style.backgroundColor=\'orange\'" style="height:11px;padding:5px;border: 1px solid gray;background-color:#769AFC" onClick="xajax_putCERLValues(\'' . $values["id"] . '\',\'' . $divid_cerl_id . '\',\'' . $values["name"] . '\',\'' . $divid_person . '\');document.getElementById(\'' . $div_id . '\').style.marginTop=\'10px\';document.getElementById(\'' . $div_id . '\').style.display=\'none\';">' . $values['name'] . '</div>';
  }
  $response->assign($div_id, 'style.display', 'none');
  $response->assign($div_id, 'innerHTML', $content);
  $response->assign($div_id, 'style.display', 'block');

  return $response;
}

function cerlSearch($person) {
  $person = trim($person);
  header("Content-Type: text/xml; charset=utf-8");
  $debug = false;
  $name = trim($_GET['name']);
  $doc = new DOMDocument('1.0', 'utf-8');
  $doc->formatOutput = true;
  $doc->preserveWhiteSpace = true;
  $uri = 'http://sru.cerl.org/thesaurus?version=1.1&operation=searchRetrieve&query=ct.personalName='.
          urlencode($person).'&maximumRecords=99';

  $doc->load($uri);
//echo $doc->saveXML();

  $records = $doc->getElementsByTagName("record");
  $counter = 0;
  $erg = array();
  foreach($records as $record) {
    $name = "";
    $biographicalData = "";
    $activityNote = "";
    $name = $record->getElementsByTagName("info")->item($i)->getElementsByTagName("display")->item(0)->nodeValue;
    try {
      $biographicalData = $record->getElementsByTagName("info")->item($i)->getElementsByTagName("biographicalData")->item(0)->nodeValue;
      $activityNote = $record->getElementsByTagName("info")->item($i)->getElementsByTagName("activityNote")->item(0)->nodeValue;
    } catch(Exception $e) {
      //echo 'Fehler: '.$e;
    }
    $cerlid = $record->getAttribute("id");
    if($cerlid!="") {
      $erg[] = array(
              'name' => $name.' ('.$biographicalData.', '.$activityNote.')',
              'id' => $cerlid
      );
    }
    $counter++;
  }

  unset ($doc);

  if($debug) print_r($erg);
  return $erg;
}
?>
