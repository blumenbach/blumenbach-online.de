<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 Mads Brunn (brunn@mail.dk)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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
 * Plugin 'Upload' for the 'fileupload' extension.
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_fileupload_pi1 extends tslib_pibase {
	var $prefixId = "tx_fileupload_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_fileupload_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "fileupload";	// The extension key.
	var $status = array();
	


	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		//debug($this->conf);
		$this->maxsize=$this->conf['maxsize']?$this->conf['maxsize']:100000;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		if($this->piVars['do_upload']){
			$this->handleUpload();
		} 
		$content.=$this->displayUploadForm();
		return $this->pi_wrapInBaseClass($content);
	}

	function displayUploadForm(){
		$content = $this->cObj->cObjGetSingle($this->conf['uploadformcObject'],$this->conf['uploadformcObject.']);
		$content = str_replace("###STATUS###",(empty($this->status) ? "" : array_pop($this->status)),$content);		
		return $content;
	}

	function handleUpload(){
		global $TYPO3_CONF_VARS;
		$content='';
		$path = '';
		if($this->conf['path']){
			$path=$this->cObj->stdWrap($this->conf['path'],$this->conf['path.']);
		}
		$uploaddir = is_dir($path)?$path:$TYPO3_CONF_VARS['BE']['fileadminDir'];
		//if file should be uploaded to the login users homedir
		if($this->conf['FEuserHomePath'] && $GLOBALS["TSFE"]->loginUser){ 
			if($this->conf['FEuserHomePath.']['field']){
				$feuploaddir=$uploaddir.$GLOBALS["TSFE"]->fe_user->user[$this->conf['FEuserHomePath.']['field']].'/';
			} else {
				$feuploaddir=$uploaddir.$GLOBALS["TSFE"]->fe_user->user["uid"].'/';
			}
			if(!is_dir($feuploaddir)){
				if(!mkdir($feuploaddir)){
					$feuploaddir=$uploaddir;
				}
			}
			$uploaddir = $feuploaddir;
		}

		$uploadfile = $uploaddir.$_FILES[$this->prefixId]['name'];
		
		if(is_file($uploadfile) && $this->conf['noOverwrite']){//file already exists?
			$this->status[] = $this->cObj->cObjGetSingle($this->conf['message.']['exist'],$this->conf['message.']['exist.']);
		}
		
		if($this->file_too_big($_FILES[$this->prefixId]['size'])){
			$this->status[] = $this->cObj->cObjGetSingle($this->conf['message.']['toobig'],$this->conf['message.']['toobig.']);			
		}
		
		if(!$this->mime_allowed($_FILES[$this->prefixId]['type'])){ //mimetype allowed?
			$this->status[] =  $this->cObj->cObjGetSingle($this->conf['message.']['mimenotallowed'],$this->conf['message.']['mimenotallowed.']);				
		}
		
		if(!$this->ext_allowed($_FILES[$this->prefixId]['name'])){ //extension allowed?
			$this->status[] =  $this->cObj->cObjGetSingle($this->conf['message.']['extensionnotallowed'],$this->conf['message.']['extensionnotallowed.']);				
		}
		
		if(empty($this->status)){ //no errors so far
			if(move_uploaded_file($_FILES[$this->prefixId]['tmp_name'], $uploadfile)) {//succes!
				$filemode = octdec($this->conf['fileMode']);
				@chmod($uploadfile,$filemode);
				$this->status[] = $this->cObj->cObjGetSingle($this->conf['message.']['uploadsuccesfull'],$this->conf['message.']['uploadsuccesfull.']);	
	 		} else {
				$this->handle_error($_FILES[$this->prefixId]['error']);
			}
		}

	}
	
	

	function handle_error($error){

		switch ($error){
			case 0: 
					break;
			case 1:
			case 2:
					$this->status[] = $this->cObj->cObjGetSingle($this->conf['message.']['toobig'],$this->conf['message.']['toobig.']);
					break;
			case 3:
					$this->status[] = $this->cObj->cObjGetSingle($this->conf['message.']['partial'],$this->conf['message.']['partial.']);
					break;
			case 4:
					$this->status[] = $this->cObj->cObjGetSingle($this->conf['message.']['nofile'],$this->conf['message.']['nofile.']);
					break;
			default:
					$this->status[] = $this->cObj->cObjGetSingle($this->conf['message.']['unknown'],$this->conf['message.']['unknown.']);
					break;
		}

	}

	function mime_allowed($mime){
		if(!($this->conf['checkMime'])) return TRUE; 		//all mimetypes allowed
		$includelist = explode(",",$this->conf['mimeInclude']);
		$excludelist = explode(",",$this->conf['mimeExclude']);		//overrides includelist
		return (   (in_array($mime,$includelist) || in_array('*',$includelist))   &&   (!in_array($mime,$excludelist))  );
	}

	function ext_allowed($filename){
		if(!($this->conf['checkExt'])) return TRUE;			//all extensions allowed
		$includelist = explode(",",$this->conf['extInclude']);
		$excludelist = explode(",",$this->conf['extExclude']) 	;	//overrides includelist
		$extension='';
		if($extension=strstr($filename,'.')){
			$extension=substr($extension, 1);    
			return ((in_array($extension,$includelist) || in_array('*',$includelist)) && (!in_array($extension,$excludelist)));
		} else {
			return FALSE;
		}
	}
	
	function file_too_big($filesize){
		return $filesize > $this->maxsize;
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/fileupload/pi1/class.tx_fileupload_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/fileupload/pi1/class.tx_fileupload_pi1.php"]);
}

?>