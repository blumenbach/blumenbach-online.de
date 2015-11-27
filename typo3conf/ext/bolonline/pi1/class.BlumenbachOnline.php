<?php
require_once (PATH_tslib . 'class.tslib_pibase.php');
session_save_path("/tmp/");
class BlumenbachOnline extends tslib_pibase {

  private $tableColumns = array ();
  public $fe_userdata = array ();
  private $uFiles = null;
  public $workpath = "";
  public $workpath_rel = ""; //pfad relativ zu docroot
  public $tablename = "tx_bolonline_Kerndaten"; //Name der gerade zu verwendenden Tabelle
  public $debug = 0;
  //	public $fakultative_felder = null;

  function BlumenbachOnline() {
    @ session_start();
    //		extract($GLOBALS);
    $this->workpath = $_SERVER['DOCUMENT_ROOT'] . "/typo3conf/ext/bolonline/mediafiles/";
    $this->workpath_rel = "typo3conf/ext/bolonline/mediafiles/";
    // put names for Table Columns from locallang_db to LOCAL_LANG
    $llFile = t3lib_extMgm :: extPath('bolonline') . 'locallang_db.xml';
    $nochdazu = t3lib_div :: readLLXMLfile($llFile, $GLOBALS['TSFE']->lang);
    $this->LOCAL_LANG = array_merge_recursive($this->LOCAL_LANG, $nochdazu);
    $this->getTableColumns(); // sets variable $this->tableColumns
    //		$this->fe_userdata = tx_sv_auth::getUser();
    $this->fe_userdata = $GLOBALS["TSFE"]->fe_user->user;
    require_once ('FileUploader.class.php');
    $this->FU = new FileUploader();

  }

  function __construct() {
    $this->BlumenbachOnline();
  }

  function autoTableGenerator($rowbez, $rowobj, $empty = FALSE, $show_deletionlink = TRUE) {
    $content = '<div style="border: 1px dashed gray;padding: 5px"><table>';
    if ($this->debug) {
      $content .= print_r($rowobj, 1);
    }
    //		$content .= $this->dump($rowbez, 1);
    if ($show_deletionlink) {
      if ($_SESSION['todo'] != 'insert' && $_SESSION['todo'] != "") {
        $content .= '<tr><td><br /><u title="delete whole part" alt="delete whole part" style="background-color:yellow;padding:5px;border: 1px solid gray;color:red" onClick="status=confirm(\'wirklich löschen?\');if(status){window.location=\'' . $_SERVER["REQUEST_URI"] . '&todo=delete&id=' . $rowobj["id"] . '&tablename=' . $this->tablename . '\';} else{}">Datensatz löschen</u><br clear="all" /><br /><br /><br /></td></tr>' . "\n";
      }
    }
    // get Mediafiles from Database:
    $mediafiles = $this->getMediafileURIs($rowobj['kerndaten_id'], $rowobj['block_nr']);

    if ($this->debug)
      $content .= 'getMediafileURIs(' . $rowobj['kerndaten_id'] . ', ' . $rowobj['block_nr'] . ')';

    foreach ($mediafiles as $nr => $data) {
      $id = $data['id'];
      $uri = $data['file_uri'];
      $sortorder = $data['sortorder'];
      $tabellenname = isset ($data['partI_id']) ? 'tx_bolonline_Mediafiles_PartI' : 'tx_bolonline_Mediafiles';
      $tabellenname = isset ($data['partII_id']) ? 'tx_bolonline_Mediafiles_PartII' : $tabellenname;
      $tabellenname = isset ($data['partIII_id']) ? 'tx_bolonline_Mediafiles_PartIII' : $tabellenname;
      $tabellenname = isset ($data['partIV_id']) ? 'tx_bolonline_Mediafiles_PartIV' : $tabellenname;
      //			print_r($tabellenname);die();
      //if (preg_match("|Part|i", $this->tabellenname)) $tabellenname = $this->tabellenname;
      $content .= "\n" . '<div id="bild_' . $id . '">
                          <img onClick="window.open(\'/' . $this->workpath_rel . $uri . '\')" src="/' . $this->workpath_rel . $uri . '" title="' . $uri . '" alt="' . $uri . '" style="width:80px;border:0px;float:left;border:1px solid gray;padding:1px;">&nbsp;&nbsp;
                          <span title="delete image no. ' . $id . ', ' . $tabellenname . '" alt="delete image no. ' . $id . ', ' . $tabellenname . '" onClick="del=confirm(\'are you shure, that you will delete the selected file?\');if(del){xajax_delImage(\'' . $id . '\',\'' . $tabellenname . '\');}else{/* do nothing */}" style="background-color:yellow;padding-left:10px;padding-right:10px;padding-top:1px;padding-bottom:1px;;border: 1px solid gray;color:red;text-decoration:underline;cursor:pointer;">';
      $content .= 'del</span>';
      $content .= '<br/><br/>
                  <span style="padding-left:10px;padding-right:10px;">
                      order: <input id="orderInput_'.$id.'" type="text" size="5" value="'.$sortorder.'"/>
                             <span onClick="console.log(document.getElementById(\'orderInput_'.$id.'\').value);
xajax_setImageOrder(\''.$tabellenname.'\','.$id.',document.getElementById(\'orderInput_'.$id.'\').value);"/ style="background-color:white;padding-left:10px;padding-right:10px;padding-top:1px;padding-bottom:1px;;border: 1px solid gray;color:blue;text-decoration:underline;cursor:pointer;">set</span>
                  </span>';
$content .=                  '</div><br clear="all"/>' . "\n";
    }

    $tabellenname = $this->tablename;

    // Einbau Zuordnungstabellen: #####################################################
    $content .= '<div id="association_pd_' . $rowobj['id'] . '">';
    $content .= $this->getAccociationMenueHtmlElements($rowobj['kerndaten_id'], $tabellenname, $rowobj['id']);
    $content .= '</div>';
    // Ende Einbau Zuordnungstabellen #####################################################

    if ($rowobj['kerndaten_id'] != 0) { //wenn Kerddaten-ID 0, keine Upload-Felder. Dann werden die Upload-Felder schon in der Ajax-Funktion setFields erzeugt.

      //Upload-Felder:
      for ($ii = 0; $ii < 5; $ii++) {
        $content .= '<input style="font-size:11px;width:200px;height:22px;margin:2px" type="file" name="fupload_' . $this->tablename . '_' . $rowobj['id'] . '_' . $rowobj['kerndaten_id'] . '_' . $ii . '_' . $rowobj['block_nr'] . '"><br />' . "\n";
      }
    }
    $bgcolor = "";

    if (preg_match("|Kerndaten|i", $this->tablename)) {
      $content .= '<tr><td style="padding: 4px">Kerndaten-ID:</td><td>' . $rowobj['kerndaten_id'] . "</td></tr>\n";
    } else {
      $content .= '<tr><td style="padding: 4px">Datensatz-ID:</td><td>' . $rowobj['id'] . "</td></tr>\n";
    }
    foreach ($rowbez as $key => $bez) {
      $label = parent :: pi_getLL($this->tablename . '.' . $bez);
      $label = $label == "" ? ucfirst($bez) : $label;

      if ($_SESSION['todo'] != 'insert') {
        if (!preg_match("|^[a-zA-Z]\d+|", $bez)) { //Kerndaten, nicht editierbar
          $trans = $rowobj[$bez];
          //					echo '<bR>$tabellenname:'.$tabellenname.' - $bez: '.$bez;die();
          if (preg_match("|Part|i", $tabellenname)) { //wenn Haupttabelle tx_bolonline_Kerndaten, dann ist datensatz_id=kerndaten_id!
            if ($bez == "id" || $bez == "datensatz_id") {
              $content .= '<input type="hidden" name="datensatz_id[]" value="' . $rowobj[$bez] . '">' . "\n";
              continue;
            }
          } else {
            if ($bez == "kerndaten_id") {
              $content .= '<input type="hidden" name="datensatz_id[]" value="' . $rowobj[$bez] . '">' . "\n";
              continue;
            }
          }
          if ($bez == "kerndaten_id") {
            if ($_SESSION['update_id']) {
              $trans = $_SESSION['update_id'];
              $rowobj[$bez] = $_SESSION['update_id'];
            }
          }
          if ($bez == "crdate")
            $trans = @ date("r", $rowobj[$bez]);
          if ($bez == "tstamp")
            $trans = @ date("r", $rowobj[$bez]);

          $bgcolor = ' style="padding: 4px"';
          $content .= '<tr><td' . $bgcolor . '>' . $label . ':</td><td><input type="hidden" name="' . $bez . '[]" value="' . $rowobj[$bez] . '">' . $trans . '</td></tr>' . "\n";

        }
      } else {
        if (!preg_match("|^[a-zA-Z]\d+|", $bez)) { //Kerndaten, nicht editierbar
          $content .= '<input type="hidden" name="' . $bez . '[]" value="' . $rowobj[$bez] . '">' . "\n";
        }
      }

      if (preg_match("|^[a-zA-Z]\d+|", $bez)) { //edtierbare Daten
        if ($empty)
          $rowobj[$bez] = ""; //fuer Neueintrag mit leeren Datenfeldern
        $bgcolor = ($key % 2) != 0 ? ' style="width:300px;background-color: lightgrey;padding: 4px"' : ' style="padding: 4px"';

        /* alle Tabellenspalten, welche, in Abhaengigkeit
				 * von der Hauptkategorie, nicht angezeigt werden 
				 * sollen, als hidden-Felder anlegen: */
        //$_REQUEST["hauptkategorie_id"]
        $zugeordnete_kategorie = $this->getMainCategories($_SESSION['update_id']);
        //				 $content .= print_r($zugeordnete_kategorie,1);
        $zugeordnete_kategorie = $zugeordnete_kategorie[0]['kategorie'];

        /* Spezialfelder und Felder an/aus */
        include ("bolonline.main.conf.php");
        //		$content = $jscript_special_inputfields;//javascript fuer Spezialfelder
        //				$content.= print_r($special_inputfields,1);
        //				$content .= print_r($zugeordnete_kategorie,1);
        //				$content.='<H1>'.$fakultative_felder[$zugeordnete_kategorie][$bez].'</H1>';
        if ((!isset ($fakultative_felder[$zugeordnete_kategorie][$bez])) || $fakultative_felder[$zugeordnete_kategorie][$bez] == "true") {
          //					$content.= print_r($special_inputfields[$bez],1);
          /*
					 *  ### Ersetzung der Werte durch Vordefinierte
					 * in $special_inputfields aus 
					 * include_once ("bolonline.main.conf.php") :
          */
          if ($special_inputfields[$bez] != "") {
            $content .= '<tr><td' . $bgcolor . '>' . $label . ':</td><td>' . $special_inputfields[$bez] . '</td></tr>' . "\n";
          } else {
            // normaler Wert, keine Ersetzung:
            //Berechnung der Textfeldgroessen. Autom. Anpassung:
            $height = round(strlen($rowobj[$bez]) / 65) * 16; //max(40,abs(strlen($rowobj[$bez])/40));
            //						$height = $height>90||strlen($rowobj[$bez])<1?90:$height;//=leere Felder mit Hoehe 90px
            $height = $height > 90 ? 90 : $height; //=leere Felder mit Hoehe 16px
            $height = $height < 20 ? $height +20 : $height;

            $content .= '<tr><td' . $bgcolor . '>' . $label . ':</td><td><textarea name="' . $bez . '[]" style="width:400px;height:' . $height . 'px;">' . $rowobj[$bez] . '</textarea></td></tr>' . "\n";
          }
        } else {
          $content .= '<input type="hidden" name="' . $bez . '[]" value="' . $rowobj[$bez] . '">';
          //				 	$content .= '<tr><td style="background-color:orange">' . $label . ':</td><td style="background-color:orange"><textarea name="' . $bez . '[]" style="width:400px;background-color:orange">' . $rowobj[$bez] . '</textarea></td></tr>' . "\n";
        }
      }
    }
    $content .= "</table></div>" . "\n";
    return $content;
  }

  public function getTableColumns() {
    $query = 'SHOW COLUMNS FROM ' . $this->tablename;
    $rowbez = array ();
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $rowbez[] = $row['Field'];
    }
    //				print_r($rowbez);
    $this->tableColumns = $rowbez;
    //		return $rowbez;
  }

