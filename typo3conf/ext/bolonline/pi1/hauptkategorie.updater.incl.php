<?php
/*
 * Created on 07.02.2011
 */
 // ################################# A1: Hauptkategorie #################################
	if (isset ($_REQUEST["hauptkategorie_update_id"])) { //Neueintrag in Kategorien
		$this->Bol->setMainCategory($_REQUEST["hauptkategorie_update_id"], $_SESSION['update_id']);
	}
	$zugeordnete_kategorie = $this->Bol->getMainCategories($_SESSION['update_id']);
	//			$this->Bol->dump($zugeordnete_kategorie);
	$content .= '<tr>';
	$content .= '<td colspan="2" style="background-color:white"><form name="hauptkategorie_update" action="' . $this->pi_getPageLink($GLOBALS['TSFE']->id) . '" method="POST">';
	$content .= "<div style='padding: 5px'>Hauptrubrik-Zuordnung ändern:</div>";
	$content .= '<input type="hidden" name="todo" value="update">';
	$content .= '<input type="hidden" name="hauptkategorie_id" value="">';
	$content .= '<select style="background-color:#fcfbc4;font-weight:bold;width:100%;height:18px;font-size:12px;padding:1px" name="hauptkategorie_update_id" onChange="document.hauptkategorie_select.hauptkategorie_id.value=document.hauptkategorie_update.hauptkategorie_update_id.value;document.hauptkategorie_update.submit();">
															<option value="">bitte wählen</option>';
	$kategorien = array ();
	$kategorien = $this->Bol->getMainCategories();
	//						$this->Bol->dump($kategorien);
	foreach ($kategorien as $nr => $id_kaegorie) {
		$kategorie = $kategorien[$nr]["kategorie"];
		$id = $kategorien[$nr]["id"];
		$content .= '<option value="' . $id . '"' . ($id == $zugeordnete_kategorie[0]['id'] ? " selected" : "") . '>' . substr($kategorie, 0, 250) . '</option>';
	}
	$content .= '</select>';
	$content .= '</form></td></tr>';
	
// ################################# Ende A1: Hauptkategorie #################################
?>
