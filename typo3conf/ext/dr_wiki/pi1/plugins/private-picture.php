<?php

// Sucht das angegebene Bild und bindet es in den Text in

class tx_drwiki_pi1_picture {

   function getDefaultParams(){
      return array(14);
   }

   function main($object, $params){

      $this->cObj = t3lib_div::makeInstance('tslib_cObj');
      $this->conf = $object->conf;

      // Default Werte
      $aConf['thumb_width'] = 180;
      $aConf['thumb_div_expand'] = 10;
      $aConf['div_expand'] = 10;
      $aConf['file_path'] = 'fileadmin';

      if($this->conf['bild.']['thumbWidth']) $aConf['thumb_width'] = $this->conf['bild.']['thumbWidth'];
      if($this->conf['bild.']['thumbDivExpand']) $aConf['thumb_div_expand'] = $this->conf['bild.']['thumbDivExpand'];
      if($this->conf['bild.']['DivExpand']) $aConf['div_expand'] = $this->conf['bild.']['DivExpand'];
      if($this->conf['bild.']['filePath']) $aConf['file_path'] = $this->conf['bild.']['filePath'];

      // Setzt einen '/' an das Ende des Pfades, wenn dieser fehlt
      if(substr($aConf['file_path'], -1) != '/'){
         $aConf['file_path'] .= '/';
      }

      $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_dam', 'title LIKE "'.$params[0].'"');
      echo $GLOBALS['TYPO3_DB']->sql_error();
      $aFile = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

      // Kontrollieren ob Datei auch vorhanden ist
      if(!file_exists($aFile['file_path'].$aFile['file_name'])) {
        return '<b>Error: Datei nicht vorhanden!</b></br>';
      }

      // Ermittelt die Dateigroesse, wenn diese Werte nicht aus dem DAM kommen
      if($aFile['width'] == 0) {
         $imgsize = getimagesize($aFile['file_path'].$aFile['file_name']);
         $aFile['width'] = $imgsize[0];
         $aFile['height'] = $imgsize[1];
      }

      // Standardformate festlegen
      $aFormat['img_height'] = $aFile['height'];
      $aFormat['img_width'] = $aFile['width'];
      $aFormat['float'] = 'left';
      $aFormat['class'] = 'ImageDefault';


      // Ermittelt die Formatierungs-Parameter
      $paramsSize = count($params);

      // Max. Anzahl an Format-Parametern
      if($paramsSize == 2) {
         // Sind nur 2 Werte angegeben wird nur ein Format ermittelt
         // Beispiel: MeinBild.jpg|thumb (eigentlich eine falsch Angabe)
         //           MeinBild.jpg|Beschreibung (richtig Angabe)
         $maxFormats = 1;
      }
      else {
      // 1. Element = Dateiname
      // letztes Element = Beschreibung für Bild
      // diese werden für die Formatanweisungen ausgelassen
      // Beispiel: MeinBild.jpg|thumb|Beschreibung
         $maxFormats = $paramsSize - 2;
      }

      // Wenn kein Format gefunden wurde, wird der letzte Parameter auf jeden Falls
      // als Beschreibung gewertet.
      $lFormat = true;

      for ($i = 1; $i <= $maxFormats; $i++) {
         switch($params[$i]) {
         case 'thumb':
            $aFormat['class'] = 'ImageThumb';
            $aFormat['img_width'] = $aConf['thumb_width'];
            break;
         case 'frameless':
            $aFormat['class'] = 'ImageFrameless';
            $aFormat['img_width'] = $aConf['thumb_width'];
            break;
         case 'right':
            $aFormat['float'] = 'right';
            break;
         case 'border':
            $aFormat['class'] = 'ImageBorder';
            break;
         default:
            // Sucht ob Bild skaliert werden soll "px"
            // überschreibt zuvor ermittelte Werte
            if(substr($params[$i], strlen($params[$i]) - 2, 2) == 'px') {
               if(substr($params[$i], 0, 1) == 'x') {
                  // Die Höhe des Bildes wurde angegeben
                  // Beispiel: x100px
                  $aFormat['img_height'] = substr($params[$i], 1, (strlen($params[$i]) - 3));
                  $aFormat['img_width'] = false;
               }
               else {
                  // Die Breite des Bildes wurde angegeben
                  // Beispiel: 100px
                  $aFormat['img_width'] = substr($params[$i], 0, (strlen($params[$i]) - 2));
                  $aFormat['img_height'] = false;
               }
            }
            else {
               if($i == 1) {
                  // Wenn der 1 Parameter nicht als Formatangabe erkannt wurde
                  $lFormat = false;
               }
            }
         }
      }

      // Der letzte Parameter ist die Beschreibung zum Bild
      if(($paramsSize > 2) || (!$lFormat)) {
         $aFile['description'] = $params[$paramsSize -1];
      }


      // Hier werden die Formatanweisungen umgesetzt
      switch($aFormat['class']) {
         case 'ImageThumb':
            // Thumbnail mit Rahmen und rechtsbündig
            $aFormat['float'] = 'right';
            $aFormat['div_width'] = $aFormat['img_width'] + $aConf['thumb_div_expand'];
            break;
         case 'ImageFrameless':
            // Thumbnail ohne Rahmen und linksbündig
            $aFormat['div_width'] = $aFormat['img_width'] + $aConf['thumb_div_expand'];
            break;
         case 'ImageBorder':
            // Bild mit Rahmen linksbündig
            $aFormat['div_width'] = $aFormat['img_width'] + $aConf['div_expand'];
            break;
         default:
            $aFormat['div_width'] = $aFormat['img_width'] + $aConf['div_expand'];
      }


      // Setzt die Style-Anweisung für den DIV zusammen
      $aStyle['div'] = 'style="float: '.$aFormat['float'].';';
      if($aFormat['div_width']) {
         $aStyle['div'] .= ' width: '.$aFormat['div_width'].'px;';
      }
      $aStyle['div'] .= '"';


      // Setzt die Style-Anweisung für das Image zusammen
      //
      // $aFormat['img_height'] wird derzeit nicht verwendet
      $aStyle['img'] = 'style="width: '.$aFormat['img_width'].'px;"';


      // Liest die TS Einstellungen für das Image ein.
      // Beispiel:
      // plugin.tx_drwiki_pi1.bild.ImageDefault.wrap = <span style="border: solid 5px red">|</span>

      $aImage = $this->conf['bild.'][$aFormat['class'].'.'];

      $aImage['file'] = $aFile['file_path'].$aFile['file_name'];
      $aImage['file.']['width'] = $aFormat['img_width'];


      // alt_text aus DAM ermitteln
      if($aFile['alt_text']) {
         $aImage['altText'] = $aFile['alt_text'];
      }
      else {
         $aImage['altText'] = $aFile['title'];
      }
      // "title" wird einfach aus "alt" übernommen
      $aImage['titleText'] = $aImage['altText'];

      $content = '<div class="'.$aFormat['class'].'" '.$aStyle['div'].'>'.$this->cObj->IMAGE($aImage);

      if($aFile['description']) {
	        // Wenn Beschreibung vorhanden ist
	        $content .= '<span>'.$aFile['description'].'</span>';
      }

      $content .= '</div>';


      return $content;
   }
}

?>