  function delete($content_id, $tablename) {
    $part_label = $this->getPartLabel($tablename);
    $mediafiles_tablename = str_replace(ucfirst($part_label), "", $tablename) . "Mediafiles_" . ucfirst($part_label);
    //		echo 'deleteMediafile('.$content_id.', '.$mediafiles_tablename.')'; die();

    if (preg_match("|Part|i", $tablename)) { //Part-Tabelle
      $query = 'DELETE FROM ' . $tablename . ' WHERE id= ' . $content_id;
      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);

      if (preg_match("#(PartI)#i", $tablename)) { //Part-Tabelle
          $query = 'DELETE FROM tx_bolonline_PartIII_associations WHERE partI_id=' . $content_id;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
          $query = 'DELETE FROM tx_bolonline_PartIV_associations WHERE partI_id=' . $content_id;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      }
      if (preg_match("#(PartII)#i", $tablename)) { //Part-Tabelle
          $query = 'DELETE FROM tx_bolonline_PartIII_associations WHERE partII_id=' . $content_id;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
          $query = 'DELETE FROM tx_bolonline_PartIV_associations WHERE partII_id=' . $content_id;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      }
      if (preg_match("#(PartIII)#i", $tablename)) { //Part-Tabelle
          $query = 'DELETE FROM tx_bolonline_PartIII_associations WHERE partIII_id=' . $content_id;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
          $query = 'DELETE FROM tx_bolonline_PartIV_associations WHERE partIII_id=' . $content_id;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      }
      
      if (preg_match("#(PartIV)#i", $tablename)) { //Part-Tabelle
          $query = 'DELETE FROM tx_bolonline_PartIII_associations WHERE partIV_id=' . $content_id;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
          $query = 'DELETE FROM tx_bolonline_PartIV_associations WHERE partIV_id=' . $content_id;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      }
      
   
    } else { //Kerndaten-Tabelle
      $query = 'DELETE FROM ' . $tablename . ' WHERE kerndaten_id= ' . $content_id;
    }
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    //		echo $query;die();
    

