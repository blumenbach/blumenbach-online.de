<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2008 Denis Royer (info@indigi.de)
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
* Module 'Wiki Admin' for the 'dr_wiki' extension.
*
* @author Denis Royer <info@indigi.de>
*
* The idea and parts of the code for this plugin came from
* several sources, including:
*
*  The initial version f 2004/2005 was based on the "a1_wiki" extension 
*  by Mirko Balluff <balluff@miba-edv.de>
*  
*  "blastwiki" at http://www.roboticboy.com/blast/
*
*  Rendering engine is based on MediWiki available at
*  "wikimedia" at http://www.mediawiki.org
*
*  DBAL patch provided by Henning Schild
*
*/

// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");

$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_drwiki_module1 extends t3lib_SCbase {
	var $pageinfo;
        
	/**
	 * init
	 */
	function init()	{
		global $AB,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			"function" => Array (
				"1" => $LANG->getLL("function1"),
                                "2" => $LANG->getLL("function2"),
                                //"3" => $LANG->getLL("function3"),
			)
		);
		parent::menuConfig();
	}

        // If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the 
        // page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()	{
		global $AB,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		
		
		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{
	
				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript">
					script_ended = 1;
					if (top.theMenu) top.theMenu.recentuid = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->php3Lang["labels"]["path"].": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content.=$this->doc->divider(5);
			
			// Render content:
			$this->moduleContent();

			
			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}
		
			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero
		
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{
		global $SOBE;

		$this->content.=$this->doc->middle();
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	/**
	 * Generates the module content
	 */
	function moduleContent()	{
        global $LANG;
		switch((string)$this->MOD_SETTINGS["function"])	{
			case 1:
				$content=$LANG->getLL("msg_start");
				$this->content.=$this->doc->section($LANG->getLL("admin"),$content,0,1);
				
				$cmd = trim($GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["cmd"]);
				if($cmd){
					switch($cmd){
						case "check_delete_keyword":
							// display dialog to ask user if he realy want do delete the keyword
							$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
							$contentKeyword = '<div align="center" style="background-color: yellow;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("cmd_delAll").' <br /><em>[' .$keyword . ']</em><br><br>'; 
							$contentKeyword .= '<a href="index.php?id='.$this->id.'&tx_drwiki_mod1[keyword]='.$keyword.'&tx_drwiki_mod1[cmd]=delete_keyword">'.$LANG->getLL("yes").'</a>&nbsp;<a href="index.php?id='.$this->id.'&tx_drwiki_mod1[keyword]='.$keyword.'#versions">'.$LANG->getLL("no").'</a><br><br></div>';
						break;
						case "delete_keyword":
							// delete all versions of selected keyword
							$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
							$query = "update tx_drwiki_pages set deleted='1' where pid=" . $this->id . " and keyword='" . $keyword . "'";
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							if (!$GLOBALS['TYPO3_DB']->sql_error()){
								$resCount = $GLOBALS['TYPO3_DB']->sql_affected_rows();
								$contentKeyword = '<div align="center" style="background-color: #00ff00;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("cmd_delAllDone").' <br /><em>[' .$keyword . '"]</em><br><br></div>';
							}else{
									$contentKeyword = '<div align="center" style="background-color: red;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("err_delAll").' <br /><em>[' .$keyword . ']</em><br><br></div>';
							}
						break;
						case "lock":
							// lock the page
							$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
							$query = "update tx_drwiki_pages set locked = '1' where uid = " . $uid;
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							if ($GLOBALS['TYPO3_DB']->sql_error()){
								$contentVersion = '<div align="center" style="background-color: red;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("err_noLock").'<br /><br /></div>';
							}
						break;
						case "unlock":
							// unlock the page
							$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
							$query = "update tx_drwiki_pages set locked = '0' where uid = " . $uid;
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							if ($GLOBALS['TYPO3_DB']->sql_error()){
								$contentVersion = '<div align="center" style="background-color: red;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("err_noUnlock").'<br><br></div>';
							}
						break;
						case "check_delete_uid":
							// ask user if he wants to delete one single version
							$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
							$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
							$contentVersion = '<div align="center" style="background-color: yellow;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("cmd_delSingleUid").' <br /><em>[ID:'  . $uid . ' - ' . $keyword . ']</em><br><br>';
							$contentVersion .= '<a href="index.php?id='.$this->id.'&tx_drwiki_mod1[keyword]='.$keyword.'&tx_drwiki_mod1[uid]='.$uid.'&tx_drwiki_mod1[cmd]=delete_uid#versions">'.$LANG->getLL("yes").'</a>&nbsp;<a href="index.php?id='.$this->id.'&tx_drwiki_mod1[keyword]='.$keyword.'&tx_drwiki_mod1[uid]='.$uid.'#versions">'.$LANG->getLL("no").'</a><br><br></div>';

						break;
						case "delete_uid":
							// delete a single version
							$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
							$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
							$query = "update tx_drwiki_pages set deleted='1' where uid=" . $uid;
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							if (!$GLOBALS['TYPO3_DB']->sql_error()){
								$resCount = $GLOBALS['TYPO3_DB']->sql_affected_rows();
								$contentVersion = '<div align="center" style="background-color: #00ff00;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("cmd_delSingleUidDone").' <br /><em>[ID:' . $uid . ' - ' . $keyword . ']</em><br><br></div>';
							}else{
								$contentVersion = '<div align="center" style="background-color: red;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("err_delSingleUid").' <br /><em>[ID:' . $uid . ' - ' . $keyword . ']</em><br><br></div>';
							}
						break;
				
						case "check_delete_older":
							// ask the user if he wants to delete older versions
							$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
							$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
							$contentVersion = '<div align="center" style="background-color: yellow;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("cmd_delOlderUid").' <br /><em>[ID:' . $uid . ' - ' . $keyword . ']</em> <br><br>';
							$contentVersion .= '<a href="index.php?id='.$this->id.'&tx_drwiki_mod1[keyword]='.$keyword.'&tx_drwiki_mod1[uid]='.$uid.'&tx_drwiki_mod1[cmd]=delete_older#versions">'.$LANG->getLL('yes').'</a>&nbsp;<a href="index.php?id='.$this->id.'&tx_drwiki_mod1[keyword]='.$keyword.'&tx_drwiki_mod1[uid]='.$uid.'#versions">'.$LANG->getLL('no').'</a><br><br></div>';

						break;
						case "delete_older":
							// delete all older versions
							$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
							$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
							$query = "update tx_drwiki_pages set deleted='1' where uid < " . $uid . " and pid = " . $this->id . " and keyword = '" . $keyword . "' and not deleted";
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							if (!$GLOBALS['TYPO3_DB']->sql_error()){
								$resCount = $GLOBALS['TYPO3_DB']->sql_affected_rows();
								$contentVersion = '<div align="center" style="background-color: #00ff00;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("cmd_delOlderUidDone").' <br /><em>[ID:' . $uid . ' - ' . $keyword . ']</em><br><br></div>';
							}else{
								$contentVersion = '<div align="center" style="background-color: red;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("err_delOlderUid").' <br /><em>[ID:' . $uid . ' - ' . $keyword . ']</em><br><br></div>';
							}
						break;
						
						case "check_delete_other":
							// ask the user if he want to delete all versions but the current
							$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
							$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
							$contentVersion = '<div align="center" style="background-color: yellow;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("cmd_delAllOther").' <br /><em>[ID:' . $uid . ' - ' . $keyword . ']</em><br><br>';
							$contentVersion .= '<a href="index.php?id='.$this->id.'&tx_drwiki_mod1[keyword]='.$keyword.'&tx_drwiki_mod1[uid]='.$uid.'&tx_drwiki_mod1[cmd]=delete_other#versions">'.$LANG->getLL("yes").'</a>&nbsp;<a href="index.php?id='.$this->id.'&tx_drwiki_mod1[keyword]='.$keyword.'&tx_drwiki_mod1[uid]='.$uid.'#versions">'.$LANG->getLL("no").'</a><br><br></div>';

						break;
						case "delete_other":
							// delete all but current version
							$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
							$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
							$query = "update tx_drwiki_pages set deleted='1' where (uid < " . $uid . " or uid > " . $uid . ") and pid = " . $this->id . " and keyword = '" . $keyword . "' and not deleted";
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							if (!$GLOBALS['TYPO3_DB']->sql_error()){
								$resCount = $GLOBALS['TYPO3_DB']->sql_affected_rows();
								$contentVersion = '<div align="center" style="background-color: #00ff00;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("cmd_delAllOtherDone").' <br /><em>[' . $resCount .' x ' . $keyword . '"]</em><br><br></div>';
							}else{
								$contentVersion = '<div align="center" style="background-color: red;border-width: 1px; border-color: black; border-style:solid;"><bR>'.$LANG->getLL("err_delAllOther").' <br /><em>[ID:' . $uid . ' - ' . $keyword . ']</em><br><br></div>';
							}
						break;
					}
				}
	 			// get all keywords
	 			$query = "select distinct keyword from tx_drwiki_pages where pid = " . $this->id . " and not deleted order by keyword asc";
	 			
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);
				if (!$GLOBALS['TYPO3_DB']->sql_error()){
					//debug(array($GLOBALS['TYPO3_DB']->sql_error(),$query));
				}
				
				$keywords = array();
				while($keyword = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
					$keywords[] = $keyword["keyword"];
				}
				
				// display keywords
				$contentKeyword .= "<table>";
				reset($keywords);
				while(list(,$keyword) = each($keywords)){
					$contentKeyword .= '<tr><td valign="top"><a href="index.php?id=' . $this->id . '&tx_drwiki_mod1[keyword]='.$keyword.'#versions">'.$keyword.'</a></td><td>&nbsp;<a href="index.php?id=' . $this->id . '&tx_drwiki_mod1[keyword]='.$keyword.'#versions"><img src="../res/mod1/view.png" border="0" title="'.$LANG->getLL("alt_VIEW").'" alt="'.$LANG->getLL("alt_VIEW").'"></a>&nbsp;<a href="index.php?id=' . $this->id . '&tx_drwiki_mod1[keyword]='.$keyword.'&tx_drwiki_mod1[cmd]=check_delete_keyword"><img src="../res/mod1/delete.png" border=0 title="'.$LANG->getLL("alt_DELETE").'" alt="'.$LANG->getLL("alt_DELETE").'"></td></tr>';
				}
				$contentKeyword .= "</table>";
				
				
				$this->content.=$this->doc->section($LANG->getLL("wiki_word"),$contentKeyword,0,1);
				
				
				// Get/Display selected versions
				$keyword = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["keyword"];
				if($keyword){
					$query = "select uid,keyword,author,tstamp,locked, summary from tx_drwiki_pages where not deleted and pid = " . $this->id . " and keyword = '" . $keyword . "' order by uid desc";
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);
					//if ($GLOBALS['TYPO3_DB']->sql_error()) debug(array($GLOBALS['TYPO3_DB']->sql_error(),$query));
					
					$versions = array();
					while($version = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
						$versions[$version["uid"]] = $version;
					}
					
					$contentVersion .= "<table cellpadding='2' cellspacing='2'>";
					$contentVersion .= '<tr><td><b>'.$LANG->getLL("header_ID").'</b></td><td><b>'.$LANG->getLL("header_AUTHOR").'</b></td><td><b>'.$LANG->getLL("header_DATE").'</b></td><td><b>'.$LANG->getLL("header_OPTIONS").'</b></td><td><b>'.$LANG->getLL("header_SUMMARY").'</b></td></tr>';
					
					reset($versions);
					while(list($uid,$version) = each($versions)){
						$contentVersion .= '<tr><td><a href="index.php?id=' .$this->id . '&tx_drwiki_mod1[keyword]=' . $keyword . '&tx_drwiki_mod1[uid]='.$version["uid"].'#content">'.$version["uid"].'</a></td><td>'.$version["author"].'</td><td>'.strftime("%d.%m.%Y %H:%M",$version["tstamp"]).'</td><td>&nbsp;<a href="index.php?id=' .$this->id . '&tx_drwiki_mod1[keyword]=' . $keyword . '&tx_drwiki_mod1[uid]='.$version["uid"].'#content"><img src="../res/mod1/view.png" border=0 title= "'.$LANG->getLL("alt_VIEW").'" alt="'.$LANG->getLL("alt_VIEW").'"></a>&nbsp;';
						$contentVersion .= ($version["locked"] ? "<a href=\"index.php?id=" .$this->id . "&tx_drwiki_mod1[keyword]=" . $keyword . "&tx_drwiki_mod1[uid]=".$version["uid"]."&tx_drwiki_mod1[cmd]=unlock#versions\"><img src=\"../res/mod1/unlock.png\" border=0 alt=\"".$LANG->getLL("alt_UNLOCK")."\"></a>":"<a href=\"index.php?id=" .$this->id . "&tx_drwiki_mod1[keyword]=" . $keyword . "&tx_drwiki_mod1[uid]=".$version["uid"]."&tx_drwiki_mod1[cmd]=lock#versions\"><img src=\"../res/mod1/lock.png\" border=0 title=\"".$LANG->getLL("alt_LOCK")."\" alt=\"".$LANG->getLL("alt_LOCK")."\"></a>");
						$contentVersion .= '&nbsp;<a href="index.php?id=' .$this->id . '&tx_drwiki_mod1[keyword]=' . $keyword . '&tx_drwiki_mod1[uid]='.$version["uid"].'&tx_drwiki_mod1[cmd]=check_delete_uid#versions"><img src="../res/mod1/delete.png" border=0 title="'.$LANG->getLL("alt_DELETE").'" alt="'.$LANG->getLL("alt_DELETE").'"></a>&nbsp;';
						$contentVersion .= '<a href="index.php?id=' .$this->id . '&tx_drwiki_mod1[keyword]=' . $keyword . '&tx_drwiki_mod1[uid]='.$version["uid"].'&tx_drwiki_mod1[cmd]=check_delete_older#versions"><img src="../res/mod1/delete_older.png" border=0 title="'.$LANG->getLL("alt_DELOLDER").'" alt="'.$LANG->getLL("alt_DELOLDER").'"></a>&nbsp;';
						$contentVersion .= '<a href="index.php?id=' .$this->id . '&tx_drwiki_mod1[keyword]=' . $keyword . '&tx_drwiki_mod1[uid]='.$version["uid"].'&tx_drwiki_mod1[cmd]=check_delete_other#versions"><img src="../res/mod1/delete_all_but_current.png" border=0 title="'.$LANG->getLL("alt_DELOTHERS").'" alt="'.$LANG->getLL("alt_DELOTHERS").'"></a></td>';
                                                $contentVersion .= '<td><em>[ '.$version["summary"].' ]</em></td></tr>';
					}
					
					$contentVersion .= "</table>";
					
					$this->content.=$this->doc->section($LANG->getLL("versionsof") . $keyword ,'<a name="versions">'.$contentVersion,0,1);	
					
				}else{
					$contentVersion .= $LANG->getLL("msg_versions") ."<br>";
					$this->content.=$this->doc->section($LANG->getLL("versions"),'<a name="versions">'.$contentVersion,0,1);	
				}
				
				// Get/Display selected content
				$uid = $GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["uid"];
				
                                
                                $content = "";
				if($uid){
					$query = "select body from tx_drwiki_pages where not deleted and uid=" . $uid;
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);
					//if ($GLOBALS['TYPO3_DB']->sql_error()) debug(array($GLOBALS['TYPO3_DB']->sql_error(),$query));
					
					$body = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$content .= $this->parse($body["body"]);
					
					$this->content.=$this->doc->section($LANG->getLL("contentof") . $keyword . "(" . $uid . ")",'<a name="content">'.$content,0,1);
					
				}else{
					$content .= $LANG->getLL("msg_edititem") . "<br>";
					
					$this->content.=$this->doc->section($LANG->getLL("content"),'<a name="content">'.$content,0,1);	
				}
				$content = "";
				
					
			break;
                        // Handle Caches
			case 2:
                $cmd = trim($GLOBALS["HTTP_GET_VARS"]["tx_drwiki_mod1"]["cmd"]);
                
                if(!$cmd){
                    $content = '<div align=center><strong><a href="index.php?id=' .$this->id .'&tx_drwiki_mod1[cmd]=clear_cache">Clear ALL Caches!</a></strong></div>';
                    $this->content.=$this->doc->section($LANG->getLL("function2"),$content,0,1);
				}
				
				if($cmd){
					switch($cmd){
						case "clear_cache":
							// display dialog to ask user if he realy want do delete the keyword
                            $query = "TRUNCATE TABLE tx_drwiki_cache";
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            if ($GLOBALS['TYPO3_DB']->sql_error()){
                                    debug(array($GLOBALS['TYPO3_DB']->sql_error(),$query));
                            }
                                                        
							$content = 'Caches cleared...';
                        	$this->content.=$this->doc->section($LANG->getLL("function2"),$content,0,1);
						break;
                    }
                }
			break;
                        // Not yet used
			case 3:
				$content="<div align=center><strong>This feature is currently under construction!</strong></div>";
				$this->content.=$this->doc->section($LANG->getLL("function3"),$content,0,1);
			break;
		} 
	}
	
        // TODO: add switch here
	function realsafehtml($str) {
		// same function as in the plugin
		
	    	// Don't do anything if there's no difference or if the original string is empty
    		$oldstr = "";

    		while($str != $oldstr) // Loop until it got no more effect
    		{
		        $oldstr = $str;
		        //nuke script and header tags and anything inbetween
		        $str = preg_replace("'<script[^>]*?>.*?</script>'si", "", $str);
		      	$str = preg_replace("'<head[^>]*?>.*?</head>'si", "", $str);
		               
		        //listed of tags that will not be striped but whose attributes will be
		        $allowed = "br|b|i|p|u|a|center|hr";
		        //start nuking those suckers. don you just love MS Word's HTML?
		        $str = preg_replace("/<((?!\/?($allowed)\b)[^>]*>)/xis", "", $str);
		        $str = preg_replace("/<($allowed).*?>/i", "<\\1>", $str);
	    	}
           return $str;
	}
	
	function parse($str) {
		// same function as in the plugin
		
	 	$str = $this->realsafehtml($str);
	 	$str = $this->substituteBlocks($str);
		$pattern = array('([\n][--][-]+)',  // bar
                         '|(___)(.*?)(___)|', // bold&italic
	                	 '|(__)(.*?)(__)|', // bold
	                	 '|(_)(.*?)(_)|', // italic
                         '|\[(===)(.*?)(===)\]|', // h2
                         '|\[(==)(.*?)(==)\]|', // h2
                         '|\[(=)(.*?)(=)\]|', // h1
	                	 '/\[(.*)(.png|.png|.gif|.jpe?g)\]/', // images
  				         '#(^|[^\"=]{1})(http://|ftp://|mailto:|news:)([^\s<>]+)([\s\n<>]|$)#sm', // links
  				         '/(([^\s<>]+))(@)([^\s<>]+)\b/i', // mail-Links
	                	 "/\[\[(.*?)\]\]/e", // Wiki-Links
	                	 '|\r\n|', // br
	                	 '|<monobr>|'); // br in pre-block, generated in substituteBlocks
	  	$replace = array('<hr size="1" width="100%">',
	                	 '<b><i>\2</i></b>',
                         '<b>\2</b>',
	                	 '<i>\2</i>',
                         '<h3>\2</h3>',
                         '<h2>\2</h2>',
                         '<h1>\2</h1>',
	                	 '<img src="\1\2">',
  				         '\\1<a href="\\2\\3" target="_blank">\\2\\3</a>\\4',
  				         '<a href="mailto:\\0">\\0</a>',
	                	 'eval(\'return $this->isKeyword("$1");\')',
	                	 '<br>',
	                	 '' . "\r\n");

	                	   
	  	$str = preg_replace($pattern, $replace, $str);
	  	
	  	return $str;
	 }
	 
	 function isKeyword($str){
	 	// we dont want to simulate the real wiki here, so we simply display the string and don't
	 	// link it
	 	
	 	return "<b>" . $str . "</b>";
	 }
	 
	 function substituteBlocks($str){
	 	// same function as in plugin
	 	
		$strArray = explode("\r\n", $str);
		
		while (list(,$row) = each($strArray)){
			if($row){
				if(!$ul0 && strpos($row, "*") === 0){
					if($mono){
						$out .= "</pre>";
						$mono = false;
					}
					
					$ul0 = true;
					$out .= "<ul>";
					$row = "<li>" . substr($row,1);	  
				}elseif($ul0 && substr($row,0,1) != "*"){
					$ul0 = false;
					$out .= "</ul>";
				}elseif($ul0){
					$row = "<li>" . substr($row,1);
				}
				
				if(!$mono && strpos($row, " ") === 0){
					if($ul0){
						$out .= "</ul>";
						$ul0 = false;	
					}
					
					$mono = true;
					$out .= "<pre>";
					$row =  substr($row,1);
				}elseif($mono && substr($row,0,1) != " "){
					$mono = false;
					$out .= "</pre>";
				}elseif($mono){
					$row = substr($row,1);
				}	
			}
			
			if(!$mono){
				$out .= $row . "\r\n";
			}else{
			 	$out .= $row . "<monobr>";
			}
		}
		if($ul0) $out .= "</ul>";
		if($mono) $out .= "</pre>";
		
		return $out;
	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dr_wiki/mod1/index.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dr_wiki/mod1/index.php"]);
}
// Make instance:
$SOBE = t3lib_div::makeInstance("tx_drwiki_module1");
$SOBE->init();
// Include files?
reset($SOBE->include_once);	
while(list(,$INC_FILE)=each($SOBE->include_once))	{include_once($INC_FILE);}
$SOBE->main();
$SOBE->printContent();

?>