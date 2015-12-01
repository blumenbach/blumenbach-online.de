<?php
require(t3lib_extMgm::extPath("t3quixplorer")."mod1/t3quixplorer_edit.php");

class ux_t3quixplorer_edit extends t3quixplorer_edit{
	function savefile($file_name) {			// save edited file
		global $LANG;
		$code = t3lib_div::_POST("content_ta");
		if(t3lib_div::_POST("win_to_unix_br")){
			$code = str_replace(chr(13).chr(10),chr(10),$code);
		}
		$fp = @fopen($file_name, "w");
		if($fp===false) t3quixplorer_div::showError(basename($file_name).": ".$LANG->getLL("error.savefile"));
		fputs($fp, $code);
		@fclose($fp);
	}
	function getT3SExtension(){
		global $TYPO3_CONF_VARS;
		$conf = @unserialize($TYPO3_CONF_VARS["EXT"]["extConf"]["t3seditor"]);
		return $conf['ext'];
	}
	function getReadme($dir){
		$file = t3quixplorer_div::get_abs_item($dir, 'readme.html');
		if(@is_file($file)){
			return file_get_contents($file);
		}else{
			return '';
		}
	}
	function main($dir,$item,&$pObj) {

		global $LANG;


		if(!t3quixplorer_div::get_is_file($dir, $item)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.fileexists"));
		if(!t3quixplorer_div::get_show_item($dir, $item)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.accessfile"));
		$fname = t3quixplorer_div::get_abs_item($dir, $item);
		$fileinfo = t3lib_div::split_fileref($fname);
		$ext = $fileinfo['fileext'];
		$theight = ($GLOBALS["T3Q_VARS"]["textarea_height"] && is_numeric($GLOBALS["T3Q_VARS"]["textarea_height"]))?$GLOBALS["T3Q_VARS"]["textarea_height"]:20;


		$pObj->doc->JScode = '
<script id="prototype-script" type="text/javascript" src="../../t3scodehighlight/contrib/prototype/prototype.js">
</script>
<script src="../../t3scodehighlight/contrib/codepress/codepress.js" type="text/javascript" id="cp-script" lang="en-us"></script>
<link type="text/css" rel="stylesheet" href="../../t3scodehighlight/contrib/codepress/t3codepress.css">
</link>
<script src="../../t3scodehighlight/contrib/codepress/content/en-us.js" type="text/javascript" id="cp-script" lang="en-us"></script>
<script src="../../t3scodehighlight/contrib/codepress/t3codepress_t3lib_tceforms.js" type="text/javascript" id="t3codepress-t3libtceforms-script"></script>

				<script type="text/javascript">

					function closeDoc(){
						window.location=\''.t3quixplorer_div::make_link("list",$dir,NULL).'\';
					}
				</script>

			';

		$content= array();

		if(t3lib_div::_POST("dosave") && t3lib_div::_POST("dosave")=="yes") {
			// Save / Save As
			$item=basename(stripslashes(t3lib_div::_POST("fname")));
			$fname2=t3quixplorer_div::get_abs_item($dir, $item);
			if(!isset($item) || $item=="") t3quixplorer_div::showError($LANG->getLL("error.miscnoname"));
			if($fname!=$fname2 && @file_exists($fname2)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.itemdoesexist"));
			$this->savefile($fname2);
			$fname=$fname2;
		}

		// open file
		$fp = @fopen($fname, "r");
		if($fp===false) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.openfile"));
		@fclose($fp);

		$fileContent = t3lib_div::getUrl($fname);

		// header
		$s_item=t3quixplorer_div::get_rel_item($dir,$item);	if(strlen($s_item)>50) $s_item="...".substr($s_item,-47);



		//$content[]=$s_item;

		//changed to absolute filename as of version 1.7 ... any complaints?
		$content[] = $fname;

		//$onkeydown = $GLOBALS['T3Q_VARS']['disable_tab'] ? '' : ' onkeydown="return catchTab(this,event)" ';

		$fileinfo = t3lib_div::split_fileref(t3quixplorer_div::get_abs_item($dir,$item));
		$lang = t3lib_div::_GP("highlight_lang");
		$ext = $fileinfo['fileext'];


        $readme = $this->getReadme($dir);
        if(strlen(trim($readme))){
        	$readme = '<div style="border: 1px solid red; background-color: yellow; padding: 10px; display:block;">'.$readme.'</div>';
        }else{
        	$readme = '';
        }
		if(!$lang){
			$lang = $ext;
		}
     if($item == 'setup.txt' || $item == 'config.txt'|| $item == 'constants.txt'){
		$content[]= $readme.'
		  <br />
		    <form id="editform" name="editform" method="post" action="'.t3quixplorer_div::make_link("edit",$dir,$item).'" >
		    <input type="hidden" name="dosave" value="yes"> '.
          '<code id="content" wrap="off" title=".ts" class="cp hideLanguage" style="height: 500px; color: silver; visibility: visible;">'.t3lib_div::formatForTextarea($fileContent).'</code>'
			.'<textarea id="content_ta" name="content_ta" rows="'.$theight.'" wrap="off" style="width: 460px; display: none;" class="cp fixed-font enable-tab">'.t3lib_div::formatForTextarea($fileContent).'</textarea>';

      }else{
		$content[]= $readme.'
		  <br />
		    <form id="editform" name="editform" method="post" action="'.t3quixplorer_div::make_link("edit",$dir,$item).'" >
		    <input type="hidden" name="dosave" value="yes"> '.
          '<code id="content" wrap="off" title=".'.$lang.'" class="cp hideLanguage" style="height: 500px; color: silver; visibility: visible;">'.t3lib_div::formatForTextarea($fileContent).'</code>'
			.'<textarea id="content_ta" name="content_ta" rows="'.$theight.'" wrap="off" style="width: 460px; display: none;" class="cp fixed-font enable-tab">'.t3lib_div::formatForTextarea($fileContent).'</textarea>';

      }



		$content[]= '
			  <br />
		      <table>
			    	<tr>
				  		<td>
				    		<input type="hidden" name="fname" value="'.$item.'">
				  		</td>
		          		<td>
				    		<input type="submit" name="savenow" value="'.$LANG->getLL("message.btnsave").'" >
				  		</td>
				  		<td>
		            <input type="button" value="'.$LANG->getLL("message.btnclose").'" onclick="closeDoc()">
				  		</td></tr></table><br />';





			$fileT3s = $fname;
			if(@is_file($fileT3s)){
			require_once (t3lib_extMgm::extPath("t3quixplorer")."mod1/geshi.php");

			  $inputCode = file_get_contents($fileT3s);



				switch($ext){
					case 'php':
					case 'php3':
					case 'inc':
						$hl = 'php';
						break;

					case 'html':
					case 'htm':
					case 'tmpl':
						$hl = 'html4strict';
						break;

					case 'js':
						$hl = 'javascript';
						break;

					case 'pl':
						$hl = 'perl';
						break;

					default:
						$hl = $ext;
						break;
				}

            if($item == 'setup.txt' || $item == 'config.txt'|| $item == 'constants.txt'){
            	$hl = 'ts';
            }
			switch($hl){
				case 'php':
				case 'xml':
				case 'sql':
				case 'html4strict':
				case 'javascript':
				case 'perl':
				case 'css':
				case 'smarty':
					$geshi = new GeSHi($inputCode,$hl,'geshi/');
					$geshi->use_classes = true;
					$geshi->set_tab_width(4);
					$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
					$geshi->set_link_target('_blank');
					$geshi->set_line_style("font-family:'Courier New', Courier, monospace; color: black; font-weight: normal; font-style: normal;font-size:12px;");

					$content[] = '
					<style type="text/css">

					.'.$hl.' *{font-size:11px;}

					'.$geshi->get_stylesheet().'
					</style>
					';

					$content[] = '<strong>T3S-File: '.$fileT3s.'</strong></br><hr />'.$geshi->parse_code();

					break;
				case 'ts':
					require_once(PATH_t3lib.'class.t3lib_tsparser.php');
					$tsparser = t3lib_div::makeInstance("t3lib_TSparser");
					$tsparser->lineNumberOffset=1;
					$formattedContent = $tsparser->doSyntaxHighlight($inputCode, array($tsparser->lineNumberOffset), 0);
					$content[]='<strong>T3S-File: '.$fileT3s.'</strong></br><hr />'.$formattedContent;
					break;
				default:
					break;
			}
			}

		return implode("",$content);
	}
}
?>