    return $this->deleteMediafiles($content_id, $mediafiles_tablename);
  }

  function insert($values_as_array, $kerndaten_id = 0) { //$kerndaten_id=0 fuer Neueintrag in tx_bolonline_kerndaten, danach kerndaten-id in sub-tabellen eintragen!
    //		echo 'sizeOf($values_as_array):' . sizeOf($values_as_array);
    //		if ($this->debug) $this->dump($values_as_array);

    // ermittle die max. kerndaten_id:
    $maxid = 0;
    #if (!preg_match("|Part|i", $this->tablename)) {
    $query = 'SELECT MAX(kerndaten_id)+1 AS maxid FROM tx_bolonline_Kerndaten';
    #} else {
    #	$query = 'SELECT MAX(id)+1 AS maxid FROM ' . $this->tablename;
    #}
    //		echo $query . "<br>\n";
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    //		echo 'MAXID:'.$row['maxid'];
    $maxid = $row['maxid'];
    if ($maxid == "")
      $maxid = 1;
    // Ende ermittle die max. kerndaten_id.

    // ermittle die max. id von den Tabellen tx_bolonline_PartI-IV:
    $max_part_id = 1;
    //		echo '$this->tablename:'.$this->tablename;
    if (preg_match("|Part|i", $this->tablename)) {
      $max_part_id = 0;
      $query = 'SELECT MAX(id)+1 AS maxid FROM ' . $this->tablename; // . ' WHERE kerndaten_id=' . $maxid;
      //		echo $query;
      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
      @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
      //		echo 'MAXID:'.$row['maxid'];
      $max_part_id = $row['maxid'];
      if ($max_part_id == "")
        $max_part_id = 1;
      //			echo $query.'$max_part_id:'.$max_part_id;
    }
    // Ende ermittle die max. kerndaten_id.

    //		echo 'sizeOf($this->tableColumns):' . sizeOf($this->tableColumns);
    if (sizeOf($values_as_array) < sizeOf($this->tableColumns))
      return "";
    $query = 'INSERT INTO ' . $this->tablename . ' VALUES(';
    //		print_r($tcols);
    $queryadd = "";
    foreach ($this->tableColumns as $index => $bez) {

      $values_as_array['kerndaten_id'] = $maxid;
      $values_as_array['id'] = $max_part_id; //erster Eintrag in DB fuer diese kerndaten_id
      $values_as_array['block_nr'] = 0; //erster Block
      // Werte mit den Userdaten des aktuell bearbeitenden users belegen:
      if ($bez == "uid")
        $values_as_array[$bez] = $this->fe_userdata['uid'];

      // wenn Kerndaten bereits eingetragen sind (ist dies nicht der eintrag in tabelle tx_bolonline_Kerndaten), uebergebene kerndaten_id eintragen:
      if ($kerndaten_id != 0 && $bez == "kerndaten_id")
        $values_as_array[$bez] = $kerndaten_id;

      if ($bez == "pid")
        $values_as_array[$bez] = $this->fe_userdata['pid'];
      if ($bez == "cruser_id")
        $values_as_array[$bez] = $this->fe_userdata['cruser_id'];
      if ($bez == "crdate")
        $values_as_array[$bez] = time(localtime());
      if ($bez == "tstamp")
        $values_as_array[$bez] = time(localtime()); //Zeitstempel des Erstellens des Datensatzes. Wird beim Update nicht veraendert!

      //			echo '<br>$values_as_array[' . $bez . ']: ' . $values_as_array[$bez];
      $wert = $values_as_array[$bez];
      if (is_array($wert)) {
        $wert = $wert[0];
      }
      $queryadd .= '"' . mysql_escape_string($wert) . '"';
      if ($index < sizeOf($this->tableColumns) - 1)
        $queryadd .= ", ";
    }
    $query .= $queryadd . ")";

    if ($this->debug)
      echo $query . "<br>\n";
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

    return $maxid;
  }

  function update($values_as_array, $kerndaten_id = 0) { //$kerndaten_id=0 fuer Neueintrag in tx_bolonline_kerndaten, danach kerndaten-id in sub-tabellen eintragen!
    $a_datensatz_id = $_REQUEST['datensatz_id'];
    if (sizeOf($values_as_array) < 1) {
      return "";
    }
    //$this->dump($values_as_array);
    $tcols_label = array_values($this->tableColumns);
    $query = "";

    $queryadd = "";
    $index = 0;
    $anz_spalten = sizeOf($values_as_array);
    $anz_bloecke = 0;
    $insert = 1;
    // ermitteln der anz abgesendeter bloecke aus formular (ajax-add)
    //				echo "<xmp style='color:red'>" . print_r($values_as_array, 1) . "</xmp>";
    $tcolcounter = 0;
    $significant_values = array ();
    //$this->dump($values_as_array);
    foreach ($values_as_array as $bez => $a_values) {
      if (preg_match("|^[a-zA-Z]\d+|", $bez)) { //Nur DB-relevante Formularwerte benutzen!
        //				print 'bez:<span style="color:blue">'.$bez.'</span> <span style="color:red">$tcols_label: '.print_r($tcols_label,1).'</span>';
        $tcolcounter++;
        if (!in_array($bez, $tcols_label)) { //kein Matching der Datenspalten!
          //					echo "falsche Spaltenbezeichner!";
          $insert = 0;
          break;
        } else {

        }
      }
      if (is_array($a_values))
        $significant_values[$bez] = $a_values; //z.B. keine fupload-Felder berücksichtigen
    }
    if ($tcolcounter < 1)
      $insert = 0; // Anz. Spalten stimmt nicht

    foreach ($values_as_array as $bez => $a_values) {
      if (is_array($a_values)) {
        $anz_bloecke = sizeOf($a_values);
        break;
      }
    }
    //				$this->dump($significant_values);
    //		echo "Anzahl Blöcke: " . $anz_bloecke;
    $id = 0;

    if ($insert) {
      for ($i = 0; $i < $anz_bloecke; $i++) {
        $datensatz_id = $significant_values["datensatz_id"][$i];
        if ($datensatz_id == "")
          $neueintrag = 1;
        if (!preg_match("|Part|i", $this->tablename)) {
          $datensatz_id = $significant_values["kerndaten_id"][$i];
          if (!is_numeric($datensatz_id))
            $datensatz_id = $significant_values["datensatz_id"][$i];
        }
        $index = 0;
        $queryadd = "";
        $updatequeryadd = "";
        $block_nr = $i;
        //				$query = 'INSERT INTO ' . $this->tablename . ' VALUES('.$significant_values["datensatz_id"][0].',' . $kerndaten_id . ',' /*. ','.$block_nr.','*/;
        $query = 'INSERT INTO ' . $this->tablename . ' VALUES(';
        if ($this->debug)
          $this->dump($significant_values);
        foreach ($significant_values as $bez => $a_values) {
          $index++;
          $standardprozedur = 1;
          $neuerDatensatz = 0;
          // Werte mit den Userdaten des aktuell bearbeitenden users belegen:
          if ($bez == "uid")
            $significant_values[$bez][$i] = $this->fe_userdata['uid'];

          // wenn Kerndaten bereits in post uebergeben, nichts machen
          if ($kerndaten_id == 0 && $bez == "kerndaten_id") {
            $significant_values[$bez][$i] = $kerndaten_id;
          }

          if ($bez == "pid") {
            $values_as_array[$bez][$i] = $this->fe_userdata['pid'];
          }
          if ($bez == "cruser_id") {
            $significant_values[$bez][$i] = $this->fe_userdata['cruser_id'];
          }
          if ($bez == "crdate") {
            $significant_values[$bez][$i] = time(localtime());
          }
          if ($bez == "tstamp") {
            $significant_values[$bez][$i] = time(localtime()); //Zeitstempel des Erstellens des Datensatzes. Wird beim Update nicht veraendert!
          }
          if ($bez == "kerndaten_id") {
            $queryadd .= $significant_values['kerndaten_id'][$i];
            $updatequeryadd .= 'kerndaten_id=' . $significant_values['kerndaten_id'][$i];
            $standardprozedur = 0;

          }

          if ($bez == "id") { //Kerndaten-Tablle
            $id = $significant_values['id'][$i];
            if ($id == "") {
              $queryadd .= "NULL";
              $standardprozedur = 0;
              $neuerDatensatz = 1;
            } else {
              // do nothing!
            }
          }

          $mediaid = $datensatz_id;

          if ($bez == "datensatz_id") {
            if (preg_match("|Part|i", $this->tablename)) { //Part-Tabelle, nicht die Kerndaten-Tabelle!
              if (is_numeric($datensatz_id)) {
                $queryadd .= $datensatz_id;
                $mediaid = $datensatz_id;
                $updatequeryadd .= 'kerndaten_id=' . $datensatz_id;
              } else { // neuer Block ohne bestehende id
                $queryadd .= 'NULL'; //$_SESSION['update_id'];
                $mediaid = "NULL";
                $neuerDatensatz = 1;
                //							$updatequeryadd .= 'id=' . $datensatz_id;
              }
            } else {
              if (is_numeric($datensatz_id)) {
                $queryadd .= $datensatz_id;
                $updatequeryadd .= 'kerndaten_id=' . $datensatz_id;
              } else { // neuer Block ohne bestehende id
                $queryadd .= "NULL";
                //							$updatequeryadd .= 'id=' . $datensatz_id;
              }
            }
            $standardprozedur = 0;
          }
          if ($bez == "block_nr") { //nur tinyint!
            $queryadd .= $block_nr;
            $updatequeryadd .= 'block_nr=' . $block_nr;
            $standardprozedur = 0;
          }
          if ($standardprozedur) {
            $queryadd .= '"' . mysql_escape_string($significant_values[$bez][$i]) . '"';
            $updatequeryadd .= $bez . '="' . mysql_escape_string($significant_values[$bez][$i]) . '"';
          }

          if ($index < sizeOf($significant_values)) {
            $queryadd .= ", ";
            $updatequeryadd .= ", ";
          }
        }

        if (is_numeric($datensatz_id) && (!$neuerDatensatz)) {
          $query .= $queryadd . ") ON DUPLICATE KEY UPDATE " . $updatequeryadd;
        } else {
          $query .= $queryadd . ")";
        }
        if ($this->debug)
          echo $query . "<br />";
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
        @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

        $query = "SELECT MAX(id) AS maxid FROM " . $this->tablename;
        //												echo '<span style="color:green">' . $query . "</span><br />";
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $maxid = $row['maxid'];
        @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

        //$significant_values['id'][$i]

        if ($neuerDatensatz) {
          $this->processMediaFileUpload($_SESSION['update_id'], $block_nr, $maxid);
        } else { // wenn id bgelegt, dann kerndaten-Tabelle!
          $id = $id == "" ? $datensatz_id : $id;
          $this->processMediaFileUpload($_SESSION['update_id'], $block_nr, $mediaid);
        }
        $query = "";
      }
    }
  }

  function _____sich____insertDelete($values_as_array, $kerndaten_id = 0) { //$kerndaten_id=0 fuer Neueintrag in tx_bolonline_kerndaten, danach kerndaten-id in sub-tabellen eintragen!
    $a_datensatz_id = $_REQUEST['datensatz_id'];
    //		echo 'sizeOf($values_as_array):' . sizeOf($values_as_array);
    //		echo 'sizeOf($this->tableColumns):' . sizeOf($this->tableColumns);

    // wichtig, beim Parameterlosen Aufruf wird sonst alle geloescht!
    //		$anz_signifikanter_spalten = sizeOf($this->tableColumns) - 2; // id und kerndaten_id werden uebergeben, bzw. angelegt (Null)
    //		if (sizeOf($values_as_array) < $anz_signifikanter_spalten){
    //			echo 'sizeOf($values_as_array) < $anz_signifikanter_spalten:'.sizeOf($values_as_array).' < '.$anz_signifikanter_spalten;
    //			return "";
    //		}
    if (sizeOf($values_as_array) < 1) {
      //			echo 'sizeOf($values_as_array) < $anz_signifikanter_spalten:' . sizeOf($values_as_array) . ' < ' . $anz_signifikanter_spalten;
      return "";
    }
    //$this->dump($values_as_array);
    //Datensatz id wird nicht übergben! prüfen!!!
    $tcols_label = array_values($this->tableColumns);
    $query = "";

    $queryadd = "";
    $index = 0;
    $anz_spalten = sizeOf($values_as_array);
    $anz_bloecke = 0;
    $delete = 1;
    // ermitteln der anz abgesendeter bloecke aus formular (ajax-add)
    //				echo "<xmp style='color:red'>" . print_r($values_as_array, 1) . "</xmp>";
    $tcolcounter = 0;
    $significant_values = array ();
    //$this->dump($values_as_array);
    foreach ($values_as_array as $bez => $a_values) {

      //						echo "<br><span style='color:red'>$bez => ".print_r($a_values,1)."</span>";
      //			print 'bez:<span style="color:blue">'.$bez.'</span> <span style="color:red">$values_as_array: '.print_r($values_as_array,1).'</span>';
      if (preg_match("|^[a-zA-Z]\d+|", $bez)) { //Nur DB-relevante Formularwerte benutzen!
        //				print 'bez:<span style="color:blue">'.$bez.'</span> <span style="color:red">$tcols_label: '.print_r($tcols_label,1).'</span>';
        $tcolcounter++;
        if (!in_array($bez, $tcols_label)) { //kein Matching der Datenspalten!
          //					echo "falsche Spaltenbezeichner!";
          $delete = 0;
          break;
        } else {

        }
      }
      if (is_array($a_values))
        $significant_values[$bez] = $a_values; //z.B. keine fupload-Felder berücksichtigen
    }
    if ($tcolcounter < 1)
      $delete = 0; // Anz. Spalten stimmt nicht

    //		echo '$delete:'.$delete;
    //echo "<xmp>".print_r($values_as_array, 1)."</xml>";
    foreach ($values_as_array as $bez => $a_values) {
      if (is_array($a_values)) {
        $anz_bloecke = sizeOf($a_values);
        break;
      }
    }
    //		echo "Anzahl Blöcke: " . $anz_bloecke;

    if ($delete) {
      //			echo '$significant_values:<xmp>' . print_r($significant_values, 1) . '</xmp>';
      // alles Löschen und danach neu anlegen
      for ($i = 0; $i < $anz_bloecke; $i++) {
        $datensatz_id = $significant_values["datensatz_id"][0];
        if (!preg_match("|Part|i", $this->tablename)) {
          $datensatz_id = $significant_values["kerndaten_id"][0];
          if (!is_numeric($datensatz_id))
            $datensatz_id = $significant_values["datensatz_id"][0];
        }
        $index = 0;
        $queryadd = "";
        $updatequeryadd = "";
        $block_nr = $i;
        //				$query = 'INSERT INTO ' . $this->tablename . ' VALUES('.$significant_values["datensatz_id"][0].',' . $kerndaten_id . ',' /*. ','.$block_nr.','*/;
        $query = 'INSERT INTO ' . $this->tablename . ' VALUES(';
        foreach ($significant_values as $bez => $a_values) {
          $index++;

          // Werte mit den Userdaten des aktuell bearbeitenden users belegen:
          if ($bez == "uid")
            $significant_values[$bez][$i] = $this->fe_userdata['uid'];

          // wenn Kerndaten bereits in post uebergeben, nichts machen
          if ($kerndaten_id == 0 && $bez == "kerndaten_id")
            $significant_values[$bez][$i] = $kerndaten_id;

          if ($bez == "pid")
            $values_as_array[$bez][$i] = $this->fe_userdata['pid'];
          if ($bez == "cruser_id")
            $significant_values[$bez][$i] = $this->fe_userdata['cruser_id'];
          if ($bez == "crdate")
            $significant_values[$bez][$i] = time(localtime());
          if ($bez == "tstamp")
            $significant_values[$bez][$i] = time(localtime()); //Zeitstempel des Erstellens des Datensatzes. Wird beim Update nicht veraendert!
          if ($bez == "kerndaten_id") {
            $queryadd .= $significant_values[$bez][$i];
            $updatequeryadd .= $bez . '=' . $significant_values[$bez][$i];
          }
          elseif ($bez == "datensatz_id") {
            if (preg_match("|Part|i", $this->tablename)) {
              if (is_numeric($datensatz_id)) {
                $queryadd .= $datensatz_id;
                $updatequeryadd .= 'id=' . $datensatz_id;
              } else { // neuer Block ohne bestehende id
                $queryadd .= "NULL";
                //							$updatequeryadd .= 'id=' . $datensatz_id;
              }
            } else {
              if (is_numeric($datensatz_id)) {
                $queryadd .= $datensatz_id;
                $updatequeryadd .= 'kerndaten_id=' . $datensatz_id;
              } else { // neuer Block ohne bestehende id
                $queryadd .= "NULL";
                //							$updatequeryadd .= 'id=' . $datensatz_id;
              }
            }
          }
          elseif ($bez == "block_nr") { //nur tinyint!
            $queryadd .= $block_nr;
            $updatequeryadd .= 'block_nr=' . $block_nr;
          } else {
            $queryadd .= '"' . mysql_escape_string($significant_values[$bez][$i]) . '"';
            $updatequeryadd .= $bez . '="' . mysql_escape_string($significant_values[$bez][$i]) . '"';
          }

          if ($index < sizeOf($significant_values)) {
            $queryadd .= ", ";
            $updatequeryadd .= ", ";
          }
        }

        //				echo $queryadd . ") ON DUPLICATE KEY UPDATE id=".$datensatz_id.',kerndaten_id='.$kerndaten_id.','.$updatequeryadd;
        if (is_numeric($datensatz_id)) {
          $query .= $queryadd . ") ON DUPLICATE KEY UPDATE " . $updatequeryadd;
        } else {
          $query .= $queryadd . ")";
        }
        if ($this->debug)
          echo $query . "<br />";
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
        @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

        $query = "SELECT MAX(id) AS maxid FROM " . $this->tablename;
        //								echo '<span style="color:green">' . $query . "</span><br />";
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $maxid = $row['maxid'];
        @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

        $this->processMediaFileUpload($_SESSION['update_id'], $block_nr, $maxid);

        $query = "";

      }

    }
  }

  function getBlocksize($kerndaten_id) {
    if ($kerndaten_id != 0) {
      $query = 'SELECT COUNT(*) AS ANZ FROM  ' . $this->tablename . ' WHERE kerndaten_id=' . $kerndaten_id;
      //			echo '<h1>'.$query.'</h1>';
      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
      @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
      //			echo $query;
      $anz = $row;
      return $row['ANZ'];
    } else
      return 0;
  }

  function show($kerndaten_id = 0) {
    $res = null;
    $anz = 0;

    if ($kerndaten_id != 0) {
      $query = 'SELECT * FROM  ' . $this->tablename . ' WHERE kerndaten_id=' . $kerndaten_id;
      if (preg_match("|Part|i", $this->tablename)) {
        $query .= ' ORDER BY block_nr ASC, id ASC';
      }
      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

      //			ermittle die Anzahl der vorhandenen Blöcke:
      $res2 = $res;
      $anz_bl = 1;
      while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
        $anz_bl++;
      }
      $GLOBALS['TYPO3_DB']->sql_free_result($res);

      //$content .= '$anz_bl:'."$anz_bl";
      if ($this->debug)
        $content .= $query;
      $anz = sizeOf($row) - 1;
    } else {
      //			$query = 'SHOW COLUMNS FROM '.$this->tablename;
    }
    //$content .= '<br>$kerndaten_id: '.$kerndaten_id.'<br>';
    //$content .= '<br>$anz: '.$anz.'<br>';
    //		$content .= '<br>' . $query . '<br>';

    if ($kerndaten_id != 0 && $anz > 0) {
      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      $counter = 0;
      while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        //							$content .= $row['id'] . ") " . $row['username'] . '<br />';
        //							echo $query;

        //Sprungmarke setzen am Beginn des letzten Datensatzes:
        $unique_id = md5($this->tablename);
        if ($anz_bl == 1 && $counter == 0)
          $content .= "\n" . '<a name="' . $unique_id . '"></a>';
        else
        if ($counter +1 == $anz_bl) {
          $content .= "\n" . '<a name="' . $unique_id . '"></a>';
        }

        $rowbez = array_keys($row);
        $show_deletionlink = $counter > 0 ? TRUE : FALSE;
        $content .= $this->autoTableGenerator($rowbez, $row, FALSE, $show_deletionlink);
        $counter++;

      }
      @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    } else { //Neueintrag
      //				echo "Tabellenfelder:".print_r($this->tableColumns,1);
      $werte = array ();

      // belegung von leeren default-werten fuer Neueintrag:
      foreach ($this->tableColumns as $nr => $rowbez) {
        $werte[$rowbez] = "";
      }

      $content .= $this->autoTableGenerator($this->tableColumns, $werte, TRUE, FALSE); //fuer Neueintrag leere Felder erzeugen

    }
    //				print_r($content);
    return $content;
  }

  function getUpdateTeaser($hauptkategorie_id = "") {
    if ($hauptkategorie_id != "") {
      // Sortierreihenfolge: A2(alphabetisch)+A3+A0
      $query = 'SELECT K.kerndaten_id,CONCAT(K.a2,"_",K.a3,"_",K.a0) AS "a0"  FROM ' . $this->tablename . ' K, tx_bolonline_HauptkategorieZuordnung HK WHERE HK.kerndaten_id=K.kerndaten_id AND HK.kategorie_id=' . $hauptkategorie_id . ' ORDER BY K.a2,CAST(K.a3 as decimal(5,5)), CAST(K.a3 as UNSIGNED),K.a3,K.a0';
    } else {
      $query = 'SELECT K.kerndaten_id,CONCAT(K.a2,"_",K.a3,"_",K.a0) AS "a0" FROM ' . $this->tablename . " K, tx_bolonline_HauptkategorieZuordnung HKZ, tx_bolonline_Hauptkategorie HK WHERE HK.id=HKZ.kategorie_id AND HKZ.kerndaten_id=K.kerndaten_id ORDER BY HK.kategorie,K.a2,CAST(K.a3 as decimal(5,5)), CAST(K.a3 as UNSIGNED),K.a3,K.a0";
    }
    //		echo $query;
    $content = array ();
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    $content = array ();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $rowbez = array_keys($row);
      //			print_r($row);
      $content[$row['kerndaten_id']] = $row['a0'];

    }
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

    // Datensaetze ohne Hauptkategorie-Zuordnung:
    $query = 'SELECT K.kerndaten_id,CONCAT(K.a2,"_",K.a3,"_",K.a0) AS "a0" FROM ' . $this->tablename . " K WHERE K.kerndaten_id NOT IN (SELECT kerndaten_id FROM tx_bolonline_HauptkategorieZuordnung) ORDER BY K.a2,CAST(K.a3 AS UNSIGNED),K.a0";
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $rowbez = array_keys($row);
      //			print_r($row);
      $content[$row['kerndaten_id']] = $row['a0'];

    }
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

    return $content;
  }

  //	function showAll($limit) {
  //		$query = 'SELECT * FROM ' . $this->tablename . ' ORDER BY kerndaten_id ASC LIMIT ' . $limit;
  //		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
  //		$content = "";
  //		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
  //			//			$content .= $row['id'] . ") " . $row['username'] . '<br />';
  //			$rowbez = array_keys($row);
  //			$content .= $this->autoTableGenerator($rowbez, $row);
  //
  //		}
  //		@ $GLOBALS['TYPO3_DB']->sql_free_result($res);
  //		return $content;
  //	}

  function processMediaFileUpload($kerndaten_id, $block_nr, $datensatz_id) {
    $contenttable_id = $datensatz_id;
    /*
		 * [f0] => Array ( 
		 * [name] => rte_0.0.11.t3x 
		 * [type] => application/octet-stream 
		 * [tmp_name] => /tmp/phpFAaDlY 
		 * [error] => 0 
		 * [size] => 123058 ) 
		 * 
		 * Bezeichner fuer Upload-Felder Bsp.:
		 * fupload_Datenbanktabelle_id_kerndatenid_fortlaufendenr_Blocknummer, nur bei neuen Feldern
		 * fupload_tx_bolonline_PartIII_105_1_4_0 // bereits in DB eingetragen, id=105
		 * fupload_tx_bolonline_PartIII_0_1_0_7_1 // neu; noch nicht in DB eingetragen, id=0
    */
    $tablename = $this->tablename;
    $content = "";
    //		$tablename = $tablename == "" ? "tx_bolonline_Mediafiles" : $tablename;
    $filename_new = "";

    //		$this->FU->MediaUploadConverter();
    //		echo 'Mediafiles: <xmp style="color:green">'.print_r($this->FU->UPLOADFILE,1	).'</xmp>';
    //$this->dump($this->FU->UPLOADFILE);
    foreach ($this->FU->UPLOADFILE as $fuploadlabel => $a_fileattribs) {
      //			echo "FILE: $fuploadlabel => " . print_r($a_fileattribs, 1) . "<br>";

      if ($a_fileattribs['error'] == 0) {
        //				$block_nr = 9;
        list ($fupload, $tx, $bolonline, $part, $part_id, $kerndaten_id, $fortlaufendenr, $b_nr) = preg_split("|_|", $fuploadlabel);
        if ($b_nr == $block_nr) { // nur Dateien von dem entsprechenden block hochladen!
          $this->FU->setUploadFieldLabelname($fuploadlabel);
          $content .= $this->FU->getUploadFileMimeType();
          $filename_new = $this->FU->cleanUploadFilename($fuploadlabel . '_' . $a_fileattribs['name']);
          move_uploaded_file($a_fileattribs['tmp_name'], $this->workpath . $filename_new);
          if ($part != "Kerndaten")
            $tablename = $tx . '_' . $bolonline . '_Mediafiles_' . $part;
          else
            $tablename = $tx . '_' . $bolonline . '_Mediafiles';

          $content .= $this->insertMediafiles(array (
                  $filename_new
                  ), $kerndaten_id, $tablename, $contenttable_id, $block_nr, $datensatz_id);
        }
      }
    }
    return $content;
  }

  function getPartLabel($tablename) {
    $partlabel = "";

    if (preg_match("|Mediafiles|i", $tablename)) {
      //			$partlabel = lcfirst(str_replace("tx_bolonline_Mediafiles_", "", $tablename));
      $tmp = str_replace("tx_bolonline_Mediafiles_", "", $tablename);
      $partlabel = strtolower($tmp[0]) . substr($tmp, 1, strlen($tmp));
    } else {
      //			$partlabel = lcfirst(str_replace("tx_bolonline_", "", $tablename));
      $tmp = str_replace("tx_bolonline_", "", $tablename);
      $partlabel = strtolower($tmp[0]) . substr($tmp, 1, strlen($tmp));
    }
    return $partlabel;
  }

  function insertMediafiles($a_uris, $kerndaten_id, $tablename, $contenttable_id, $block_nr, $datensatz_id) {
    $tablename = $tablename == "" ? "tx_bolonline_Mediafiles" : $tablename;
    $queries = "";
    foreach ($a_uris as $index => $uri) {
      // Hier wird nur eingetragen, kein update vorgesehen!
      $query = 'INSERT INTO  ' . $tablename . ' VALUES(NULL,' . $contenttable_id . ',"' . mysql_escape_string($uri) . '",' . $block_nr . ',100)';
      $queries .= $query;
      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);

    }
    if ($this->debug)
      echo '<span style="color:red">' . $queries . '</span><br />';

    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    return $queries;
  }

  function dump($object, $flag = 0) {
    if (!$flag)
      echo '<xmp style="color:red;font-size:10px">' . print_r($object, 1) . '</xmp>';
    else
      return '<xmp style="color:red;font-size:10px">' . print_r($object, 1) . '</xmp>';
  }

  function getMediafileURIs($kerndaten_id, $block_nr) {
    $tablename = $this->tablename;
    $part_bezeichner = $this->getPartLabel($tablename); //str_replace("tx_bolonline_", "", $tablename);

    if ($tablename == "tx_bolonline_Kerndaten") {
      $query = 'SELECT * FROM tx_bolonline_Mediafiles WHERE kerndaten_id=' . $kerndaten_id . ' ORDER BY sortorder, id';

    } else {

      $tablename = "tx_bolonline_Mediafiles_" . ucfirst($part_bezeichner);
      $query = 'SELECT mediafiles.* FROM ' . $tablename . ' mediafiles,' . str_replace("_Mediafiles", "", $tablename) . ' content WHERE content.block_nr=mediafiles.block_nr AND mediafiles.block_nr=' . $block_nr . ' AND content.kerndaten_id=' . $kerndaten_id . ' AND mediafiles.' . $part_bezeichner . '_id=content.id ORDER BY sortorder, id';
    }
    //		echo $query;die();
    $content = array ();
    //		echo  '<h1>$kerndaten_id, $partxxx_id'."$kerndaten_id, $partxxx_id".$query.'</h1>';die();
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    //		$content = array ();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $rowbez = array_keys($row);
      //					print_r($row);
      $content[] = $row;

    }
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    //		//hole bestehende Bilder aus der DB anhand der block_nummer
    //		$query = 'SELECT mediafiles.file_uri,mediafiles.id FROM '.$tablename.' mediafiles,'.str_replace("_Mediafiles","",$tablename).' content WHERE content.block_nr='.$block_nr.' AND content.kerndaten_id='.$kerndaten_id.' AND mediafiles.'.lcfirst($part_bezeichner).'_id=content.id';
    //
    //		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    //		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
    //			$rowbez = array_keys($row);
    //			//					print_r($row);
    //			$content[] = $row;
    //
    //		}
    //		$GLOBALS['TYPO3_DB']->sql_free_result($res);

    //		$this->dump($content);echo $query;die();
    return $content;
  }

  function getAssociationIDs($kerndaten_id, $tablename, $spaltenbez, $block_id = 0) {
    $query = 'SELECT id,' . $spaltenbez . ' FROM ' . $tablename . ' WHERE kerndaten_id=' . $kerndaten_id;
    print $query;
    $content = array ();
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      //			print_r($row);
      $content[] = $row;

    }
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    //		print_r($content);
    return $content;
  }

  function getAssociatedIDs($kerndaten_id, $tablename, $block_id) {
    if ($tablename != "tx_bolonline_PartIII" && $tablename != "tx_bolonline_PartVI") {
      //			return 0;
    }
    $partII_ids = array ();
    $partI_ids = array ();
    //		$erg = array();

    if ($tablename == "tx_bolonline_PartIII") {
      $query = 'SELECT partII_id AS id FROM tx_bolonline_PartIII_associations WHERE kerndaten_id=' .
      $kerndaten_id . ' AND  partII_id<>0 AND partIII_id=' . $block_id;

      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        //			print_r($row);
        $partII_ids[] = $row['id'];
      }
      @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

      /*---- neue Zuordnung in gelb. Pulldown-menü auf rot -------*/
      //ALTER TABLE `tx_bolonline_PartIII_associations` ADD `partI_id` BIGINT( 20 ) UNSIGNED NOT NULL
      $query = 'SELECT partI_id AS id FROM tx_bolonline_PartIII_associations WHERE kerndaten_id=' .
      $kerndaten_id . ' AND  partI_id<>0 AND partIII_id=' . $block_id;

      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        //			print_r($row);
        $partI_ids[] = $row['id'];

      }
      @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

      return array (
              "partI_ids" => $partI_ids,
              "partII_ids" => $partII_ids
      );
      /*--------------------------------------------------------*/

      //			return array (
      //				"partII_ids" => $partII_ids
      //			);
    }

    if ($tablename == "tx_bolonline_PartIV") {
      $query = 'SELECT partII_id AS id FROM tx_bolonline_PartIV_associations WHERE kerndaten_id=' . $kerndaten_id . '
        AND  partII_id<>0 AND partIV_id=' . $block_id;

      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        //				print_r($row);
        $partII_ids = $row['id'];

      }
      @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

      $query = 'SELECT partI_id AS id FROM tx_bolonline_PartIV_associations WHERE kerndaten_id=' . $kerndaten_id . '
        AND  partI_id<>0 AND partIV_id=' . $block_id;

      $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        //			print_r($row);
        $partI_ids[] = $row['id'];

      }
      @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

      return array (
              "partI_ids" => $partI_ids,
              "partII_ids" => $partII_ids
      );
    }
  }

  function setAssociatedIDs($zuordnungstabellenname, $element_value, $kerndaten_id, $block_bez, $block_id, $delete = true) {
    $query = "";
    $queries = "";
    //table=tx_bolonline_PartIV_associations
    if ($zuordnungstabellenname == "tx_bolonline_PartIV_associations") {
      if (preg_match("#.+PartI$#", $block_bez)) {
        $query = 'DELETE FROM ' . $zuordnungstabellenname . ' WHERE kerndaten_id=' . $kerndaten_id . ' AND partI_id<>0
          AND partIV_id=' . $block_id;
         if ($delete) {
          $queries .= $query;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
        }
        $query = 'INSERT INTO ' . $zuordnungstabellenname . ' VALUES(NULL,' . $kerndaten_id . ',' . $block_id . ',0,' .
        $element_value . ')';

        $queries .= $query;
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      }
      if (preg_match("#.+PartII$#", $block_bez)) {
        $query = 'DELETE FROM ' . $zuordnungstabellenname . ' WHERE kerndaten_id=' . $kerndaten_id . ' AND partII_id<>0
          AND partIV_id=' . $block_id;
        $queries .= $query;
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
        $query = 'INSERT INTO ' . $zuordnungstabellenname . ' VALUES(NULL,' . $kerndaten_id . ',' . $block_id . ',' .
        $element_value . ',0)';
        $queries .= $query;
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      }
    }

    if ($zuordnungstabellenname == "tx_bolonline_PartIII_associations") {
      if (preg_match("#.+PartII$#", $block_bez)) {
        $query = 'DELETE FROM ' . $zuordnungstabellenname . ' WHERE kerndaten_id=' . $kerndaten_id . ' AND partII_id<>0
          AND  partIII_id=' . $block_id;
        if ($delete) {
          $queries .= $query;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
        }

        $query = 'INSERT INTO ' . $zuordnungstabellenname . ' VALUES(NULL,' . $kerndaten_id . ',' . $block_id . ',' .
        $element_value . ',0)';
        $queries .= $query;
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      }

      /*-------------------- neue rote zuordnung im gelben block ---------------*/
      if (preg_match("#.+PartI$#", $block_bez)) {
        $query = 'DELETE FROM ' . $zuordnungstabellenname . ' WHERE kerndaten_id=' . $kerndaten_id . ' AND partI_id<>0
          AND  partIII_id=' . $block_id;
         if ($delete) {
          $queries .= $query;
          $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
        }
        $query = 'INSERT INTO ' . $zuordnungstabellenname . ' VALUES(NULL,' . $kerndaten_id . ',' . $block_id . ',0,' .
        $element_value . ')';
        $queries .= $query;
        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
      }
      /*--------------------------------------------------------------------------*/

     // $queries .= $query;
      //$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    }

    //@ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    //table=tx_bolonline_PartIII_associations
    //
    //		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    //		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
    //			//			print_r($row);
    //			$partI_ids = $row;
    //
    //		}
    //		@ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    return $queries;
  }

  function getAccociationMenueHtmlElements($kerndaten_id, $block_bez, $block_id) {
    $tablename = $block_bez;
    //	$Bol = new BlumenbachOnline();
    //	$response = new tx_xajax_response();
    //	$Bol->tablename = 'tx_bolonline_'.$block_bez;
    //	$Bol->getTableColumns();
    //		$content = $block_bez . '<br>';
    ob_start();
    /*
		 * PartIII = gelb
		 * PartII = blau
		 * PartI = rot
		 * PartIV = orange
    */
    $ids_blau = array ();
    $ids_rot = array ();
    //	$content = '<TABLE>';
    if ($block_bez == "tx_bolonline_PartIII") { //=gelb
      // Blau-id(s) (PartII) muessen auswaehlbar sein
      // JavaScript hierfuer muss in die Hauptdatei, damit bei Aufruf dieses Ajax-Blockes vorhanden!
      //			$content .= '
      //						<script language="JavaScript">';
      //			$content .= '
      //						function getMultiple(ob) {
      //							var arSelected = "";
      //							var counter = 0;
      //							while (ob.selectedIndex != -1) {
      //
      //							if (counter==0) arSelected+= ob.options[ob.selectedIndex].value;
      //							else arSelected+="|"+ob.options[ob.selectedIndex].value;
      //								ob.options[ob.selectedIndex].selected = false;
      //								counter++;
      //							} // You can use the arSelected array for further processing.
      //
      //						return arSelected;
      //						}
      //						';
      //			$content .= '
      //						</script>';

      $ids_blau = $this->getAssociationIDs($kerndaten_id, 'tx_bolonline_PartII', 'b1', 0);
      $ids_blau_associated = $this->getAssociatedIDs($kerndaten_id, $tablename, $block_id);
      //			$content .= $this->dump($ids_blau_associated,1);
      $size = min(sizeOf($ids_blau), 3);
      $selection_name = "tx_bolonline_PartIII_PartIII_" . $block_id;
      //	$content = "<select name='assoziation' onChange='xajax_setAccociationIDs(this.assoziation.value)'>";
      $content .= "<tr><td><div style='color:blue'>Zuordnung zu Blau:</div><div style='color:black;display:none;
        width:300px;padding:2px' id='" . $selection_name . "_message'></div></td><td colspan='2'><select id='" .
      $selection_name . "'multiple='multiple' size='" . $size . "' 	>";
      foreach ($ids_blau as $nr => $a_data) {
        $selected = "";
        $spid = $ids_blau[$nr]['id'];
        if (in_array($spid, $ids_blau_associated["partII_ids"])) {
          $selected = " selected";
        }
        $bezeichner = "ID " . $spid . ": " . substr($ids_blau[$nr]['b1'], 0, 70) . "...";
        $content .= "<option" . $selected . " value='" . $spid . "'>" . $bezeichner . "</option>";
      }
      $content .= "</select>";

      //
      $content .= "<span style='font-size:0.9em'>(Auswahl mit gedr&uuml;ckter Strg-Taste)</span><br />
        <input type='button' onClick='xajax_setAccociationIDs(\"tx_bolonline_PartIII_associations\",
        getMultiple(document.getElementById(\"" . $selection_name . "\")),\"" . $selection_name . "\",
      $kerndaten_id,\"tx_bolonline_PartII\",$block_id)'  style='font-size:11px;border:1px solid gray;
      padding:2px;width:60px;height:22px' value='zuordnen'></td></tr>";

      //			$content .= "<span style='font-size:0.9em'>(Auswahl mit gedr&uuml;ckter Strg-Taste)</span><br /><input type='button' onblur='getMultiple(document.getElementById(\"" . $selection_name . "\").options)' style='font-size:11px;border:1px solid gray;padding:2px;width:60px;height:22px' value='zuordnen'><div style='display:none;width:200px' id='" . $selection_name . "_message'></div></td></tr>";

      /* TODO! --------------- neu rot --------*/
      /*$ids_rot = $this->getAssociationIDs($kerndaten_id, 'tx_bolonline_PartI', 'a4', 0);
      //	$content .= "<br><select name='assoziation2' onChange='xajax_setAccociationIDs(this.assoziation.value)'>";
      $ids_rot_associated = $this->getAssociatedIDs($kerndaten_id, $tablename, $block_id);
      //			$content .= $this->dump($ids_rot_associated,1);
      //		$content .= "\n<tr><td style='color:red'>Zuordnung zu Rot:</td><td colspan='2'><select id='tx_bolonline_PartI_".$block_id."'>";
      $selection_name = "tx_bolonline_PartIII_PartI_" . $block_id;
      $content .= "\n<tr><td><div style='color:red'>Zuordnung zu Rot:</div><div style='color:black;display:none;width:280px;padding:2px' id='" . $selection_name . "_message'></div></td><td colspan='2'><select id='" . $selection_name . "' onChange='xajax_setAccociationIDs(\"tx_bolonline_PartIII_associations\",document.getElementById(\"" . $selection_name . "\").options[document.getElementById(\"" . $selection_name . "\").selectedIndex].value,\"" . $selection_name . "\",$kerndaten_id,\"tx_bolonline_PartI\",$block_id)'>";
      $content .= "<option value=''>Zuornung festlegen</option>";
      foreach ($ids_rot as $nr => $a_data) {
        $selected = "";
        $spid = $ids_rot[$nr]['id'];
        if ($spid == $ids_blau_associated["partI_ids"]) {
          $selected = " selected";
        }
        $bezeichner = "ID " . $spid . ": " . substr($ids_rot[$nr]['b1'], 0, 80) . "...";
        $content .= "<option" . $selected . " value='" . $spid . "'>" . $bezeichner . "</option>";
      }
      $content .= "</select></td></tr>\n";
       * */
      /*************************************/
      $ids_rot = $this->getAssociationIDs($kerndaten_id, 'tx_bolonline_PartI', 'a4', 0);
      $ids_rot_associated = $this->getAssociatedIDs($kerndaten_id, $tablename, $block_id);
      $size = min(sizeOf($ids_blau), 3);
      $selection_name = "tx_bolonline_PartIII_PartI_" . $block_id;
      //	$content = "<select name='assoziation' onChange='xajax_setAccociationIDs(this.assoziation.value)'>";
      $content .= "<tr><td><div style='color:red'>Zuordnung zu Rot:</div><div style='color:black;display:none;width:300px;
        padding:2px' id='" . $selection_name . "_message'></div></td><td colspan='2'><select id='" . $selection_name
      . "'multiple='multiple' size='" . $size . "' 	>";
      foreach ($ids_rot as $nr => $a_data) {
        $selected = "";
        $spid = $ids_rot[$nr]['id'];
        if (in_array($spid, $ids_rot_associated["partI_ids"])) {
          $selected = " selected";
        }
        $bezeichner = "ID " . $spid . ": " . substr($ids_rot[$nr]['b1'], 0, 70) . "...";
        $content .= "<option" . $selected . " value='" . $spid . "'>" . $bezeichner . "</option>";
      }
      $content .= "</select>";
      $content .= "<span style='font-size:0.9em'>(Auswahl mit gedr&uuml;ckter Strg-Taste)</span><br />
        <input type='button' onClick='xajax_setAccociationIDs(\"tx_bolonline_PartIII_associations\",
        getMultiple(document.getElementById(\"" . $selection_name . "\")),\"" . $selection_name . "\",
      $kerndaten_id,\"tx_bolonline_PartI\",$block_id)'  style='font-size:11px;border:1px solid gray;padding:2px;
      width:60px;height:22px' value='zuordnen'></td></tr>";


      /*--/TODO ------------- neu rot --------*/

    }

    if ($block_bez == "tx_bolonline_PartIV") { //=orange
      // Blau-id (PartII) muss auswaehlbar sein + Rot-ID (PartI) muss auswaehlbar sein

      $ids_blau = $this->getAssociationIDs($kerndaten_id, 'tx_bolonline_PartII', 'b1', 0);
      $ids_blau_associated = $this->getAssociatedIDs($kerndaten_id, $tablename, $block_id);
      //			$ids_blau_associated = $this->getAssociatedIDs('tx_bolonline_PartIV', 26);
      //			$content .= $this->dump($ids_blau_associated,1);
      $selected = "";

      $selection_name = "tx_bolonline_PartIV_PartII_" . $block_id;
      $content .= "\n<tr><td><div style='color:blue'>Zuordnung zu Blau:</div>
        <div style='color:black;display:none;width:280px;padding:2px' id='" .
      $selection_name . "_message'></div></td><td colspan='2'><select id='" .
      $selection_name . "' onChange='xajax_setAccociationIDs(\"tx_bolonline_PartIV_associations\",
        document.getElementById(\"" . $selection_name . "\").options[document.getElementById(\"" .
      $selection_name . "\").selectedIndex].value,\"" . $selection_name . "\",$kerndaten_id,\"tx_bolonline_PartII\",
      $block_id)'>";
      $content .= "<option value=''>Zuordnung festlegen</option>";
      foreach ($ids_blau as $nr => $a_data) {
        $selected = "";
        $spid = $ids_blau[$nr]['id'];
        if ($spid == $ids_blau_associated["partII_ids"]) {
          $selected = " selected";
        }
        $bezeichner = "ID " . $spid . ": " . substr($ids_blau[$nr]['b1'], 0, 70) . "...";
        $content .= "<option" . $selected . " value='" . $spid . "'>" . $bezeichner . "</option>";
      }
      $content .= "</select></td></tr>\n";




      /******************** Zuordnung Rot *******************************************/

      $ids_rot = $this->getAssociationIDs($kerndaten_id, 'tx_bolonline_PartI', 'a4', 0);
      //	$content .= "<br><select name='assoziation2' onChange='xajax_setAccociationIDs(this.assoziation.value)'>";
      $ids_rot_associated = $this->getAssociatedIDs($kerndaten_id, $tablename, $block_id);
      //			$content .= $this->dump($ids_rot_associated,1);
      //		$content .= "\n<tr><td style='color:red'>Zuordnung zu Rot:</td><td colspan='2'><select id='tx_bolonline_PartI_".$block_id."'>";

      $selection_name = "tx_bolonline_PartIV_PartI_" . $block_id;
      /*
      $content .= "\n<tr><td><div style='color:red'>Zuordnung zu Rot:</div>
        <div style='color:black;display:none;width:280px;padding:2px' id='" .
      $selection_name . "_message'></div></td><td colspan='2'><select id='" .
      $selection_name . "' onChange='xajax_setAccociationIDs(\"tx_bolonline_PartIV_associations\",
        document.getElementById(\"" . $selection_name . "\").options[document.getElementById(\"" .
      $selection_name . "\").selectedIndex].value,\"" . $selection_name . "\",$kerndaten_id,\"tx_bolonline_PartI\",
      $block_id)'>";
      $content .= "<option value=''>Zuornung festlegen</option>";
      foreach ($ids_rot as $nr => $a_data) {
        $selected = "";
        $spid = $ids_rot[$nr]['id'];
        if ($spid == $ids_rot_associated["partI_ids"]) {
          $selected = " selected";
        }
        $bezeichner = "ID " . $spid . ": " . substr($ids_rot[$nr]['a4'], 0, 80) . "...";
        $content .= "<option" . $selected . " value='" . $spid . "'>" . $bezeichner . "</option>";
      }
      $content .= "</select></td></tr>\n";
    }*/
      //	$content = "<select name='assoziation' onChange='xajax_setAccociationIDs(this.assoziation.value)'>";
      $content .= "<tr><td><div style='color:red'>Zuordnung zu Rot:</div><div style='color:black;display:none;width:300px;
        padding:2px' id='" . $selection_name . "_message'></div></td><td colspan='2'><select id='" . $selection_name
      . "'multiple='multiple' size='" . $size . "' 	>";
      foreach ($ids_rot as $nr => $a_data) {
        $selected = "";
        $spid = $ids_rot[$nr]['id'];
        if (in_array($spid, $ids_rot_associated["partI_ids"])) {
          $selected = " selected";
        }
        $bezeichner = "ID " . $spid . ": " . substr($ids_rot[$nr]['b1'], 0, 70) . "...";
        $content .= "<option" . $selected . " value='" . $spid . "'>" . $bezeichner . "</option>";
      }
      $content .= "</select>";
      $content .= "<span style='font-size:0.9em'>(Auswahl mit gedr&uuml;ckter Strg-Taste)</span><br />
        <input type='button' onClick='xajax_setAccociationIDs(\"tx_bolonline_PartIV_associations\",
        getMultiple(document.getElementById(\"" . $selection_name . "\")),\"" . $selection_name . "\",
      $kerndaten_id,\"tx_bolonline_PartI\",$block_id)'  style='font-size:11px;border:1px solid gray;padding:2px;
      width:60px;height:22px' value='zuordnen'></td></tr>";
    }

    //		$content .= '</TABLE>';
    //	$content = "*** kerndaten_id=$kerndaten_id,block_bez=$block_bez ***";
    //		$response->assign('association_pd_' . $block_id, 'style.display', 'none');
    //		$response->assign('association_pd_' . $block_id, 'innerHTML', $content);
    //		$response->assign('association_pd_' . $block_id, 'style.display', 'block');

    return $content;
    //	return $response->getXML();
  }

  function setAssociation($kerndaten_id, $block_bez, $block_id) {

  }

  function getMediafiles($kerndaten_id, $tablename = "", $part_id = 0) {

    $tablename = $tablename == "" ? "tx_bolonline_Mediafiles" : $tablename;
    $query = 'SELECT * FROM ' . $tablename . ' WHERE kerndaten_id=' . $kerndaten_id . ' ORDER BY id ASC';
    //		echo $query;
    $content = array ();
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    $content = array ();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $rowbez = array_keys($row);
      //			print_r($row);
      $content[] = $row;

    }
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    return $content;
  }

  function getMediafileName($id, $tablename = "") {
    $mediafile_name = "";

    $tablename = $tablename == "" ? "tx_bolonline_Mediafiles" : $tablename;
    $query = 'SELECT file_uri FROM ' . $tablename . ' WHERE id=' . $id;
    //echo $query;
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $mediafile_name = $row['file_uri'];
    }
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    return $mediafile_name;
  }

  function getMediafileNames($content_id, $tablename) {
    $mediafile_names = array ();
    $part_label = $this->getPartLabel($tablename);
    $query = 'SELECT file_uri FROM ' . $tablename . ' WHERE ' . $part_label . '_id=' . $content_id;
    //		echo $query;
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $mediafile_names[] = $row['file_uri'];
    }
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);
    return $mediafile_names;
  }

  function deleteMediafile($id, $tablename) {
    // delete mediafile
    //		echo '$tablename:'.$this->dump($tablename);die();
    $tablename = $tablename == "" ? "tx_bolonline_Mediafiles" : $tablename;
    $part_label = $this->getPartLabel($tablename);
    $mediafile_name = $this->getMediafileName($id, $tablename);
    //		echo '$mediafile_name:'.$mediafile_name;die();
    $deleted = @ unlink($this->workpath . $mediafile_name);

    // delete database-entry
    $query = 'DELETE FROM ' . $tablename . ' WHERE id=' . $id;
    //		echo $query;
    $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);

    if ($deleted)
      return (boolean) true;
    else
      return (boolean) false;
  }

  function deleteMediafiles($id, $tablename) {
    // delete mediafile
    $part_label = $this->getPartLabel($tablename);
    $mediafile_names = array ();
    $mediafile_names = $this->getMediafileNames($id, $tablename);
    //		$this->dump($mediafile_names); die();
    foreach ($mediafile_names as $nr => $mediafile_name) {
      //			echo "<br>lösche: $mediafile_name";
      unlink($this->workpath . $mediafile_name);
    }
    //		die();
    //		echo '$mediafile_name:'.$mediafile_name;die();
    //		$deleted = @ unlink($this->workpath . $mediafile_name);

    // delete database-entry
    $query = 'DELETE FROM ' . $tablename . ' WHERE ' . $part_label . '_id=' . $id;
    //		echo $query;
    $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);

    $erg = $this->getMediafileNames($id, $tablename);
    $deleted = sizeOf($erg) > 0 ? 0 : 1;
    if ($deleted)
      return (boolean) true;
    else
      return (boolean) false;
  }

  function getMainCategories($kerndaten_id = null) {
    $query = "";
    if ($kerndaten_id == null) { // alle Haupt-Kategorien holen
      $query = 'SELECT id,kategorie FROM tx_bolonline_Hauptkategorie ORDER BY kategorie ASC';
    } else {
      $query = 'SELECT hk.id,hk.kategorie FROM tx_bolonline_Hauptkategorie hk,tx_bolonline_HauptkategorieZuordnung hkz WHERE hkz.kategorie_id=hk.id AND hkz.kerndaten_id=' . $kerndaten_id;
    }
    //		echo $query;
    $content = array ();
    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    $content = array ();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $rowbez = array_keys($row);
      //			print_r($row);
      $content[] = $row;

    }
    @ $GLOBALS['TYPO3_DB']->sql_free_result($res);

    return $content;
  }

  function setMainCategory($kategorie_id, $kerndaten_id) {
    $query = "";
    if ($kerndaten_id && $kategorie_id) { // alle Haupt-Kategorien holen
      $query = 'INSERT INTO tx_bolonline_HauptkategorieZuordnung VALUES(NULL,' . $kategorie_id . ',' . $kerndaten_id . ') ON DUPLICATE KEY UPDATE kategorie_id=' . $kategorie_id . ',kerndaten_id=' . $kerndaten_id;
      	//		echo $query;
      $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    }
  }

  function setImageOrder($table, $id, $order) {
    $query = "UPDATE ".$table." SET sortorder = '".$order."' WHERE id = ".$id;
    $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
    // return $query
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bolonline/pi1/class.tx_nwstmpl_pi1.php']) {
  include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bolonline/pi1/class.tx_nwstmpl_pi1.php']);
}
?>
