<?php
/*
 * Created on 07.02.2011
 */
 // ################################# A1: Hauptkategorie #################################
	if (isset ($_REQUEST["hauptkategorie_id"])) { //Neueintrag in Kategorien
//		$this->Bol->setMainCategory($_REQUEST["hauptkategorie_id"], $_SESSION['update_id']);
	}
	$hauptkategorien = $this->Bol->getMainCategories();//$_SESSION['hauptkategorie_id']
//	$this->Bol->dump($hauptkategorien);
	$content .= '<tr>';
	$content .= '<td colspan="2" style="background-color:lightgrey"><form name="hauptkategorie_select" action="' . $this->pi_getPageLink($GLOBALS['TSFE']->id) . '" method="POST">';
	$content .= '<input type="hidden" name="todo" value="update">';
	$content .= '<select style="background-color:lightblue;font-weight:bold;width:100%;height:35px;font-size:19px;padding:2px" name="hauptkategorie_id" onChange="document.hauptkategorie_select.submit()">
	<option value="all">Alle Kategorien</option>';
//	$kategorien = array ();
//	$kategorien = $this->Bol->getMainCategories();
	//						$this->Bol->dump($kategorien);
	foreach ($hauptkategorien as $nr => $id_kategorie) {		
		$kategorie = $hauptkategorien[$nr]["kategorie"];
		$id = $hauptkategorien[$nr]["id"];
		$content .= '<option value="' . $id . '"' . ($id == $_SESSION['hauptkategorie_id'] ? " selected" : "") . '>' . substr($kategorie, 0, 250) . '</option>';
	}
	$content .= '</select>';
	$content .= '</form></td></tr>';
// ################################# Ende A1: Hauptkategorie #################################
?>
