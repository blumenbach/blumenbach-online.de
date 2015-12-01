<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Ubbo Veentjer (ubbo.veentjer@gmx.de)
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
 * Plugin 'TG-Filemanager' for the 'tg_filemanager' extension.
 *
 * @author  Ubbo Veentjer <ubbo.veentjer@gmx.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_tgfilemanager_pi1 extends tslib_pibase {
  var $prefixId = 'tx_tgfilemanager_pi1';    // Same as class name
  var $scriptRelPath = 'pi1/class.tx_tgfilemanager_pi1.php';  // Path to this script relative to the extension dir.
  var $extKey = 'tg_filemanager';  // The extension key.
  var $pi_checkCHash = TRUE;
  
  var $dir; // the Request dir
  var $filedir; // entry directory
  var $absdir; // absolute dir to browse
  var $reldir; // relative dir to browse
  var $updir; // directory up
  
  var $dirperm='0777'; // permissions for new directories (octal)
  
  /**
   * [Put your description here]
   */
  function main($content,$conf)  {
    $this->conf=$conf;
    $this->pi_setPiVarDefaults();
    $this->pi_loadLL();
      //init Flexform configuration for Plugin
    $this->pi_initPIflexForm();
      // no cache fuers erste...
    $GLOBALS["TSFE"]->set_no_cache();
       // not sure if its safer, but the script should not go for ..
    if(!(strpos($this->piVars['dir'], '..') === false)){
         echo "to much memory exhausted, windows is going down :-P";
         exit();
      } 

      // directory settings:
    $this->dir=$this->piVars['dir'];
    $this->filedir=$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'abspath');
    $this->absdir=$this->filedir.$this->dir;
    $this->reldir=$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'relpath').$this->dir;
    $this->iconpath=$conf['iconpath'];
    $updir=explode('/', $this->dir);
    unset($updir[count($updir)-2]);
    $this->updir=implode('/', $updir);
    //$this->dirperm=$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'dir_perm');

      //echo $this->dirperm;
      
    if(!is_dir($this->absdir)){
      $content .= "Pfad nicht gefunden, evt gel&ouml;scht oder umbenannt?";
      $this->absdir=$this->filedir;
    }
      // Do Action
    $this->doRequestedActions();
      // render content
    $content.=$this->renderFm();
    if($this->piVars['del']){
      $content .= $this->renderDel($dir, $absdir, $this->piVars['del']);
    }elseif($this->piVars['rename']){
      $content .= $this->renderRename($this->dir, $this->absdir, $this->piVars['rename']);
    }else{
      $content .= $this->renderAddData($this->dir);
    }
    return $this->pi_wrapInBaseClass($content);
  }
  
  function doRequestedActions() {
      if($this->piVars['download']){
         $this->putFile($this->absdir, $this->piVars['download']);
      }
  
    if($this->piVars['createDir']){
            // be carefull with 777 - do we have to filter strings?
      mkdir($this->absdir.$this->piVars['createDirName'], 0775);
    }
    if($this->piVars['upload']){
      if (!move_uploaded_file($_FILES['upFile']['tmp_name'], $this->absdir.$_FILES['upFile']['name'])) {
        //print_r($_FILES['upFile']);
      }
    }
    if($this->piVars['doRename']){
      rename($this->absdir.$this->piVars['toRename'], $this->absdir.$this->piVars['newName']);
    }
    if($this->piVars['sureDel']){
      $todel = $this->absdir.$this->piVars['toDel'];
      if(is_file($todel)) unlink($todel);
      if(is_dir($todel)){
        if (!rmdir($todel)) echo "Verzeicnis nicht leer";
      }
    }
   }
  
  function renderFM(){
    $content .= '<table width="100%"><tr><td>';
    if($this->dir != ''){
      $uplink=$this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1[dir]' => $this->updir));
      $content .= '<a href="'.$uplink.'"><img src="'.$this->iconpath.'go-up.png" alt="In &uuml;bergeordnetes Verzeichnis wechseln" title="In &uuml;bergeordnetes Verzeichnis wechseln" /></a>';
    }
    
    $content .= '</td><td align="right">/'.$this->dir.'</td></tr></table>
           <table width="100%">
           <tr>
             <th>Name</th>
             <th>Gr&ouml;sse</th>
             <th>Letzte &Auml;nderung</th>
             <th>&nbsp;</th><th>&nbsp;</th>
           </tr>';
    $content .= $this->renderDirList($this->absdir, $this->dir, $this->filedir);
    $content .= '</table>';
    return $content;
  }
  
  function renderAddData($dir){
    $action= $this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir));
    $content .='
     <div id="action-box">
     <form action="'.$action.'" method="post" enctype="multipart/form-data">    
        <input type="text" size="30" name="'.$this->prefixId.'[createDirName]" />
        <input type="hidden" name="'.$this->prefixId.'[dir]" value=  "'.$this->dir.'" />
        <input type="submit" value="Verzeichnis Erstellen" name="'.$this->prefixId.'[createDir]" /><br/><br/>   
        <input type="file" size="30" name="upFile" />
        <input type="submit" value="Upload" name="'.$this->prefixId.'[upload]" /> 
     </form>
     </div>';
     return $content;
  }
  
  function renderRename($dir, $absdir, $torename){
    $action= $this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir));
    return '
     <div id="action-box">       
        <form action="'.$action.'" method="post">
           <input type="hidden" name="'.$this->prefixId.'[toRename]" value="'.$torename.'">
           <input type="hidden" name="'.$this->prefixId.'[dir]" value=  "'.$this->dir.'" />
           Neuer Name für "'.$torename.'":&nbsp;&nbsp; 
           <input type="input" name="'.$this->prefixId.'[newName]" value="'.$torename.'">
           <input type="submit" name="'.$this->prefixId.'[doRename]" value="Umbenennen">
        </form>
     </div>';
  }
  
  function renderDel($dir, $absdir, $todel){
    $action= $this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir));
    return '
        <div id="warn-box">
        <p><img src="'.$this->iconpath.'warning.png" alt="Achtung" />
        Sind Sie Sicher, das Sie '.$todel.' l&ouml;schen wollen? <br/>
        Dieser Vorgang kann nicht R&uuml;ckg&auml;ngig gemacht werden </p>
        <form action="'.$action.'" method="post">
           <input type="hidden" name="'.$this->prefixId.'[toDel]" value="'.$todel.'">
           <input type="hidden" name="'.$this->prefixId.'[dir]" value=  "'.$this->dir.'" />
           <input type="submit" name="'.$this->prefixId.'[sureDel]" value="Ja">
           <input type="submit" name="'.$this->prefixId.'[noDel]" value="Nein">
        </form>
        </div>';
  }  
  
  function renderDirList($absdir, $dir, $filedir){
    $lsarr=$this->dirToArray($absdir);

    if(is_array($lsarr['dirs'])) foreach($lsarr['dirs'] as $entry){
      $stat=stat($absdir.$entry);
      $ren_action=$this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir, $this->prefixId.'[rename]' => $entry));
      $del_action=$this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir, $this->prefixId.'[del]' => $entry));
      $content .= '<tr class="link">';
      $content .= '<td><a href="'
            .$this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir.$entry.'/'))
            .'"><img src="'.$this->iconpath.'folder.png" alt="Verzeichnis" />&nbsp;&nbsp;'.$entry.'</a></td>'
            .'<td>&nbsp;</td>'
            .'<td>'.date('d.m.Y', $stat['ctime']).'</td>'
            .'<td><a href="'.$del_action.'"><img src="'.$this->iconpath.'user-trash.png" title="L&ouml;schen" alt="L&ouml;schen" /></a></td>'
            .'<td><a href="'.$ren_action.'"><img src="'.$this->iconpath.'rename.png" title="Umbenennen" alt="Umbenennen" /></a></td></tr>';
    }
    
    if(is_array($lsarr['files'])) foreach($lsarr['files'] as $entry){
      $stat=stat($absdir.$entry);
      $ren_action=$this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir, $this->prefixId.'[rename]' => $entry));
      $del_action=$this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir, $this->prefixId.'[del]' => $entry));
         $downloadlink=$this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_tgfilemanager_pi1%5Bdir%5D' => $this->dir, $this->prefixId.'%5Bdownload%5D' => $entry));
      $content .= '<tr class="link">'
            .'<td><a href="'.$downloadlink.'">'.$this->getFileIcon($entry).'&nbsp;&nbsp;'.$entry.'</a></td>'
            .'<td>'.$this->size_hum_read($stat['size']).'</td>'
            .'<td>'.date('d.m.Y', $stat['ctime']).'</td>'
            .'<td><a href="'.$del_action.'"><img src="'.$this->iconpath.'user-trash.png" title="L&ouml;schen" alt="L&ouml;schen" /></a></td>'
            .'<td><a href="'.$ren_action.'"><img src="'.$this->iconpath.'rename.png" title="Umbenennen" alt="Umbenennen" /></a></td></tr>';   
    }
    return $content;
  }
  
  function dirToArray($dir){
    if(is_dir($dir)&&is_readable($dir)){
      $d = dir($dir);
      while (false !== ($entry = $d->read())) {
        if($entry!='..' && $entry!='.'){
        //$stat=stat($dir.$entry);
          if(is_dir($dir.$entry)){
            $arr['dirs'][]=$entry;
          }elseif(is_file($dir.$entry)){
            $arr['files'][]=$entry;
          }
        }
      }    
      $d->close();
      if(is_array($arr['dirs'])) natcasesort($arr['dirs']);
      if(is_array($arr['files'])) natcasesort($arr['files']);
      return $arr;
    }else return false;
  }
  
  function getFileIcon($filename){
    $imgTypes = array('png', 'gif', 'jpg', 'svg');
    $docTypes = array('pdf', 'doc', 'sxw', 'odt');
    $compTypes = array('zip', 'gz', 'tar', 'rar', 'tgz');
    $webTypes = array('html', 'htm');
    
    $ext = substr(strrchr($filename, "."), 1);
    if (in_array($ext, $imgTypes)) $icon='file-image.png';
    else if (in_array($ext, $docTypes)) $icon='file-document.png';
    else if (in_array($ext, $compTypes)) $icon='file-compressed.png';
    else if (in_array($ext, $webTypes)) $icon='file-html.png';
    else $icon='file-txt.png';
    return '<img src="'.$this->iconpath.$icon.'" alt="Datei" />';
  }
  
    // stolen from php.net
  function size_hum_read($size){
    /*
    Returns a human readable size
    */
    $i=0;
    $iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    while (($size/1024)>1) {
      $size=$size/1024;
      $i++;
    }
    return substr($size,0,strpos($size,'.')+4).' '.$iec[$i];
  }
  
  function putFile($absdir, $filename){

    $file=$absdir.$filename;

    include("mimetypes.php");
    $fileinfo = t3lib_div::split_fileref($filename);
    $mimetype = $mimetypes[$fileinfo["fileext"]];
    if ($mimetype == "") $mimetype = "application/octet-stream";
    
    header( 'Content-type: '.$mimetype );
      // images may be embedded, other files are attached...
    if(substr($mimetype, 0, 5)!='image'){    
      header( 'Content-Length: ' . @filesize( $file ) );
      header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    }
    @readfile( $file );
    exit;
  }
  
  
     // mostly taken from moc_filemanager
     // not used anymore, may go...
  function putFile2($absdir, $filename){
     $file=$absdir.$filename;
     
        //Clean all output buffers.
    while (@ob_end_clean());
         // including mimetypes.php here means it is only included when required, thereby saving parsing time for all other requests
      include("mimetypes.php");
         // extract the file extesion and attempt to determine the Mime type from the file's extension
      $fileinfo = t3lib_div::split_fileref($filename);
      $mimetype = $mimetypes[$fileinfo["fileext"]];
      if ($mimetype == "") {
         $mimetype = "application/octet-stream";
      }
     
      $fp = @fopen($file, 'rb');
      if(!$fp) return "File not found";
      
      header("Cache-control: must-revalidate, post-check=0, pre-check=0");
      header("Content-Transfer-Encoding: binary");
      header("Content-Type: $mimetype");
      header("Content-Length: ".filesize($file));
      header("Content-Disposition: attachment; filename=".$filename);
      header("Cache-control: private");
      while (!feof($fp)) {
         $buffer = fgets($fp, 4096);
         echo $buffer;
      }
      fclose($fp);
      exit;
  }
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tg_filemanager/pi1/class.tx_tgfilemanager_pi1.php'])  {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tg_filemanager/pi1/class.tx_tgfilemanager_pi1.php']);
}

?>
