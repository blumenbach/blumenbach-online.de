<?php
/*
 * Xajax-Anwendung
 */
session_save_path("/tmp/");
@ session_start();
require_once (t3lib_extMgm :: extPath('xajax') . 'class.tx_xajax.php');

function getAccociationIDs($kerndaten_id,$block_bez,$block_id){
	$Bol = new BlumenbachOnline();
	$response = new tx_xajax_response();
//	$Bol->tablename = 'tx_bolonline_'.$block_bez;
//	$Bol->getTableColumns();
	$content = $block_bez.'<br>';
	ob_start();
	$ids_blau = array();
	$ids_rot = array();
	if($block_bez=="tx_bolonline_PartIII"){//=gelb
		// Blau-id(s) (PartII) muessen auswaehlbar sein
		
		$ids_blau = $Bol->getAssociationIDs($kerndaten_id,'tx_bolonline_PartII','b1',0);
	//	$content = "<select name='assoziation' onChange='xajax_setAccociationIDs(this.assoziation.value)'>";
		$content .= "<br>Zuordnung zu Blau: <select name='assoziation'>";
		foreach($ids_blau as $nr=>$a_data){
			$spid = $ids_blau[$nr]['id'];
			$bezeichner = $ids_blau[$nr]['b1'];
			$content .= "<option value='".$spid."'>".$bezeichner."</option>";
		}
		$content .= "</select>";
		
	}
	
	if($block_bez=="tx_bolonline_PartIV"){//=orange
		// Blau-id (PartII) muss auswaehlbar sein + Rot-ID (PartI) muss auswaehlbar sein
	
		$ids_blau = $Bol->getAssociationIDs($kerndaten_id,'tx_bolonline_PartII','b1',0);
	//	$content = "<select name='assoziation' onChange='xajax_setAccociationIDs(this.assoziation.value)'>";
		$content .= "<br>Zuordnung zu Blau: <select name='assoziation'>";
		foreach($ids_blau as $nr=>$a_data){
			$spid = $ids_blau[$nr]['id'];
			$bezeichner = $ids_blau[$nr]['b1'];
			$content .= "<option value='".$spid."'>".$bezeichner."</option>";
		}
		$content .= "</select>";
		
		$ids_rot = $Bol->getAssociationIDs($kerndaten_id,'tx_bolonline_PartI','a4',0);
	//	$content .= "<br><select name='assoziation2' onChange='xajax_setAccociationIDs(this.assoziation.value)'>";
		$content .= "<br>Zuordnung zu Rot: <select name='assoziation2'>";
		foreach($ids_rot as $nr=>$a_data){
			$spid = $ids_rot[$nr]['id'];
			$bezeichner = $ids_rot[$nr]['a4'];
			$content .= "<option value='".$spid."'>".$bezeichner."</option>";
		}
		$content .= "</select>";
	}

	
//	$content = "*** kerndaten_id=$kerndaten_id,block_bez=$block_bez ***";
	$response->assign('association_pd_' . $block_id, 'style.display', 'none');
	$response->assign('association_pd_' . $block_id, 'innerHTML', $content);
	$response->assign('association_pd_' . $block_id, 'style.display', 'block');

	return $response;
	//	return $response->getXML();
}


function setAccociationIDs($zuordnungstabellenname,$element_value,$element_id,$kerndaten_id,$block_bez,$block_id){
	$Bol = new BlumenbachOnline();
	$messagediv = $element_id."_message";
	$response = new tx_xajax_response();
	$response->assign($element_id, 'selected', 'selected');
	$response->assign($element_id, 'value', $element_value);
	$query = "";
	$a_element_value = array();
	$a_element_value = preg_split("#\|#",$element_value);
//	$anz_ids = sizeOf($a_element_value);
	$delete = true;
	$message = "";
	foreach($a_element_value as $element_value){
		$query .= $Bol->setAssociatedIDs($zuordnungstabellenname, $element_value, $kerndaten_id, $block_bez, $block_id, $delete);
		$delete = false;//bei BlockIII nur beim ersten Eintrag den Datensatz löschen
		if($element_value!=""){
			$message .= '<div style="font-size:11px;"><span style="color:red">ID '.$element_value.'</span> has been associated successfully to segment <strong>'.$block_bez.'</strong> with <strong>ID '.$block_id.'</strong></div>';
		} else {
			$message .= '<div style="font-size:11px;">This arrangement has been deleted successfully from segment <strong>'.$block_bez.'</strong> with <strong>ID '.$block_id.'</strong></div>';	
		}
	}
	
	//$message .= $query;
	// Message
//	$message = "<span style='color:red'>".$element_value." wurde erfolgreich zugeordnet!</span>";
	$response->assign($messagediv, 'style.display', 'none');
	$response->assign($messagediv, 'innerHTML', $message);
//	$response->assign($messagediv, 'innerHTML', $message.$query);
	$response->assign($messagediv, 'style.display', 'block');

	return $response;
	//	return $response->getXML();
}
?>