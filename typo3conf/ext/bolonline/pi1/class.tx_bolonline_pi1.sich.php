<?php
ini_set('error_reporting', "E_ERROR | E_PARSE");
session_save_path("/tmp/");
@session_start();
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Sven Thomas <thomas@sub.uni-goettingen.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once (PATH_tslib . 'class.tslib_pibase.php');
require_once ('class.BlumenbachOnline.php');
/*** 
 * 
 * Plugin 'BOL-Datenbank' for the 'bolonline' extension.
 *
 * @author	Sven Thomas <thomas@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_bolonline
 */
class tx_bolonline_pi1 extends tslib_pibase {
    var $prefixId = 'tx_bolonline_pi1'; // Same as class name
    var $scriptRelPath = 'pi1/class.tx_bolonline_pi1.php'; // Path to this script relative to the extension dir.
    var $extKey = 'bolonline'; // The extension key.
    var $pi_checkCHash = true;
    var $BOL = null;
    var $fe_user = array ();
    /**
     * The main method of the PlugIn
     *
     * @param	string		$content: The PlugIn content
     * @param	array		$conf: The PlugIn configuration
     * @return	The content that is displayed on the website
     */

    function getProjecttitle($content, $conf) {
        if (!isset ($_GET["P"]))
            return $content;
        return $content . current($GLOBALS['TYPO3_DB']->sql_fetch_row($GLOBALS['TYPO3_DB']->exec_SELECTquery("pisprojecttitle", "tx_pisprojectlist_project", "uid=" . $_GET["P"])));
    }

