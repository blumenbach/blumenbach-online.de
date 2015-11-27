<?php
/*
 * Xajax-Anwendung
 */
session_save_path("/tmp/");
@ session_start();
require_once (t3lib_extMgm :: extPath('xajax') . 'class.tx_xajax.php');

function setFields($tablename, $label = "", $anz = 1, $blocknr = 1, $show_multiplier = true) {
	$Bol = new BlumenbachOnline();
	$response = new tx_xajax_response();
	$submitbutton = 'update'; //htmlspecialchars(tx_bolonline_pi1::pi_getLL('update_button_label'));
	$submit_label = 'update'; //htmlspecialchars(tx_bolonline_pi1::pi_getLL('submit_label_update'));
	$anz = $anz < 1 ? 1 : $anz;
	$content = "";
	$unique_id = md5($tablename);
	$link = '/intern/bol_db/?no_cache=1';

	$content .= "\n" . '<form action="' . $link . '#' . $unique_id . '" method="POST" id="contentpart' . $unique_id . '" name="contentpart' . $unique_id . '" enctype="multipart/form-data">' . "\n";

	$Bol->tablename = $tablename;

	$Bol->getTableColumns();
	//$content .= $Bol->processMediaFileUpload($_SESSION['update_id']);
	//	$Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);
	//	$blockbez = '<br /><b>Block: '.$Bol->tablename.'</b><br />';


	// Blockbezeichner aus Sprachdatei holen:
	$__blockbez = $Bol->pi_getLL($Bol->tablename . '.label');
	$blockbez = '<br /><b style="background-color:gold;font-size:1.3em;padding:5px;border:1px solid black;">' . $__blockbez . '</b><br /><br /><br />';
	
//	$blockbez = '<br /><b class="blockbez_' . $tablename . '">Block: ' . $tablename . '</b>' . "\n" . '<br />';
	$content .= $blockbez;
	//	$content .= $Bol->show($_SESSION['update_id']);

	// Nachsehen, wieviele Bloecke in DB bereits existieren:
	$anz_vorhandener_bloecke_in_db = $Bol->getBlocksize($_SESSION['update_id']);
	if ($Bol->debug) {

		$content .= '$anz:' . $anz . '<br>';
		$content .= '$anz_vorhandener_bloecke_in_db:' . $anz_vorhandener_bloecke_in_db . '<br>';
	}

	$content .= $Bol->show($_SESSION['update_id']);

	for ($i = $anz_vorhandener_bloecke_in_db; $i < $anz; $i++) {
		for ($ii = 0; $ii < 5; $ii++) {
			//name="fuploadtx_bolonline_PartIII_118_3_0"+ bei den hier neu erzeugten upload-feldern: _blocknr, bei 1 beginnend!
			$content .= '<input style="font-size:11px;width:200px;height:22px;margin:2px" type="file" type="file" name="fupload_' . $tablename . '_0_' . $_SESSION['update_id'] . '_' . $ii . '_' . ($i +1) . '"><br />' . "\n";
		}

		if ($anz > $anz_vorhandener_bloecke_in_db)
			$content .= $Bol->show(0); //bei einem neu hinzugefügten Feld (ueber ajax) der Fall!

		$content .= "<br /><br clear='all' />";

	}
	$content .= '<h3>' . $submit_label . '</h3><br>' . "\n" . '
									<input type="submit" name="[submit_button]" value="' . $submitbutton . '">' . "\n";
	$content .= '</form>' . "\n";

	if ($show_multiplier) {
		$content .= "\n" . '&nbsp;<u onClick="xajax_setFields(\'' . $tablename . '\',\'' . $label . '\',\'' . ($anz_vorhandener_bloecke_in_db +1) . '\',\'true\')">+ weiterer Block</u><br />' . "\n";
	}

	$response->assign('partcontentdiv_' . $label, 'style.display', 'none');
	$response->assign('partcontentdiv_' . $label, 'innerHTML', $content);
	$response->assign('partcontentdiv_' . $label, 'style.display', 'block');

	return $response;
	//	return $response->getXML();
}

function delImage($id, $tablename) {
	$Bol = new BlumenbachOnline();
	$response = new tx_xajax_response();

	if (!$id > 0)
		return "";
	$response = new tx_xajax_response();
	$deleted = (boolean) false;
	$deleted = $Bol->deleteMediafile($id, $tablename) . ':<br />';
	if ($deleted) {
		$content .= 'Bild "' . $id . '" wurde erfolgreich gel&ouml;scht!' . "\n";
	} else {
		$content .= '<span style="color:red">Bild "' . $id . '" konnte nicht gel&ouml;scht werden!</span>' . "\n";
	}
	//	if ($_SESSION['todo'] != 'update')
	//		$content = '';
	$response->assign('bild_' . $id, 'style.display', 'none');
	$response->assign('bild_' . $id, 'innerHTML', $content);
	$response->assign('bild_' . $id, 'style.display', 'block');
	return $response;
}
?>