    function main($content, $conf) {
        $this->Bol = new BlumenbachOnline();
        $this->conf = $conf;

        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->fe_user = $this->Bol->fe_userdata;
        @ session_start();
        $submitbutton = "";

//		include_once("./bolonline.main.conf.php");

        /* Spezialfelder und Felder an/aus */
        include_once("bolonline.main.conf.php");
        $content .= $jscript_special_inputfields;//javascript fuer Spezialfelder

        //		if($this->Bol->debug) $content .= "<xmp>".print_r($_REQUEST, 1)."</xmp>";
        //		$GLOBALS['TYPO3_DB']->sql_free_result($res);
        #$content = $this->Bol->showAll($this->piVars['limit']);
        //		$content .= $this->Bol->show($_POST['id']);

        $_SESSION['todo'] = $_REQUEST["todo"] != "" ? $_REQUEST["todo"] : $_SESSION['todo'];
        $_SESSION['update_id'] = $_REQUEST["kerndaten_id"][0] != "" ? $_REQUEST["kerndaten_id"][0] : $_SESSION['update_id'];
        $_SESSION['hauptkategorie_id'] = $_REQUEST['hauptkategorie_id']!=""?$_REQUEST['hauptkategorie_id']:$_SESSION['hauptkategorie_id'];
        $_SESSION['hauptkategorie_update_id'] = $_REQUEST['hauptkategorie_update_id']!=""?$_REQUEST['hauptkategorie_update_id']:$_SESSION['hauptkategorie_update_id'];

        ######## XAJAX-Initialisieren:
        //require('/var/www/produktion.blumenbach.de/typo3conf/ext/bolonline/pi1/fileupload.ajax.server.php');
        require (t3lib_extMgm :: extPath('xajax') . 'class.tx_xajax.php');
        $xajax = t3lib_div :: makeInstance('tx_xajax');
        $xajax->errorHandlerOn();
        $xajax->outputEntitiesOn();
        $xajax->cleanBufferOn(); //wichtig!
        $xajax->setTimeout(500); //disabled
        $xajax->setLogFile("/var/www/produktion.blumenbach.de/typo3conf/ext/bolonline/test_ajax.log");
        $xajax->decodeUTF8InputOn(); // for input
        $xajax->setCharEncoding('utf-8'); //for output
        $xajax->statusMessagesOn();
        $xajax->debugOff();
        $xajax->registerExternalFunction('setUploadFields', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/fileupload.ajax.incl.php');
        $xajax->registerExternalFunction('setNoAssignedUploadFields', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/fileupload.ajax.incl.php');
        $xajax->registerExternalFunction('delImage', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/fileupload.ajax.incl.php');
        $xajax->registerExternalFunction('setFields', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/fileupload.ajax.incl.php');
        $xajax->registerExternalFunction('getDBDumpfile', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/datensicherung.php');
        $xajax->registerExternalFunction('getAccociationIDs', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/associations.pulldown.ajax.incl.php');
        $xajax->registerExternalFunction('setAccociationIDs', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/associations.pulldown.ajax.incl.php');
        $xajax->registerExternalFunction('getGettyData', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/gettySearch.ajax.incl.php');
        $xajax->registerExternalFunction('putGettyValues', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/gettySearch.ajax.incl.php');
        $xajax->registerExternalFunction('getGNDData', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/gndSearch.ajax.incl.php');
        $xajax->registerExternalFunction('putGNDValues', t3lib_extMgm :: siteRelPath('bolonline') . '/pi1/gndSearch.ajax.incl.php');

        /*-------------- under development.... ---------------------*/
//        $bis = rand(11,2999);
//        echo $bis;
//        for($i=0;$i<$bis;$i++) {
//            echo chr(rand(0,150))." ";
//        }
//        die();
        /*------------ //under development.... ---------------------*/


        //print_r($_POST);
        $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $xajax->getJavascript(t3lib_extMgm :: siteRelPath('xajax'));
        $xajax->processRequests();
        $content .= '<table style="width:100%;border: 1px solid black">';

        if ($_SESSION['todo'] == "update" && isset ($_SESSION['update_id'])) {
            require_once("hauptkategorie.changer.incl.php");
        }



        //###### Backup-Link: ############################################################################################
        include_once("db_backup.php");
        $content .= '<tr><td colspan="2"><div style="padding:5px;" id="db_backuplink" onClick="xajax_getDBDumpfile()">o <u>Hier klicken um eine Datensicherungsdatei zu erzeugen...</u></div></td></tr>';
        ##################################################################################################################



        $content .= '<tr><td colspan="2" style="background-color:lightgrey;height:1px;padding:1px"></td></tr>';

        //		$content .= 'Mediafiles: <xmp style="color:green">'.print_r($_FILES,1	).'</xmp>';
        // ################################# Datensatz editieren #################################
        $content .= '<tr><td style="width:50%">';
        $content .= '<form name="todo" action="' . $this->pi_getPageLink($GLOBALS['TSFE']->id) . '" method="POST">';
        $content .= '<select style="background-color:#fcfbc4;width:100%;padding:2px" name="todo" onChange="document.todo.submit()">
						<option value="">bitte wählen</option>
				   		<option style="color:blue" value="insert"' . ($_SESSION['todo'] == "insert" ? " selected" : "") . '>neuer Datensatz</option>
					<option style="color:red" value="update"' . ($_SESSION['todo'] == "update" ? " selected" : "") . '>bestehenden Datensatz editieren</option>
					</select>';
        $content .= '</form></td>';
        //print_r($_POST);

        if ($_SESSION['todo'] == 'update') {
            $content .= '<td style="width:50%"><form name="update" action="' . $this->pi_getPageLink($GLOBALS['TSFE']->id) . '" method="POST">';
            $content .= '<input type="hidden" name="todo" value="update">';
            $content .= '<select style="background-color:#fda92a;width:100%;padding:2px" name="kerndaten_id[]" onChange="document.update.submit()">
																		<option value="">bitte wählen</option>';

            /****** Hauptkategorie-Zuordnung aendern: *******************/
            $datensatz_ids = array ();
            $datensatz_ids = $this->Bol->getUpdateTeaser($_SESSION['hauptkategorie_id']/*$_SESSION['hauptkategorie_update_id']*/);
//			$content .= $this->Bol->dump($datensatz_ids);
            foreach ($datensatz_ids as $id => $value) {
                $zugeordnete_kategorie = $this->Bol->getMainCategories($id);
                $content .= '<option value="' . $id . '"' . ($_SESSION['update_id'] == $id ? " selected" : "") . '>' . substr($value, 0, 250) . ' ->'.$zugeordnete_kategorie[0]['kategorie'].'</option>';
            }
            $content .= '</select>';
            $content .= '</form></td></tr>';

        }
        // ################################# Ende Datensatz editieren #################################

        $content .= '<tr><td colspan="2" style="background-color:lightgrey;height:1px;padding:1px"></td></tr>';

        if ($_SESSION['todo'] == "update" && isset ($_SESSION['update_id'])) {
            require("hauptkategorie.updater.incl.php");
        }


        /*--------- Funktion fuer Multiple Select der Tabellenzuordnung gelb ------*/
        $content .= '<script language="JavaScript">
                        function getMultiple(ob) {
                                var arSelected = "";
                                var counter = 0;
                                while (ob.selectedIndex != -1) {
                                if (counter==0) arSelected+= ob.options[ob.selectedIndex].value;
                                else arSelected+="|"+ob.options[ob.selectedIndex].value;
                                        ob.options[ob.selectedIndex].selected = false;
                                        counter++;
                                } // You can use the arSelected array for further processing.

                        return arSelected;
                        }
                      </script>';
        /*-------- /Funktion fuer Multiple Select der Tabellenzuordnung gelb ------*/

        $content .= '<tr><td colspan="2" style="background-color:lightgrey;height:1px;padding:1px"></td></tr>';
        $content .= '<tr><td colspan="2" style="height:10px"></td></tr>';

        //$content .= '<form action="' . $this->pi_getPageLink($GLOBALS['TSFE']->id) . '" method="POST" id="stammdaten" name="stammdaten">';

        //$content .= '<tr><td><br /><br />'.$this->setUploadFields().'</td></tr>';

        //		print_r($this->Bol->getTableColumns());
        $content .= '<tr><td colspan="2">';

        #if($_POST['todo']!=0){


        switch ($_SESSION['todo']) {
            case "insert" :

                $submitbutton = htmlspecialchars($this->pi_getLL('submit_button_label'));
                $submit_label = htmlspecialchars($this->pi_getLL('submit_label_new'));
                //		$content .=  'insert';
                //				$content .= $this->Bol->show();
                $content .= '<form name="neueintrag" method="POST">';
                $this->Bol->tablename = 'tx_bolonline_Kerndaten';


                $this->Bol->getTableColumns();

                $blockbez = '<br /><b style="color:green">Block: ' . $this->Bol->tablename . '</b><br />';
                $content .= $blockbez;
                $content .= $this->Bol->show(0) . '<br /><br /><br />';
                $kerndaten_id = $this->Bol->insert(t3lib_div :: _POST()); // da $this->Bol->tablename = 'tx_bolonline_Kerndaten' ist die hier zurückgegebene id die kerndaten_id!

                $this->Bol->tablename = 'tx_bolonline_PartI';
                $this->Bol->getTableColumns();
                $blockbez = '<br /><b style="color:red">Block: ' . $this->Bol->tablename . '</b><br />';
                $content .= $blockbez;
                $content .= $this->Bol->show(0) . '<br /><br /><br />';
                $this->Bol->insert(t3lib_div :: _POST(), $kerndaten_id);

                $this->Bol->tablename = 'tx_bolonline_PartII';
                $this->Bol->getTableColumns();
                $blockbez = '<br /><b style="color:blue">Block: ' . $this->Bol->tablename . '</b><br />';
                $content .= $blockbez;
                $content .= $this->Bol->show(0) . '<br /><br /><br />';
                $this->Bol->insert(t3lib_div :: _POST(), $kerndaten_id);

                $this->Bol->tablename = 'tx_bolonline_PartIII';
                $this->Bol->getTableColumns();
                $blockbez = '<br /><b style="color:yellow">Block: ' . $this->Bol->tablename . '</b><br />';
                $content .= $blockbez;
                $content .= $this->Bol->show(0) . '<br /><br /><br />';
                $this->Bol->insert(t3lib_div :: _POST(), $kerndaten_id);

                $this->Bol->tablename = 'tx_bolonline_PartIV';
                $this->Bol->getTableColumns();

                // Blockbezeichner aus Sprachdatei holen:
                $__blockbez = parent :: pi_getLL($this->tablename . '.d');
                $blockbez = '<br /><b style="color:orange">' . $__blockbez . '</b><br />';
                $content .= $blockbez;
                $content .= $this->Bol->show(0) . '<br /><br /><br />';
                $this->Bol->insert(t3lib_div :: _POST(), $kerndaten_id);
                $content .= '<input type="submit" value="' . $submit_label . '"></form>';
                //				echo 'UPDATE-ID:'.$kerndaten_id;
                if ($kerndaten_id > 0) {
                    $_SESSION['todo'] = "update";
                    $_SESSION['update_id'] = $kerndaten_id;

                    $text = "/intern/bol_db/?no_cache=1&kerndaten_id=" . $kerndaten_id;
                    $link = $text;
                    //					$link = $text . $this->pi_getPageLink($GLOBALS['TSFE']->$kerndaten_id, '');
                    //					echo 'WEITERLEITUNG nach: '.$link;
                    @ header("Location: " . $link);
                }

                break;

            case "update" :
                $submitbutton = htmlspecialchars($this->pi_getLL('update_button_label'));
                $submit_label = htmlspecialchars($this->pi_getLL('submit_label_update'));
                if ($_SESSION['update_id'] > 0) {

                    //					$this->Bol->tablename = 'tx_bolonline_Kerndaten';
                    //					$this->Bol->getTableColumns();
                    //					echo $this->Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);
                    //					$blockbez = '<br /><b>Block: ' . $this->Bol->tablename . '</b><br />';
                    //					$content .= $blockbez;
                    //					$content .= $this->Bol->show($_SESSION['update_id']);

                    // File-Upload-Felder:
                    //					$content .= '<script language="JavaScript">xajax_setUploadFields(1,"_kerndaten")</script>';
                    $content .= '<style type="text/css">' . "\n";
                    $content .= '#partcontentdiv_kerndaten {
                                        border: 2px solid green;
                                        padding: 5px;
                                }
                                #partcontentdiv_partI {
                                        border: 2px solid red;
                                        padding: 5px;
                                }
                        #partcontentdiv_partII {
                                        border: 2px solid blue;
                                        padding: 5px;
                                }
                        #partcontentdiv_partIII {
                                        border: 2px solid yellow;
                                        padding: 5px;
                                }
                        #partcontentdiv_partIV {
                                        border: 2px solid orange;
                                        padding: 5px;
                                }
                                .blockbez_tx_bolonline_Kerndaten { color: green }
                                .blockbez_tx_bolonline_PartI { color: red; }
                                .blockbez_tx_bolonline_PartII { color: blue; }
                                .blockbez_tx_bolonline_PartIII { color: yellow; }
                                .blockbez_tx_bolonline_PartIV { color: orange; }
										
										
                                ' . "\n";
                    $content .= '</style>' . "\n";

                    // alle anderen Felder:
                    $content .= '<script language="JavaScript">xajax_setFields("tx_bolonline_Kerndaten","kerndaten",1,0,0)</script>';
                    $content .= '<script language="JavaScript">xajax_setFields("tx_bolonline_PartI","partI",1,1)</script>';
                    $content .= '<script language="JavaScript">xajax_setFields("tx_bolonline_PartII","partII",1,1)</script>';
                    $content .= '<script language="JavaScript">xajax_setFields("tx_bolonline_PartIII","partIII",1,1)</script>';
                    $content .= '<script language="JavaScript">xajax_setFields("tx_bolonline_PartIV","partIV",1,1)</script>';

                    $content .= '<div id="partcontentdiv_kerndaten" style="width:700px;"><h1>Kerndaten</h1></div><br /><br />';
                    $content .= '<div id="partcontentdiv_partII" style="width:700px;"><h1>PartII-FELDER</h1></div><br /><br />';
                    $content .= '<div id="partcontentdiv_partI" style="width:700px;"><h1>PartI-FELDER</h1></div><br /><br />';
                    $content .= '<div id="partcontentdiv_partIII" style="width:700px;"><h1>PartIII-FELDER</h1></div><br /><br />';
                    $content .= '<div id="partcontentdiv_partIV" style="width:700px;"><h1>PartIV-FELDER</h1></div><br /><br />';

                    //					if ($_SESSION['todo'] != 'delete' && $_SESSION['todo'] != '') {
                    //						$content .= '</td></tr><tr><td>
                    //						<br><br>
                    //						<h3>' . $submit_label . '</h3><br>
                    //						<input type="submit" name="' . $this->prefixId . '[submit_button]" value="' . $submitbutton . '">
                    //						</form>
                    //						<br />
                    //						<!--<p>You can click here to ' . $this->pi_linkToPage('get to this page again', $GLOBALS['TSFE']->id) . '</p>-->
                    //							';
                    //					}

                    $this->Bol->tablename = 'tx_bolonline_Kerndaten';
                    $this->Bol->getTableColumns();
                    //					$this->Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);
                    $this->Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);
                    /*
					$this->Bol->tablename = 'tx_bolonline_Kerndaten';
					$this->Bol->getTableColumns();
					$this->Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);
					$blockbez = '<br /><b>Block: '.$this->Bol->tablename.'</b><br />';
					$content .= $blockbez;
					$content .= $this->Bol->show($_SESSION['update_id']);
                    */
                    $this->Bol->tablename = 'tx_bolonline_PartI';
                    $this->Bol->getTableColumns();
                    $this->Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);
                    /*
					$blockbez = '<br /><b>Block: '.$this->Bol->tablename.'</b><br />';
					$content .= $blockbez;
					$content .= $this->Bol->show($_SESSION['update_id']);
                    */
                    $this->Bol->tablename = 'tx_bolonline_PartII';
                    $this->Bol->getTableColumns();
                    $this->Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);

                    //					$content .= '<script language="JavaScript">xajax_setFields("tx_bolonline_PartII","partII",1)</script>';
                    /*
					$blockbez = '<br /><b>Block: '.$this->Bol->tablename.'</b><br />';
					$content .= $blockbez;
					$content .= $this->Bol->show($_SESSION['update_id']);
                    */
                    $this->Bol->tablename = 'tx_bolonline_PartIII';
                    $this->Bol->getTableColumns();
                    $this->Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);
                    /*
					$blockbez = '<br /><b>Block: '.$this->Bol->tablename.'</b><br />';
					$content .= $blockbez;
					$content .= $this->Bol->show($_SESSION['update_id']);
                    */
                    $this->Bol->tablename = 'tx_bolonline_PartIV';
                    $this->Bol->getTableColumns();
                    $this->Bol->update(t3lib_div :: _POST(), $_SESSION['update_id']);
                    #$content .= '<script language="JavaScript">xajax_setFields("tx_bolonline_PartIV","partIV",1)</script>';
                    /*
					$blockbez = '<br /><b>Block: '.$this->Bol->tablename.'</b><br />';
					$content .= $blockbez;
					$content .= $this->Bol->show($_SESSION['update_id']);
                    */

                }
                break;

            case "delete" :
                $deleted = $this->Bol->delete($_REQUEST['id'], $_REQUEST['tablename']);
                if ($deleted) {
                    $content .= 'Der Datensatz Nr. ' . $_REQUEST['id'] . 'aus Tabelle ' . $_REQUEST['tablename'] . ' wurde gelöscht!';
                } else {
                    $content .= "<div style='color:red'>something went wrong, couldǹt delete!</div>";
                }
                break;

            default :
            //				$content .= $this->Bol->show($_GET['id']);
            //				$content .= "Bitte Aktion wählen!";
                break;
        }

        $content .= '</td></tr></table>';

        return $this->pi_wrapInBaseClass($content);
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bolonline/pi1/class.tx_bolonline_pi1.php']) {
    include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bolonline/pi1/class.tx_bolonline_pi1.php']);
}
?>