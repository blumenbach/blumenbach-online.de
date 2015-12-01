<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2009 Denis Royer (info@indigi.de)
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
* Extension 'DR Wiki' for the 'dr_wiki' extension.
*
* @author Denis Royer <info@indigi.de>
*
* I've written this plugin for the EU founded research project FIDIS (www.fidis.net)
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

    // get path of the extension
    $ext_path = t3lib_extMgm::extPath('dr_wiki');
    require_once(PATH_tslib.'class.tslib_pibase.php'); 
    //Load HTML Sanitizer Class
    require_once(t3lib_extMgm::extPath('dr_wiki').'lib/class.html_sanitizer.php');
    //Load Index List formattinf Class for Categories
    require_once(t3lib_extMgm::extPath('dr_wiki').'lib/class.wiki.createIndexList.php');
    //Load Mailer Extension
    require_once (PATH_t3lib.'class.t3lib_htmlmail.php');

    // Ratings API if enabled;
	if (t3lib_extMgm::isLoaded('ratings')) {
		require_once(t3lib_extMgm::extPath('ratings', 'class.tx_ratings_api.php'));
	}
   
    // Load plugin list for the dr_wiki
    include(dirname(__FILE__) .'/plugins/plugincfg.php');

    class tx_drwiki_pi1 extends tslib_pibase {
        // Same as class name
		var $prefixId = "tx_drwiki_pi1";
        // Path to this script relative to the extension dir.
        var $scriptRelPath = "pi1/class.tx_drwiki_pi1.php"; 
        var $extKey = "dr_wiki"; // The extension key.
        var $sitePath = "";
		var $drWikiVersion = 'dr_wiki Version 1.8.2';
        // PID for record storage - 0 is default
        var $storagePid = 0;

        // Array of installed Plugins
        var $pluginArray = array();
        var $pluginList = '';
        // Code of the template
        var $templateCode = "";
        // Global Configuration Array for TS configuration
        var $conf = array();
        // Global Configuration Array for FlexForm configuration
        var $ffConf = array();
        // Contains the cacheable version of the page
        var $cacheContents = "";
        // array and variables for parsing blocks (ul, etc.) and headers
        var $mDTopen = array();
        var $mLastSection;
        var $mInPre;
        var $mAutonumber;
        // List of allowed HTML tags --> see template config
        var $allowedHTML = '<a><br><b><h1><h2><h3><h4><h5><h6><img><li><ol><p><strong><table><tr><td><th><u><ul><thead><tbody><tfoot><em><dd><dt><dl><span><div><del><add><i><hr><pre><br><blockquote><address><code><caption><abbr><acronym><cite><dfn><q><ins><sup><sub><kbd><samp><var><tt><small><big>';
        //allowed WIKI Markups
        var $allowedWIKI ='<ref><noinclude><references2col><references>';
        // Target for external Links --> see template config
        var $extLinkTarget;
        // Maximum level of headings to create TOC
        var $maxTocLevel;
        // Minimum amount for Headers to be displayed in TOC
        var $minHeaderCount;
        // Number headings on a wiki page?
        var $doNumberHeadings;
        // Activate Toc? Default: true (see main() for details)
        var $doShowToc;
        // divider between the article and the discussion heading [Article | Discussion]
        var $divDiscussion = " | ";
        // Namespace for the discussions
        var $keyDiscussion = "Discussion:";
        // global variable for the redirect link warning used for redirects
        var $redirectLink = "";
        // Link to be added to body of wiki page if the wiki word is a discussion
        // expl.: Discussion:WikiWord --> WikiWord
        var $backToKeywordDiscussion = "";
        // UserID used when no IP or FE-Login is present
        var $anonymousUser = "[<em>Anonymous</em>]";
        // De-/Activate the caching of wiki-pages
        var $enableWikiCaching;
        // Is the wiki a read-only version? (Default: false)
        var $read_only;
        // turn off link Images?
        var $turnOffImageLink;
        //  Redirect edit-user to login page if not loged-in?
        var $activateAccessControl;
        var $allowedGroups;
        var $disallowedGroups;
        var $pageRedirect;
        var $adminUserGroup;
        
        // footer and header
        var $wikiFooter = ''; var $wikiHeader = '';
        // page template
        var $initialPageText1Name = ''; var $initialPageText1 = '';
        var $initialPageText2Name = ''; var $initialPageText2 = '';
        var $initialPageText3Name = ''; var $initialPageText3 = '';

        //Editor config
        var $numberRows;
        var $numberColumns;
        var $wrapLinesOn;
        var $charSummary;
        var $initSummary = 'N/A';
        //TODO: transfer to array
        var $noTemplates = 'IMAGE_[^>]*?|CONTRIBUTINGAUTHORS|ALLCONTRIBUTINGAUTHORS|EDITPAGEICON|EDITPAGELINK|CURRENTUSER|NUMBEROFARTICLES|VERSION|PAGEAUTHOR|CURRENTTIME|CURRENTYEAR|CURRENTDAYNAME|CURRENTDAY|CURRENTMONTHNAMEGEN|PAGENAME|GETDISCUSSIONLINK|REVISIONID|NAMESPACE|DATE|SWATCHBEATS|CURRENTMONTH|CURRENTMONTHNAME';
        var $legalChars = ' %!"$&\'()*,\\-.\\/0-9:;=?@A-Z\\\\^_`a-z~\\x80-\\xFF';
        
        // Default Page to display
        var $wikiHomePage = "HomePage";
        
        //html sanitizer object 
        var $sanitizer;
        
        //Index List formatting object
        var $indexListFormatter;
        
        // Definition of Namespaces and InterWiki links
        // Todo: Integrate Namespaces and expand configuration array (ext, hide NS)
       var $currentNameSpace = ""; 
        
        var $nameSpaces = array(
            'ISBN' => 'http://www.amazon.com/exec/obidos/ASIN/',
            'IMDB' => 'http://www.imdb.com/find?q=',
            'Wikipedia' => 'http://en.wikipedia.org/wiki/',
            'Discussion' => 'Discussion', // internal NS
            'User' => 'User', // internal NS
            'Help' => 'Help', // internal NS
            'Category' => 'Category', // internal NS
            'Template' => 'Template', // internal NS
            );
            
        var $categoryIndex = array();
        
        //ratings API Object
        var $ratingsApiObj=null;
        
    /**
     * The Main Function
     *
     * Loads and sets global configuration and calls singleView or showWiki to display
     * the whole wiki or a single Version
     *
     * @param	[type]		$content: ...
     * @param	[type]		$conf: ...
     * @return	[type]		...
     */
        function main($content, $conf)
        {
            // Configuring so caching is not expected. This value means that no cHash
            // params are ever set. We do this, because it's a USER_INT object!
            $this->pi_USER_INT_obj=1;
            
             // Local cObj.
            $this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
            
            // Disable Caching
            $GLOBALS["TSFE"] -> set_no_cache();
            // uncomment to enable debug-output of failed queries
            $GLOBALS['TYPO3_DB']->debugOutput = FALSE;
            
            // Initialise plug-in array
            $myPluginArray  = new tx_drwiki_pi1_plugin();
            
            // Initialise Index List formatting object
            $this->indexListFormatter = new tx_drwiki_pi1_indexListFormatter();
            
            // Load language and initialise local language settings
            $this->pi_loadLL();
            
            // get configuration from TYPO3 TCA/Flexform and initialise extension
            $this->conf = $conf;
            $this->pi_setPiVarDefaults();
            $this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
            
            $this->sanitizer =  new html_sanitizer;
            //initialte HTML sanitizer
            //TODO: Add to configuration
            $this->sanitizer->addAllowedTags($this->allowedHTML);
            $this->sanitizer->addAdditionalTags($this->allowedWIKI);
            $this->sanitizer->allowStyle();
            
            // get rid of XSS exploits by sanitising the piVars
            // tx_drwiki_pi1[keyword]=<script>alert("doh")</script> and
            // tx_drwiki_pi1[key"><SCRIPT%3Ealert("XSS")</SCRIPT>word]=HomePage
            $this->piVars = $this->sanitizeValues($this->piVars);
            
            // Assign the flexform data to a local variable for easier access
            $piFlexForm = $this->cObj->data['pi_flexform'];
            
            // Traverse the entire array based on the language...
            // and assign each configuration option to $this->ffConf array...
            if ($piFlexForm) {
              foreach ( $piFlexForm['data'] as $sheet => $data )
               foreach ( $data as $lang => $value )
                foreach ( $value as $key => $val )
                 $this->ffConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
            }
            
            // Merge TS and FlexForm values
            // Priority goes to the FlexForms values!
            
            $this->minHeaderCount = $this->ffConf["minHeaderCount"] ? $this->ffConf["minHeaderCount"] : $conf['toc.']['minHeaderCount'];
            $this->maxTocLevel = $this->ffConf["maxTocLevel"] ? $this->ffConf["maxTocLevel"] : $conf['toc.']['maxTocLevel'];
            $this->enableWikiCaching = $this->ffConf["disableWikiCaching"] ? false : true;
            $this->doNumberHeadings = $this->ffConf["disableDoNumberHeadings"] ? false : true;
            $this->doShowToc = $this->ffConf["disable_toc"] ? false : true;
            $this->read_only = $this->ffConf["read_only"] ? true : false;
            $this->extLinkTarget = $this->ffConf["extLinkTarget"] ? $this->ffConf["extLinkTarget"] : "_blank";
            $this->turnOffImageLink = $this->ffConf["turnOffImageLink"] ? true : false;
            
            // Template insertion
            $this->initialPageText1Name = $this->ffConf["initialPageText1Name"] ? $this->ffConf["initialPageText1Name"] : "Template 1";
            $this->initialPageText1 = $this->ffConf["initialPageText1"] ? $this->ffConf["initialPageText1"] : "no template defined";
            $this->initialPageText1 = addcslashes($this->initialPageText1, "\n,\',\"");
            $this->initialPageText2Name = $this->ffConf["initialPageText2Name"] ? $this->ffConf["initialPageText2Name"] : "Template 2";
            $this->initialPageText2 = $this->ffConf["initialPageText2"] ? $this->ffConf["initialPageText2"] : "no template defined";
            $this->initialPageText2 = addcslashes($this->initialPageText2, "\n,\',\"");
            $this->initialPageText3Name = $this->ffConf["initialPageText3Name"] ? $this->ffConf["initialPageText3Name"] : "Template 3";
            $this->initialPageText3 = $this->ffConf["initialPageText3"] ? $this->ffConf["initialPageText3"] : "no template defined";
            $this->initialPageText3 = addcslashes($this->initialPageText3, "\n,\',\"");
            $this->activateInitialPageText = $this->ffConf["activateInitialPageText"] ? true : false;
            
            // Footer an Header
            $this->wikiHeader = $this->ffConf["wikiHeader"] ? $this->ffConf["wikiHeader"] : "";
            $this->wikiFooter = $this->ffConf["wikiFooter"] ? $this->ffConf["wikiFooter"] : "";
            
            //add mail stuff mailHideItem
            $this->mailNotify  = $this->ffConf["mailNotify"] ? true : false;
            $this->mailHideItem  = $this->ffConf["mailHideItem"] ? false : true;
            $this->mailRecipient  = $this->ffConf["mailRecipient"] ? $this->ffConf["mailRecipient"] : "";
            $this->mailFromEmail  = $this->ffConf["mailFromEmail"] ? $this->ffConf["mailFromEmail"] : "";
            $this->mailFromName  = $this->ffConf["mailFromName"] ? $this->ffConf["mailFromName"] : "";
            $this->mailSubject  = $this->ffConf["mailSubject"] ? $this->ffConf["mailSubject"] : "";
            
            // Editor Config
            $this->numberRows = $this->ffConf["numberRows"] ? $this->ffConf["numberRows"] : $conf['editorConfig.']['numberRows'];
            $this->numberColumns = $this->ffConf["numberColumns"] ? $this->ffConf["numberColumns"] : $conf['editorConfig.']['numberColumns'];
            $this->wrapLinesOn = $this->ffConf["wrapLinesOn"] ? "on" : "off";
            $this->charSummary = $this->ffConf["charSummary"] ? $this->ffConf["charSummary"] : $conf['editorConfig.']['charSummary'];

            // Settings for Write Access control. Replaces older "Redirect settings".
            // The admin can decide who can write to the WIKI. 
            // The user gets redirected to $this->pageRedirect
            $this->activateAccessControl = $this->ffConf["activateAccessControl"] ? $this->ffConf["activateAccessControl"] : false;
            $this->allowedGroups = $this->ffConf["allowedGroups"] ? $this->ffConf["allowedGroups"] : false;
            $this->disallowedGroups = $this->ffConf["disallowedGroups"] ? $this->ffConf["disallowedGroups"] : false;
            $this->pageRedirect = $this->ffConf["pageRedirect"] ? $this->ffConf["pageRedirect"] : false;
            $this->adminUserGroup = $this->ffConf["adminUserGroup"] ? $this->ffConf["adminUserGroup"] : false;
            
            // Initialise extension template: Use std template if no FlexForm template is set:
            $this->templateCode = $this->ffConf["templatefile"] ? $this->cObj->fileResource("uploads/tx_drwiki/".$this->ffConf["templatefile"]) : $this->cObj->fileResource($this->conf["templateFile"]);
            
            // Set HomePage / KeyWord
            $this->wikiHomePage = $this->ffConf["setHomePage"] ? $this->ffConf["setHomePage"] : $this->wikiHomePage;
            
            // Set site-path for images
            $this->sitePath = t3lib_extMgm::siteRelPath('dr_wiki');
            
            // load allowedHTML
            $this->allowedHTML = $this->allowedHTML ? $this->allowedHTML : $this->cObj->stdWrap($this->conf["allowedHTML"],$this->conf["allowedHTML."]);            

            // Global content-replaces
            list($globalMarkerArray["###GW1B###"], $globalMarkerArray["###GW1E###"]) = explode("|", $this->conf["wrap1"]);
            list($globalMarkerArray["###GW2B###"], $globalMarkerArray["###GW2E###"]) = explode("|", $this->conf["wrap2"]);
            list($globalMarkerArray["###GW3B###"], $globalMarkerArray["###GW3E###"]) = explode("|", $this->conf["wrap3"]);
            $globalMarkerArray["###GC1###"] = $this->cObj->stdWrap($this->conf["color1"], $this->conf["color1."]);
            $globalMarkerArray["###GC2###"] = $this->cObj->stdWrap($this->conf["color2"], $this->conf["color2."]);
            $globalMarkerArray["###GC3###"] = $this->cObj->stdWrap($this->conf["color3"], $this->conf["color3."]);

            // Load templatecode
            $this->templateCode = $this->cObj->substituteMarkerArrayCached($this->templateCode, $globalMarkerArray);
            


            // Not nice, but functional :-) Loads the plugin-array
            // from the global variable $pluginArray defined in plugins/plugincfg.php
            $this->pluginArray = $myPluginArray->getPlugIns();
            $this->pluginList = $myPluginArray->getPluginString();
            
            // Load the ratings API if enabled
	        if (t3lib_extMgm::isLoaded('ratings')) {
				$this->ratingsApiObj = t3lib_div::makeInstance('tx_ratings_api');
			}
			
			// include srfreecap
        	if (t3lib_extMgm::isLoaded('sr_freecap') ) {
				require_once(t3lib_extMgm::extPath('sr_freecap').'pi2/class.tx_srfreecap_pi2.php');
				$this->freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
						
			}
			
            switch((string)$conf["CMD"])
            {
                case "singleView": // displays a single version
                 list($t) = explode(":", $this->cObj->currentRecord);
                 $this->internal["currentTable"] = $t;
                 $this->internal["currentRow"] = $this->cObj->data;
                 return $this->pi_wrapInBaseClass($this->singleView($content, $conf));
                 break;
                
                default: // displays the wiki und stores records under the PID defined in the pages-field
                 if (strstr($this->cObj->currentRecord, "tt_content"))
                 {
                    // Get Page ID - now without explicitly setting it ;-)
                    $this->conf["pidList"] = $this->ffConf["pages"];
                    $this->conf["pidList"] = $this->conf["pidList"] ? implode(t3lib_div::intExplode(",",$this->conf["pidList"]),",") : $GLOBALS["TSFE"]->id;
                    $this->conf["recursive"] = $this->ffConf["recursive"];
                    list($pid) = explode(",",$this->conf["pidList"]);
                    $this->storagePid = $pid;
                 }
                 return $this->pi_wrapInBaseClass($this->showWiki($content, $conf));                
                 break;
            }
        }

    /**
     * getUid
     *
     * Returns the uid of the newest version with the keyword $keyword
     *
     * Parameters: $keyword (string) - The keyword to search for
     *
     * @param	[string]		$keyword: Keyword of the wiki-page to be searched
     * @return	[integer]		UID of the current wiki page for $keyword
     */
        function getUid($keyword)
        {
            $pidList = $this->pi_getPidList($this->conf["pidList"], $this->conf["recursive"]);
            
            $keyword = $GLOBALS['TYPO3_DB']->fullQuoteStr($keyword,'tx_drwiki_pages');

            // The newest version has the highest uid
            // Patch for latest UID Query provided by Kasper M. Petersen
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('MAX(uid) AS uid','tx_drwiki_pages',
            'tx_drwiki_pages.pid IN ('.$pidList.')'.$this->cObj->enableFields("tx_drwiki_pages").' AND keyword='.$keyword,
            '','uid DESC');
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	        return $row["uid"];

        }
        
    /**
     * getDiscussion
     *
     * Returns the uid of the newest version with the keyword Discussion:keyword
     *
     * Parameters: $keyword (string) - The keyword to search for
     *
     * @param	[string]		$keyword: Keyword of the wiki-page to be searched
     * @return	[integer]		UID of the current wiki page for $keyword
     */
        function getDiscussionLink($keyword, $getState=false)
        {
            if ((substr_count($keyword,$this->keyDiscussion)>=1)) {
                if(!$getState) {return "";}
                    else {return false;}
            } else {
                //Todo check keyword if it is a discussion
                $pidList = $this->pi_getPidList($this->conf["pidList"], $this->conf["recursive"]);
                $keyword = $this->nameSpaces["Discussion"].":".$keyword;
                
                // Again, the newest version has the highest uid
                $uid = $this->getUid($keyword); // get uid of discussion page

                if(!$getState)
                {
                    if ($uid) {return $this->pi_linkTP_keepPIvars($keyword, array("keyword" => $keyword, "showUid" => ""), 1, 0);}
                        else {return "";}
                }
                else
                {
                    if  ($uid) {return true;}
                        else {return false;}            
                }
            }
        }
        
    /**
     * showWiki
     *
     * Main class to display the wiki
     *
     * @param	[type]		$content: ...
     * @param	[type]		$conf: ...
     * @return	[type]		...
     */
        function showWiki($content, $conf)
        {
            if ((!$this->piVars["keyword"]) && (!$this->piVars["showUid"]))
            {
                // if we dont know what page to display we take the HomePage

                $this->piVars["keyword"] = $this->wikiHomePage;
            }
			
			//Check if category page exists and create it on the fly. 
			$kewordCategory = $this->getNameSpace($this->piVars["keyword"]);
			if ($kewordCategory == $this->nameSpaces["Category"] AND !$this->keywordExists($this->piVars["keyword"])) {
				$pageContent = array(
	                        'pid' => $this->storagePid,
	                        'crdate' => time(),
	                        'tstamp' => time(),
	                        'summary' => $this->piVars['summary'],
	                        'keyword' => trim($this->piVars['keyword']),
	                        'body' => '={{PAGENAME}}=', //ToDo: Make it FlexForm
	                        'date' => $this->piVars['date'],
	                        'author' => 'Category Creator',
	                    );
	            // HOOK: insert only if hook returns OK or is not set
	            if($this->hook_submit_beforeInsert($pageContent)){
	                $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
	                    'tx_drwiki_pages',
	                    $pageContent
	                );
	        	}
	        	// HOOK: to do something after insert
	        	$this->hook_submit_afterInsert($pageContent);
 			}
			
			
            if (($this->keywordExists($this->piVars["keyword"]) == NULL || $this->piVars["cmd"] == "new") && !$this->piVars["showUid"]) {
                /* the keyword to display doesn't exist or we want to create
                 * a new page and no uid to display is given..
                 * and the wiki is not in read-only mode
                 * ... then we display the createview
                 */
                if (!$this->read_only) {return $this->createView($content, $conf);}
                else {
                    $this->piVars["keyword"] = $this->wikiHomePage;
                    $this->piVars["showUid"] = 0;
                    return $this->singleView($content, $conf);
                }
            } elseif($this->piVars["cmd"] == "edit" && !$this->read_only) {
                // if the user want to edit a page (create a new version) we let him do it...
                return $this->editView($content, $conf);
                
            } elseif($this->piVars["cmd"] == 'lock' && $this->isUserWikiAdmin()){
            	//un-/lock current page and return page-view and reset cmd varaible in piVars
            	$this->piVars["cmd"] = '';
            	$this->togglePageLock($this->piVars["keyword"]);
            	return $this->singleView($content, $conf);
            	
            } elseif($this->piVars["cmd"] == 'activateHidden' && $this->isUserWikiAdmin()){
            	//activate hidden versions from mail notification
            	$this->piVars["cmd"] = '';
            	$myUid = $this->piVars["showUid"];
            	$this->piVars["showUid"]='';
            	$this->activateHiddenItem($myUid);
            	return $this->singleView($content, $conf);
            	
            } else {
                // we know what page/version to display, it exists and the user does not want to edit it
                // => then we simply display it.
                if ($this->piVars["cmd"] == "list" && !$this->isRecordLocked($this->piVars["keyword"]))
                    {
                    // the user want's a version listview
                    return $this->versionListView($content, $conf);
                    
                } else if ($this->piVars["cmd"] == "getHTML") {
                    
                    return $this->getHTMLFile($content, $conf);
                    
                } else {
                    // the user wants to view a single version
                    // Here
                    return $this->singleView($content, $conf);
                }
            }
        }

    /**
     * showList
     *
     * Displays a listview of pages or versions
     *
     * Parameters:
     * $additionalWhere (string) - SQL-where-clause added to the query.
     * $showAllVersions (boolean) - 0: display only the newest version; 1: display all versions; 2: display oldest version
     * $fieldList (array of strings) - names of the rows to display, default: "uid","keyword","author","tstamp"
     * $linkedField (string)  - name of the row to be linked (to display the pageversion)
     * 
     *
     * @param	[type]		$additionalWhere: ...
     * @param	[type]		$showAllVersions: ...
     * @param	[type]		$fieldList: ...
     * @param	[type]		$linkedField: ...
     * @return	[type]		...
     */
        function showList($additionalWhere, $showAllVersions = 0, $fieldList = array("uid", "keyword", "author", "tstamp", "summary"), $linkedField = "id")
        {
            $pidList = $this->pi_getPidList($this->conf["pidList"], $this->conf["recursive"]);
            if ($showAllVersions == 1) {
                // display all versions, not only the newest
                $fields = 'uid';
            }
            if ($showAllVersions == 2) {
                // display all versions, not only the oldest
                $fields = 'min(uid) AS uid';
            } else {
                // display only the newest version
                $fields = 'max(uid) AS uid';
            }

            $where = 'tx_drwiki_pages.pid IN ('.$pidList.')'.$this->cObj->enableFields('tx_drwiki_pages');
            if ($additionalWhere) {
                $where .= ' AND '. trim($additionalWhere);
            }
            if ($showAllVersions == 2) {
                $where .= ' AND tx_drwiki_pages.keyword='.$GLOBALS['TYPO3_DB']->fullQuoteStr(trim($this->piVars["keyword"]),'tx_drwiki_pages');
            }
    
            $groupby ='';
            if (!$showAllVersions) {
                $groupby = 'keyword';
            }

            // get the uid's
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,'tx_drwiki_pages',$where,$groupby);
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
            {
                $uidList .= $row["uid"] . ",";
            }
            // cut last colon
            $uidList = substr($uidList,0,strlen($uidList)-1);
            
            if($uidList!=""){
                list($this->internal["orderBy"], $this->internal["descFlag"]) = explode(":", trim($this->piVars["sort"]));
                if ($this->internal["orderBy"]) {
                	$orderby = $this->internal["orderBy"].($this->internal["descFlag"]?" DESC":"");
			        $orderby = $GLOBALS['TYPO3_DB']->fullQuoteStr($orderby,'tx_drwiki_pages');
                } else {
			        $orderby = 'keyword';
		        }
 		        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_drwiki_pages','uid IN ('.$uidList.')','', $orderby);
    
                if ($showAllVersions == 2)
                    {
                    $tmpRow = $this->internal["currentRow"];
                    while ($this->internal["currentRow"] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
                    {
                        // data
                        reset($fieldList);
                        while (list(, $field) = each($fieldList))
                        {
                            $content .= $this->getFieldContent($field);
                        }
                    }
                    $this->internal["currentRow"] = $tmpRow;
                }
                else
                {
                    // Beacaue it's dynamic, we put the data in an simple table
                    $content = "<table border=0 cellpadding=4 cellspacing=0>";
                    // Header
                    $content .= "<tr>";
                    reset($fieldList);
                    while (list(, $field) = each($fieldList))
                    {
                        $content .= "<th><b>" . $this->getFieldHeader_sortLink($field) . "</b></th>";
                    }
                    $content .= "</tr>";
                    $tmpRow = $this->internal["currentRow"];
                    
                    while ($this->internal["currentRow"] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
                    {
                        // Daten
                        $content .= "<tr>";
                        reset($fieldList);
                        while (list(, $field) = each($fieldList))
                        {
                            $content .= "<td><b>" . $this->getFieldContent($field,0) . "</b></td>";
                        }
                        $content .= "</tr>";
                    }
                    
                    $content .= "</table>";
                    $this->internal["currentRow"] = $tmpRow;
                }
            } else {
                $content = "<p> No results found </p>";
            }
            
            return $content;
        }

    /**
    * getFieldHeader_sortLink
    *
    * returns a sortlink for the given row
    *
    * @param	string		$fN: The fieldname of the row
    * @return	string		...
    */
        function getFieldHeader_sortLink($fN)
        {
            return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN), array("sort" => $fN.":".($this->internal["descFlag"]?0:1)));
        }

    /**
     * getFieldHeader
     *
     * returns the header/label for the given row
     *
     *
     * @param	[string]		$fN: The fieldname of the row
     * @return	[string]		header/label
     */
        function getFieldHeader($fN)
        {
            switch($fN)
            {
                default:
                if ($fN == $this->internal["orderBy"])
                {
                    return '<span' . $this->pi_classParam("listrowHeader-selected" ) . '>'. "<nobr>" . $this->pi_getLL("listFieldHeader_".$fN, "[".$fN."]") . "&nbsp;" .  "</nobr>" . '</span>';
                }
                else
                    {
                    return '<span'.$this->pi_classParam("listrowHeader" ).'>'. $this->pi_getLL("listFieldHeader_".$fN, "[".$fN."]") . '</span>';
                }

                break;
            }
        }

    /**
     * pi_list_browseresults
     *
     * returns a result browser
     *
     * @param	[string]	$showResultCount: ...
     * @param	[type]		$tableParams: ...
     * @return	[type]		...
     */
        function pi_list_browseresults($showResultCount = 1, $tableParams = "")
        {

            // Initializing variables:
            $pointer = t3lib_div::intInRange($this->piVars['pointer'],0,1000);
            $count = $this->internal["res_count"];
            $results_at_a_time = t3lib_div::intInRange($this->internal["results_at_a_time"], 1, 1000);
            $maxPages = t3lib_div::intInRange($this->internal["maxPages"], 1, 100);
            $max = ceil($count/$results_at_a_time);

            $links = array();

            $imagePointerLeft = $this->cObj->cObjGetSingle($this->conf["icon_pointer_left"], $this->conf["icon_pointer_left."]) . "&nbsp;";
            $imagePointerRight = "&nbsp;" . $this->cObj->cObjGetSingle($this->conf["icon_pointer_right"], $this->conf["icon_pointer_right."]);

            // Make browse-table/links:
            if ($this->pi_alwaysPrev >= 0)
            {
                if ($pointer > 0)
                {
                    $links[] = '<td><p>'.$this->pi_linkTP_keepPIvars($imagePointerLeft . $this->pi_getLL("pi_list_browseresults_prev", "< Previous"), array("pointer" => ($pointer-1?$pointer-1:"")), 0).'</p></td>';
                }
                elseif ($this->pi_alwaysPrev)
                {
                    $links[] = '<td><p>'.$this->pi_getLL("pi_list_browseresults_prev", "< Previous").'</p></td>';
                }
            }
            if (($max-$pointer) <= $maxPages/2)
            {
                $unten = t3lib_div::intInRange($max-$maxPages, 0, $max);
                $oben = $max;
            }
            elseif ($pointer <= $maxPages/2)
            {
                $unten = 0;
                $oben = ($max < $maxPages)?$max:
                $maxPages;
            }
            else
            {
                $unten = t3lib_div::intInRange($pointer-$maxPages/2, 0, $max);
                $oben = t3lib_div::intInRange($pointer+$maxPages/2, 0, $max);
            }
            if ($max > 1)
            {
                for($a = $unten; $a < $oben; $a++)
                {
                    $links[] = '<td'.($pointer == $a?$this->pi_classParam("browsebox-SCell"):"").' nowrap>'.
                    $this->pi_linkTP_keepPIvars(trim($this->pi_getLL("pi_list_browseresults_page", "Page"). " ".
                    ($a+1)) . $imagePointerRight, array("diff_uid" => "", "pointer" => ($a?$a:"")), $this->pi_isOnlyFields($this->pi_isOnlyFields)).'</td>';
                }
            }
            if ($pointer < $max-1)
            {
                $links[] = '<td><p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL("pi_list_browseresults_next", "Next") . $imagePointerRight, array("diff_uid" => "", "pointer" => $pointer+1)).'</p></td>';
            }

            // return "Max: $max maxPages: $maxPages";

            $pR1 = $pointer * $results_at_a_time+1;
            $pR2 = $pointer * $results_at_a_time+$results_at_a_time;
            $sTables = '<DIV'.$this->pi_classParam("browsebox").' align="left">'. '<'.trim('table '.$tableParams).'>
                <tr>'.implode("", $links).'</tr>
                </table>' .($showResultCount ? '<P>'.sprintf(
            str_replace("###SPAN_BEGIN###", "<span".$this->pi_classParam("browsebox-strong").">", $this->pi_getLL("pi_list_browseresults_displays", "Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>")),
                $pR1,
                min(array($this->internal["res_count"], $pR2)),
                $this->internal["res_count"] ).'</P>':'' ).'</DIV>';
            return $sTables;
        }

    /**
     * versionListView
     *
     * returns a version listview for the (url-)given keyword
     * uses the template layout (subpart VERSION_LIST)
     *
     * @param	[string]		$content: Content of the extension output
     * @param	[array]		        $conf: Configuration Array of the extension
     * @return	[string]		Listview of the current wiki-page (keyword)
     */
        function versionListView($content, $conf)
        {
            $markerArray = array();
        
            if ($this->piVars["diff_uid"]) {
            
                 // parse and replace subparts in the template file
                $subpart = $this->cObj->getSubpart($this->templateCode, "###DIFF_VIEW###");
               // globale replacing
                $markerArray["###KEYWORD###"] = '[['.$this->piVars["keyword"].']]'; 
                // Replace markers in template
                $markerArray["###ICON_VERSIONS###"] = $this->makeIconLink(
                					$this->cObj->cObjGetSingle($this->conf["iconVersions"], $this->conf["iconVersions."]),
                					$this->pi_linkTP_keepPIvars_url(array("cmd" => "list", "showUid" => "", "diff_uid" => "", "keyword" => $this->piVars["keyword"]), 1, 0)
                					);
                $markerArray["###ICON_BACK###"] = $this->pi_linkTP_keepPIvars($this->cObj->cObjGetSingle($this->conf["iconBack"], $this->conf["iconBack."]), array("showUid" => "", "cmd" => "", "pointer" => "", "diff_uid" => "", "keyword" => $this->piVars["keyword"]), 1, 0);
                $markerArray["###ICON_HOME###"] = $this->pi_linkTP_keepPIvars($this->cObj->cObjGetSingle($this->conf["iconHome"], $this->conf["iconHome."]), array("showUid" => "", "cmd" => "","pointer" => "", "diff_uid" => "", "keyword" => $this->wikiHomePage), 1, 0);
                
                //Get Data and Versions...
                $latestUid = $this->getUid($this->piVars["keyword"]);
                $newestVersion = $this->pi_getRecord("tx_drwiki_pages", $latestUid, 1);
                $olderVersion = $this->pi_getRecord("tx_drwiki_pages", $this->piVars["diff_uid"], 1);
                

                $markerArray["###DIFF_LATEST_UID###"] =$latestUid;
                $markerArray["###DIFF_DIFF_UID###"] =$this->piVars["diff_uid"];
                
                if (strcmp($olderVersion["body"], $newestVersion["body"])) {
                
                    //load diff engineand do the diff
                    require_once(PATH_t3lib.'class.t3lib_diff.php');
                    $diffEngine = t3lib_div::makeInstance('t3lib_diff');
                    
                    // do diff word by word (red vs. green)
                    $result = $diffEngine->makeDiffDisplay($olderVersion["body"],$newestVersion["body"]);
                    $markerArray["###DIFF_RESULT###"] = '<p class="diff-result">'.$result.'</p>';
                
                } else {
                    $markerArray["###DIFF_RESULT###"] ='<p><strong>The two test strings are exactly the same!</strong></p>';
                }
                
                //format strings
                $newestVersion = preg_replace('|\r\n|', '<br />', $newestVersion);
                $olderVersion = preg_replace('|\r\n|', '<br />', $olderVersion);
                
                $markerArray["###DIFF_RESULT###"] .= '<table class="diff-table">'.
                         '<tr><td class="diff-r" style="font-weight:bold">'.$this->piVars["keyword"].' (ID: '.$this->piVars["diff_uid"].') '.$olderVersion["date"].' by '.$olderVersion["author"].' &rArr </td>'.
                         '<td class="diff-g" style="font-weight:bold">'.$this->piVars["keyword"].' (ID: '.$latestUid.') '.$newestVersion["date"]. ' by '.$newestVersion["author"].' &rArr;</td></tr>'.
                         '<tr><td class="diff-table-cell-red">'.$olderVersion["body"].'</td>'.
                         '<td class="diff-table-cell-green">'.$newestVersion["body"] .'</td></tr>'.
                         '</table>';
                
                $subpart = $this->cObj->substituteMarkerArrayCached($subpart, $markerArray);
                
            } else {
                $pidList = $this->pi_getPidList($this->conf["pidList"], $this->conf["recursive"]);
                
                // get it into FlexForms!!!
                list($this->internal["orderBy"], $this->internal["descFlag"]) = explode(":", $this->piVars["sort"]);
                $this->internal["results_at_a_time"] = t3lib_div::intInRange($this->conf["listView."]["results_at_a_time"], 0, 1000, 3);
                // Number of results to show in a listing.
                $this->internal["maxPages"] = t3lib_div::intInRange($this->conf["listView."]["maxPages"], 0, 1000, 2);
                // The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
                $this->internal["orderByList"] = "uid,author,tstamp";
    
                $sqlkeyword = $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($this->piVars['keyword']),'tx_drwiki_pages');
                $where = 'tx_drwiki_pages.pid IN ('.$pidList.')'.$this->cObj->enableFields('tx_drwiki_pages').' AND keyword = '.$sqlkeyword;
                // Get number of existing versions of this wikipage
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
                    'COUNT(*)',
                    'tx_drwiki_pages',
                    $where
                );
                list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
                
                // validate to positive ints
                $results_at_a_time = t3lib_div::intInRange($this->internal["results_at_a_time"], 1, 1000);
                $pointer = t3lib_div::intInRange($this->piVars['pointer'],0,1000);
                
                // sorting of shown records
                if (t3lib_div::inList($this->internal["orderByList"], $this->internal["orderBy"])) {
                    $orderby = $GLOBALS['TYPO3_DB']->fullQuoteStr($this->internal['orderBy'].($this->internal['descFlag']?' DESC':''),'tx_drwiki_pages');
                } else {
                    $orderby = 'uid DESC';
                }
                // limits for pageview
                $limit = $pointer * $results_at_a_time.','.$results_at_a_time;
                
                // Get the records to show on this page
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ('*','tx_drwiki_pages',$where,'',$orderby,$limit);
    
                // parse and replace subparts in the template file
                $subpart = $this->cObj->getSubpart($this->templateCode, "###VERSION_LIST###");            
                $subpartHeader = $this->cObj->getSubpart($subpart, "###VERSION_LIST_HEADER");
                $subpartRows = $this->cObj->getSubpart($subpart, "###VERSION_LIST_ROWS");
                $templateRow = $this->cObj->getSubpart($subpartRows, "###VERSION_LIST_ROW");
                $templateRowOdd = $this->cObj->getSubpart($subpartRows, "###VERSION_LIST_ROW_ODD");
    
    
                // globale replacing
                $markerArray["###KEYWORD###"] = $this->piVars["keyword"];
                
                // Replace markers in template
                $markerArray["###BROWSER###"] = $this->pi_list_browseresults();
                $markerArray["###ICON_BACK###"] = $this->pi_linkTP_keepPIvars($this->cObj->cObjGetSingle($this->conf["iconBack"], $this->conf["iconBack."]), array("showUid" => "", "cmd" => "","pointer" => "", "diff_uid" => "", "keyword" => $this->piVars["keyword"]), 1, 0);
                $markerArray["###ICON_HOME###"] = $this->pi_linkTP_keepPIvars($this->cObj->cObjGetSingle($this->conf["iconHome"], $this->conf["iconHome."]), array("showUid" => "", "cmd" => "","pointer" => "", "diff_uid" => "", "keyword" => $this->wikiHomePage), 1, 0);
    
                $subpart = $this->cObj->substituteMarkerArrayCached($subpart, $markerArray);
    
                // Header
                $markerArray["###HEADER_UID###"] = $this->getFieldHeader_sortLink("uid");
                $markerArray["###HEADER_AUTHOR###"] = $this->getFieldHeader_sortLink("author");
                $markerArray["###HEADER_TSTAMP###"] = $this->getFieldHeader_sortLink("tstamp");
                $markerArray["###HEADER_SUMMARY###"] = $this->getFieldHeader("summary");
                $markerArray["###HEADER_DIFF_SELECT###"] = $this->getFieldHeader("diff");
    
                $subpartHeader = $this->cObj->substituteMarkerArrayCached($subpartHeader, $markerArray);
                $subpart = $this->cObj->substituteSubpart($subpart, "###VERSION_LIST_HEADER###", $subpartHeader);
    
                // Columns
                $tmpRow = $this->internal["currentRow"];
                $c = 0;
                $subpartRows = "";
                while ($this->internal["currentRow"] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
                {
                    $markerArray["###UID###"] = $this->getFieldContent("uid");
                    if ($this->getFieldContent("author")) {$markerArray["###AUTHOR###"] = $this->getFieldContent("author");}
                        else {$markerArray["###AUTHOR###"] = $this->anonymousUser;}
                    $markerArray["###TSTAMP###"] = $this->getFieldContent("tstamp");
                    $markerArray["###DIFF_SELECT###"] = $this->getFieldContent("diff");
                    $markerArray["###SUMMARY###"] = strip_tags($this->getFieldContent("summary"));
                    $markerArray["###EDIT_PANEL###"] = $this->pi_getEditPanel($this->internal["current_row"]);
    
                    if (!($c%2))
                    {
                        $subpartRows .= $this->cObj->substituteMarkerArrayCached($templateRow, $markerArray);
                    }
                    else
                        {
                        $subpartRows .= $this->cObj->substituteMarkerArrayCached($templateRowOdd, $markerArray);
                    }
                    $c++;
                }
    
                $subpart = $this->cObj->substituteSubpart($subpart, "###VERSION_LIST_ROWS###", $subpartRows);
    
                $this->internal["currentRow"] = $tmpRow;
            }
            return $subpart;
        }

    /**
     * getUser
     *
     * Gets the actual user name of the current user, editing a wiki page.
     * @param   [integer]   $info set outputto info mode
     * @return	[string]	User Name of the current user or IP or anonymousUser
     */
        function getUser($info=0)
        {
            // if we know the username, we use it for the DB - otherwise we use the IP as identifier
            if ($GLOBALS["TSFE"]->loginUser)
            {
                $author = $GLOBALS["TSFE"]->fe_user->user["name"];
                if(empty($author)) $author = $GLOBALS["TSFE"]->fe_user->user["username"];
            }
            elseif ($GLOBALS["REMOTE_ADDR"]) 
            {
                $author = $GLOBALS["REMOTE_ADDR"];
            } 
            else 
            {
                $author = $this->anonymousUser;
            }
            
            // get user info and wiki link - e.g. "[[John Doe (iD: 1)]]"
            if($info==1) {
                $author = '[['.$this->nameSpaces["User"].':'.$author.'|'.$author.' (ID: ' .$GLOBALS["TSFE"]->fe_user->user["uid"].')]]';
            }
            
            return $author;
        }
        
	/**
	*
	* isUserWikiAdmin
	*
	* @return	[boolean]		TRUE if the current user belongs to the admin owner groups
	*
	*/
		function isUserWikiAdmin() 
		{
			$usergroup = $GLOBALS['TSFE']->fe_user->user['usergroup'];
			$ownersgrouplist = $this->adminUserGroup;
			if ((strlen($usergroup)==0) || (strlen($ownersgrouplist)==0)){
				return 0;
			}
			$str_length = strlen($usergroup);
			for ($i=0;$i<$str_length;$i++){
				if ($usergroup[$i]==$ownersgrouplist){
					return 1;	
				}
			}
			return 0;
		}
		
    /**
     * createView
     *
     * Displays a form to create a new page or inserts the data if the user has submitted it
     *
     * @param	[string]		$content: Content of the extension output
     * @param	[array]		        $conf: Configuration Array of the extension
     * @return	[string]		Current page content
     */
        function createView($content, $conf)
        {
            // get NameSpacefor variables
            $getNS = preg_match_all( '/(.*)(:)(.*)/e', $this->piVars["keyword"] , $NSmatches );
            $this->currentNameSpace = $NSmatches[1][0];
            // redirect if the wiki is only editable when a user is logged in
            if ( ($this->activateAccessControl) )                       // Access control active?
            {   
                if ( !$GLOBALS["TSFE"]->fe_user->user["uid"] > 0 )        // User is NOT logged in?
                {
                    $parameters = array("redirect_url" => $this->pi_linkTP_keepPIvars_url(array("cmd" => "edit", "submit" => ""), 1, 0));
                    $link = ($this->pageRedirect) ? $this->pi_linkToPage('Log-In',$this->pageRedirect,'',$parameters) : '';
                    
                    $content = '<div class="wiki-box-red">'.
                 			$this->cObj->cObjGetSingle($this->conf["sys_Warning"], $this->conf["sys_Warning."]).
                 			$this->pi_getLL("pi_edit_login_warning", "Attention: You need to be logged-in ").
							$link.'<br/><br/></div>';
				    return $content;
                 }   
                if (  ($this->allowedGroups == true) && (!$this->inGroup ($this->allowedGroups))    // User is NOT in "Allowed Groups"?
                     OR 
                      ($this->disallowedGroups == true) && ($this->inGroup ($this->disallowedGroups))  ) // User IS in "Disallowed Groups"?
                {
                    $parameters = array("redirect_url" => $this->pi_linkTP_keepPIvars_url(array("cmd" => "edit", "submit" => ""), 1, 0));	
                    $content = '<div class="wiki-box-red">'.
                 			$this->cObj->cObjGetSingle($this->conf["sys_Warning"], $this->conf["sys_Warning."]).
                 			$this->pi_getLL("pi_edit_disallowed", "Sorry, you are not allowed to edit or create this article. Please talk to the administrator if you think this is an error.").
							'<br/><br/></div>';
				    return $content;
                }
            }
            if ($this->piVars["submitCreate"] && !$this->read_only && !$this->read_only)
            {
                // the user has filled out the form before, so we insert the
                // data in the database and reset the pi-variables. Then we display
                // the current keyword (the page we have just created)
                $this->piVars["body"] = $this->replaceSignature($this->piVars["body"]);
                // exec_INSERTquery is sql-injection safe, no quoting needed here                
                
                if ($this->piVars['summary'] == $this->initSummary) {$this->piVars['summary'] = '';};
                //check if previous record is locked (only when admin user is present)
                $isLocked = 0;
                if ($this->isUserWikiAdmin()) $isLocked = $this->isRecordLocked($this->piVars['keyword']);
                
                // check hiding status --> only set it when email notification is
                // active - otherwise set to false
                if ($this->mailNotify) 
                	{$hidden = $this->mailHideItem;}
                else {$hidden = false;}
                
                $pageContent = array(
                        'pid' => $this->storagePid,
                        'crdate' => time(),
                        'tstamp' => time(),
                        'keyword' => trim($this->piVars['keyword']),
                        'summary' => $this->piVars['summary'],
                        'body' => $this->piVars['body'],
                        'date' => $this->piVars['date'],
                        'author' => $this->piVars['author'],
                        'locked' => $isLocked,
                        'hidden' => $hidden,
                    );
                
                $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
                    'tx_drwiki_pages',
                     $pageContent
                );
                
                $this->piVars["cmd"] = "";
                $this->piVars["section"] = "";
                $this->piVars["body"] = "";
                $this->piVars["author"] = "";
                $this->piVars["date"] = "";
                $this->piVars["wiki"] = "";
                $this->piVars["submitCreate"] = "";
                $this->piVars["summary"] = "";
                $this->piVars["showUid"] = "";
                $this->piVars["referer"] = "";
                $this->piVars["pluginSEARCH"]["sword"] = "";
                $this->piVars["pluginSEARCH"]["submit"] = "";   
                 
                // send mail and add note to the output that everything was saved 
                $note ="";            
                if ($this->mailNotify) {
                	$this->mailAdmin($GLOBALS['TYPO3_DB']->sql_insert_id(), $pageContent['keyword'], $pageContent['body']);
		            $note = '<div class="wiki-box-yellow">'.
		             	$this->cObj->cObjGetSingle($this->conf["sys_Info"], $this->conf["sys_Info."]).
		             	$this->pi_getLL("pi_mail_notify_warning", "Your changes were mailed to the wiki editor and will be added later on.").
						'<br/><br/></div>';                	
                }

                return $note . $this->singleView($content, $conf);
            } else {
                // the user has freshly clicked a create-link, so we display
                // the form
                
                //create captcha
                //$captcha = $this->freeCap->makeCaptcha();
                
                // get user name
                $author = $this->getUser();

                $content = '<b> Create: '.$this->piVars["keyword"].'</b> (User: '. $this->getUser() .')<br /><br />'.$this->getEditToolbar();
                // add template adding to page
                //$this->initialPageText1 = preg_replace('|\n|','\\n', $this->initialPageText1);
                
                //TODO: Add a better selector for the templates
                //IDEA: Use FF to add Templates in a mre flexible Way. Maybe similar to the MP3 PLayer
                //extension as separate records stored in a database?
                if ($this->activateInitialPageText) {
					$content .= '['.$this->pi_getLL('pi_add_template','Add template:');
					$content .= ' <a href="#" onclick="addTemplate(\''.$this->initialPageText1.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText1Name.'</a>';
					$content .= ' | <a href="#" onclick="addTemplate(\''.$this->initialPageText2.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText2Name.'</a>';
					$content .= ' | <a href="#" onclick="addTemplate(\''.$this->initialPageText3.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText3Name.'</a> ]';
                }
                $content .= '<form name="'.$this->prefixId. '_CreateForm" method="post" action="'.$this->pi_linkTP_keepPIvars_url(array("cmd" => ""), 1, 0).'">';
                $content .= '<input id="submit" type="Submit" name="'.$this->prefixId.'[submitCreate]" value="'.$this->pi_getLL("pi_edit_save", "Save Page").'" accesskey="s" title="[Alt+s] '.$this->pi_getLL("pi_edit_save", "Save Page").'" tabindex="2" />&nbsp;';
                //$content .= '<input type="Submit" name="'.$this->prefixId.'[previewEdit]" value="'.$this->pi_getLL("pi_edit_preview", "Preview").'" accesskey="p" title="[Alt+p] '.$this->pi_getLL("pi_edit_preview", "Preview").'" tabindex="3" />&nbsp;';
                $content .= '<input type="reset" value="'.$this->pi_getLL("pi_edit_reset", "Reset").'">';
                $content .= " ".$this->pi_linkTP_keepPIvars($this->pi_getLL("pi_edit_cancel", "Cancel / Exit"), array("pluginSEARCH" => "", "submitCreate" =>"", "keyword" => $this->piVars["keyword"], "showUid" => "", "cmd" => ""), 1, 0);
                $content .= '<br />';                
                $content .= '<textarea id="'.$this->prefixId.'[body]" name="'.$this->prefixId.'[body]" cols="'.$this->numberColumns.'" rows="'.$this->numberRows.'" wrap="'.$this->wrapLinesOn.'" accesskey="t" title="[Alt+t]" tabindex="1"></textarea>';
                $content .= '<br />'. $this->pi_getLL("listFieldHeader_summary"). ': ';
                $content .= '<input name="'.$this->prefixId.'[summary]" size="'.$this->charSummary.'" value="'.$this->initSummary.'" accesskey="u" title="[Alt+u]" tabindex="2" />';
                $content .= '<br />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[date]" value="'.date('Y-m-d H:i:s').'" />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[author]" value="'.$author.'" />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[wiki]" value="'.trim($this->piVars["keyword"]).'" />';
                $content .= '<input id="submit" type="Submit" name="'.$this->prefixId.'[submitCreate]" value="'.$this->pi_getLL("pi_edit_save", "Save Page").'" tabindex="3" accesskey="s" title="[Alt+s] '.$this->pi_getLL("pi_edit_save", "Save Page").'" />&nbsp;';
                $content .= '<input type="reset" value="'.$this->pi_getLL("pi_edit_reset", "Reset").'">';
                // Add cancel, back to referer link....
                $content .= " ". $this->pi_linkTP_keepPIvars($this->pi_getLL("pi_edit_cancel", "Cancel / Exit"), array("submitCreate" =>"", "keyword" => $this->piVars["referer"], "referer" => "", "showUid" => "", "cmd" => "", "pluginSEARCH" => ""), 1, 0);
                
                $content .= '</form>';
                // Add Edit Tools
                $content .= $this->cObj->getSubpart($this->templateCode, "###EDIT_TOOLS###");
                //reset referer
                $this->piVars["referer"] = "";
                return $content;
            }
        }

    /**
     * editView
     *
     * Displays a form to edit a  page (create a new version) or inserts the data if the user has
     *
     * @param	[string]		$content: Content of the extension output
     * @param	[array]	    $conf: Configuration Array of the extension
     * @return	[string]		Current page content
     */
        function editView($content, $conf)
        {
        	// TODO: Make Form part of the Template....
        	// get the latest ID to check for newer versions...
            $latestUID = $this->getUid($this->piVars["keyword"]);
            
            // get NameSpacefor variables
            $getNS = preg_match_all( '/(.*)(:)(.*)/e', $this->piVars["keyword"] , $NSmatches );
            $this->currentNameSpace = $NSmatches[1][0];

            if ($this->piVars["submitEdit"] && !$this->read_only)
            {                
                // the user has filled out the form before and submitted it, so we insert the
                // given data in the database and display the given keyword (this displays the freshly
                // created version)
				
                // check if a newer version is available or not:
                // No newer version:
                if ($latestUID <= $this->piVars["latest"]) {                    
                    
                    //reassemble sections
                    if ($this->piVars['section']) {
                    	//Get Data and Versions...
                		$latestUid = $this->getUid($this->piVars["keyword"]);
                		$latestVersion = $this->pi_getRecord("tx_drwiki_pages", $latestUid, 1);
                    	$this->piVars['body'] = $this->replaceSection($this->piVars['section'],$this->piVars['body'],$latestVersion['body']);
                    }
                    
                    $this->piVars["body"] = $this->replaceSignature($this->piVars["body"]);
                    
                    // remove summary text if it has not been changed
                    if ($this->piVars['summary'] == $this->initSummary) {$this->piVars['summary'] = '';};
                    //check if previous record is locked (only when admin user is present)
                    $isLocked = 0;
                    if ($this->isUserWikiAdmin()) $isLocked = $this->isRecordLocked($this->piVars['keyword']);
                    
	                // check hiding status --> only set it when email notification is
	                // active - otherwise set to false
	                if ($this->mailNotify) 
	                	{$hidden = $this->mailHideItem;}
	                else {$hidden = false;}                    
	                    
                    $pageContent = array(
	                            'pid' => $this->storagePid,
	                            'crdate' => time(),
	                            'tstamp' => time(),
	                            'summary' => $this->piVars['summary'],
	                            'keyword' => trim($this->piVars['keyword']),
	                            'body' => $this->piVars['body'],
	                            'date' => $this->piVars['date'],
	                            'author' => $this->piVars['author'],
	                            'locked' => $isLocked,
	                            'hidden' => $hidden,
	                        );
	                        
	                //TODO: Check if unset aray could be done more efficint
                	$this->piVars["cmd"] = "";
            		$this->piVars["section"] = "";
                	$this->piVars["body"] = "";
                	$this->piVars["author"] = "";
                	$this->piVars["date"] = "";
                	$this->piVars["wiki"] = "";
                	$this->piVars["submitEdit"] = "";
           	    	$this->piVars["previewEdit"] = "";
                	$this->piVars["showUid"] = "";
                	$this->piVars["summary"] = "";
                	$this->piVars["latest"] = "";
                    $this->piVars["pluginSEARCH"]["sword"] = "";
                    $this->piVars["pluginSEARCH"]["submit"] = "";
                    
	                // HOOK: insert only if hook returns OK or is not set
	                if($this->hook_submit_beforeInsert($pageContent)){
	                    $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
	                        'tx_drwiki_pages',
	                        $pageContent
	                    );
	                    
	                    $note = '';
		                if ($this->mailNotify) {
		                	$this->mailAdmin($GLOBALS['TYPO3_DB']->sql_insert_id(), $pageContent['keyword'], $pageContent['body']);
				            $note = '<div class="wiki-box-yellow">'.
				             	$this->cObj->cObjGetSingle($this->conf["sys_Info"], $this->conf["sys_Info."]).
				             	$this->pi_getLL("pi_mail_notify_warning", "Your changes were mailed to the editor and will be added later on.").
								'<br/><br/></div>';             		                	
		                }	                    
                	}
                	// HOOK: to do something after insert
                	$this->hook_submit_afterInsert($pageContent);

                	return $note . $this->singleView($content, $conf);
                } else {  // changes are detected....
                	$this->piVars["cmd"] = "";
                	$this->piVars["section"] = "";
                	$this->piVars["body"] = "";
                	$this->piVars["author"] = "";
                	$this->piVars["date"] = "";
                	$this->piVars["wiki"] = "";
                	$this->piVars["submitEdit"] = "";
           	    	$this->piVars["previewEdit"] = "";
                	$this->piVars["showUid"] = "";
                	$this->piVars["summary"] = "";
                	$this->piVars["latest"] = "";
                    $this->piVars["pluginSEARCH"]["sword"] = "";
                    $this->piVars["pluginSEARCH"]["submit"] = "";

                        //get latest version of wiki-page from DB                	
                	$this->internal["currentRow"] = $this->pi_getRecord("tx_drwiki_pages", $latestUID, 1);
                	return '<div class="wiki-box-red">'.$this->cObj->cObjGetSingle($this->conf["sys_Warning"], $this->conf["sys_Warning."]).$this->pi_getLL("pi_edit_ver_warning", "Attention: newer version detected!").'</div>' . $this->singleView($this->internal["currentRow"]["content"], $conf);
                }                
            }
            elseif ($this->piVars["previewEdit"] && !$this->read_only)
            {
                $tmp_summary = $this->piVars["summary"];
                $this->piVars["summary"] = "";
                $tmp_body = $this->piVars["body"];
                
                //get Preview
                $previewContent = $this->parse($tmp_body,1);
                $this->piVars["body"] = "";
                $tmp_author = $this->piVars["author"];
                $this->piVars["author"] = "";

                $this->piVars["date"] = "";
                $this->piVars["cmd"] = "";
                $this->piVars["wiki"] = "";
                $this->piVars["previewEdit"] = "";
                $this->piVars["showUid"] = "";
                $this->piVars["latest"] = "";
                $this->piVars["pluginSEARCH"]["sword"] = "";
                $this->piVars["pluginSEARCH"]["submit"] = "";

                $content = '<b> Edit: '.$this->piVars["keyword"] .'</b> (User: '. $this->getUser() .')<br /><br />'.$this->getEditToolbar();
                // add template adding to page
                //$this->initialPageText1 = preg_replace('|\n|','\\n', $this->initialPageText1);
                if ($this->activateInitialPageText) {
                    $content .= "[".$this->pi_getLL("pi_add_template", "Add template:");
                    $content .= ' <a href="#" onclick="addTemplate(\''.$this->initialPageText1.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText1Name.'</a>';
                    $content .= ' | <a href="#" onclick="addTemplate(\''.$this->initialPageText2.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText2Name.'</a>';
                    $content .= ' | <a href="#" onclick="addTemplate(\''.$this->initialPageText3.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText3Name.'</a> ]';
                }                
                $content .= '<form name="'.$this->prefixId. '_EditForm" method="post" action="'.$this->pi_linkTP_keepPIvars_url(array(), 1, 0).'">';
                $content .= '<input type="Submit" name="'.$this->prefixId.'[submitEdit]" value="'.$this->pi_getLL("pi_edit_save", "Save Page").'" accesskey="s" title="[Alt+s] '.$this->pi_getLL("pi_edit_save", "Save Page").'" tabindex="2" />&nbsp;';
                $content .= '<input type="Submit" name="'.$this->prefixId.'[previewEdit]" value="'.$this->pi_getLL("pi_edit_preview", "Preview").'" accesskey="p" title="[Alt+p] '.$this->pi_getLL("pi_edit_preview", "Preview").'" tabindex="3" />&nbsp;';
                $content .= '<input type="reset" value="'.$this->pi_getLL("pi_edit_reset", "Reset").'">';
                $content .= " ".$this->pi_linkTP_keepPIvars($this->pi_getLL("pi_edit_cancel", "Cancel / Exit"), array("pluginSEARCH" => "", "submitCreate" =>"", "keyword" => $this->piVars["keyword"], "showUid" => "", "cmd" => ""), 1, 0);
                $content .= '<br />';
                $content .= '<textarea id="'.$this->prefixId.'[body]" name="'.$this->prefixId.'[body]" cols="'.$this->numberColumns.'" rows="'.$this->numberRows.'" wrap="'.$this->wrapLinesOn.'" accesskey="t" title="[Alt+t]">'.$tmp_body.'</textarea>';
                $content .= '<br />'. $this->pi_getLL("listFieldHeader_summary"). ': ';
                $content .= '<input name="'.$this->prefixId.'[summary]" size="'.$this->charSummary.'" value="'.$tmp_summary.'" accesskey="u" title="[Alt+u]" tabindex="1" />';
                $content .= '<br />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[date]" value="'.date('Y-m-d H:i:s').'" />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[author]" value="'.$tmp_author.'" />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[cmd]" value="edit" />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[wiki]" value="'.trim($this->piVars["keyword"]).'" />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[section]" value="'.trim($this->piVars["section"]).'" />';
                $content .= '<input type="hidden" name="'.$this->prefixId.'[latest]" value="'.$latestUID.'" />';
                $content .= '<input type="Submit" name="'.$this->prefixId.'[submitEdit]" value="'.$this->pi_getLL("pi_edit_save", "Save Page").'" accesskey="s" title="[Alt+s] '.$this->pi_getLL("pi_edit_save", "Save Page").'" tabindex="2" />&nbsp;';
                $content .= '<input type="Submit" name="'.$this->prefixId.'[previewEdit]" value="'.$this->pi_getLL("pi_edit_preview", "Preview").'" accesskey="p" title="[Alt+p] '.$this->pi_getLL("pi_edit_preview", "Preview").'" tabindex="3" />&nbsp;';
                $content .= '<input type="reset" value="'.$this->pi_getLL("pi_edit_reset", "Reset").'">';
                $content .= " ".$this->pi_linkTP_keepPIvars($this->pi_getLL("pi_edit_cancel", "Cancel / Exit"), array("pluginSEARCH" => "", "submitCreate" =>"", "keyword" => $this->piVars["keyword"], "showUid" => "", "cmd" => ""), 1, 0);
                $content .= '</form>';
                
                // Add Edit Tools
                $content .= $this->cObj->getSubpart($this->templateCode, "###EDIT_TOOLS###");
			                 
                
                return $content . $this->getUsedTemplateList($tmp_body) . '<br /><br /><h3>'.$this->pi_getLL("pi_edit_preview", "Preview") .'</h3><hr />' . $previewContent;
            }
            else
                {
                if ( ($this->activateAccessControl) )                       // Access control active?
                {   
                    if ( !$GLOBALS["TSFE"]->fe_user->user["uid"] > 0 )        // User is NOT logged in?
                    {
                        $parameters = array("redirect_url" => $this->pi_linkTP_keepPIvars_url(array("cmd" => "edit", "submit" => ""), 1, 0));
                        $link = ($this->pageRedirect) ? $this->pi_linkToPage('Log-In',$this->pageRedirect,'',$parameters) : '';
                        
                        $content = '<div class="wiki-box-red">'.
                     			$this->cObj->cObjGetSingle($this->conf["sys_Warning"], $this->conf["sys_Warning."]).
                     			$this->pi_getLL("pi_edit_login_warning", "Attention: You need to be logged-in ").
    							$link.'<br/><br/></div>';
    				    return $content;
                     }   
                    if (  ($this->allowedGroups == true) && (!$this->inGroup ($this->allowedGroups))    // User is NOT in "Allowed Groups"?
                         OR 
                          ($this->disallowedGroups == true) && ($this->inGroup ($this->disallowedGroups))  ) // User IS in "Disallowed Groups"?
                    {
                        $parameters = array("redirect_url" => $this->pi_linkTP_keepPIvars_url(array("cmd" => "edit", "submit" => ""), 1, 0));	
                        $content = '<div class="wiki-box-red">'.
                     			$this->cObj->cObjGetSingle($this->conf["sys_Warning"], $this->conf["sys_Warning."]).
                     			$this->pi_getLL("pi_edit_disallowed", "Sorry, you are not allowed to edit or create this article. Please talk to the administrator if you think this is an error.").
    							'<br/><br/></div>';
    				    return $content;
                    }
                }                
                
                
                
                {  
                    // display the edit form
                    // TODO: take layout from template
    
                    $author = $this->getUser();
                    
                    // if we don't know the uid, we search it by the keyword
                    if (!$this->piVars["showUid"])
                    {
                        $this->piVars["showUid"] = $this->getUid($this->piVars["keyword"]);
                    }
    
                    // get the record
                    $this->internal["currentTable"] = "tx_drwiki_pages";
                    $this->internal["currentRow"] = $this->pi_getRecord("tx_drwiki_pages", $this->piVars["showUid"], 1);
                    
                    
                    //get the section to be edited
                    if($this->piVars["section"]) {
                    	$this->internal["currentRow"]['body'] = $this->getSection($this->getFieldContent("body"), $this->piVars["section"]);
                    }
    
                    // if we have no keyword we take it from the current data
                    if (!$this->piVars["keyword"])
                    {
                        $this->piVars["keyword"] = trim($this->getFieldContent("keyword"));
                    }
                    
                    // create the form and add the toolbar
                    $content = '<b>Edit: '.$this->piVars["keyword"].'</b> (User: '. $this->getUser() .')<br /><br />'.$this->getEditToolbar();
                    // add template adding to page
                    if ($this->activateInitialPageText) {
                        $content .= "[".$this->pi_getLL("pi_add_template", "Add template:");
                        $content .= ' <a href="#" onclick="addTemplate(\''.$this->initialPageText1.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText1Name.'</a>';
                        $content .= ' | <a href="#" onclick="addTemplate(\''.$this->initialPageText2.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText2Name.'</a>';
                        $content .= ' | <a href="#" onclick="addTemplate(\''.$this->initialPageText3.'\', \''.$this->prefixId.'[body]\'); return false;">'.$this->initialPageText3Name.'</a> ]';
                    }                    
                    $content .= '<form id="'.$this->prefixId. '_EditForm" name="'.$this->prefixId. '_EditForm" method="post" action="'.$this->pi_linkTP_keepPIvars_url(array(), 1, 0).'">';
                    $content .= '<input type="Submit" name="'.$this->prefixId.'[submitEdit]" value="'.$this->pi_getLL("pi_edit_save", "Save Page").'" accesskey="s" title="[Alt+s] '.$this->pi_getLL("pi_edit_save", "Save Page").'" tabindex="2" />&nbsp;';
                    $content .= '<input type="Submit" name="'.$this->prefixId.'[previewEdit]" value="'.$this->pi_getLL("pi_edit_preview", "Preview").'" accesskey="p" title="[Alt+p] '.$this->pi_getLL("pi_edit_preview", "Preview").'" tabindex="3" />&nbsp;';
                    $content .= '<input type="reset" value="'.$this->pi_getLL("pi_edit_reset", "Reset").'">';
                    $content .= " ".$this->pi_linkTP_keepPIvars($this->pi_getLL("pi_edit_cancel", "Cancel / Exit"), array("pluginSEARCH" => "", "submitCreate" =>"", "keyword" => $this->piVars["keyword"], "showUid" => "", "cmd" => ""), 1, 0);
                    $content .= '<br />';
                    $content .= '<textarea id="'.$this->prefixId.'[body]" name="'.$this->prefixId.'[body]" cols="'.$this->numberColumns.'" rows="'.$this->numberRows.'" wrap="'.$this->wrapLinesOn.'" accesskey="t" title="[Alt+t]">'.$this->getFieldContent("body").'</textarea>';
                    $content .= '<br />'. $this->pi_getLL("listFieldHeader_summary"). ': ';
                    $content .= '<input name="'.$this->prefixId.'[summary]" size="'.$this->charSummary.'" accesskey="u" title="[Alt+u]" tabindex="1" />';
                    $content .= '<br />';
                    $content .= '<input type="hidden" name="'.$this->prefixId.'[date]" value="'.date('Y-m-d H:i:s').'" />';
                    $content .= '<input type="hidden" name="'.$this->prefixId.'[author]" value="'.$author.'" />';
                    $content .= '<input type="hidden" name="'.$this->prefixId.'[section]" value="'.trim($this->piVars["section"]).'" />';
                    $content .= '<input type="hidden" name="'.$this->prefixId.'[cmd]" value="edit" />';
                    $content .= '<input type="hidden" name="'.$this->prefixId.'[wiki]" value="'.trim($this->piVars["keyword"]).'" />';
                    $content .= '<input type="hidden" name="'.$this->prefixId.'[latest]" value="'.$latestUID.'" />';
                    $content .= '<input type="Submit" name="'.$this->prefixId.'[submitEdit]" value="'.$this->pi_getLL("pi_edit_save", "Save Page").'" accesskey="s" title="[Alt+s] '.$this->pi_getLL("pi_edit_save", "Save Page").'" tabindex="2" />&nbsp;';
                    $content .= '<input type="Submit" name="'.$this->prefixId.'[previewEdit]" value="'.$this->pi_getLL("pi_edit_preview", "Preview").'" accesskey="p" title="[Alt+p] '.$this->pi_getLL("pi_edit_preview", "Preview").'" tabindex="3" />&nbsp;';
                    $content .= '<input type="reset" value="'.$this->pi_getLL("pi_edit_reset", "Reset").'">';
                    // Add abort-link
                    $content .= " ".$this->pi_linkTP_keepPIvars($this->pi_getLL("pi_edit_cancel", "Cancel / Exit"), array("pluginSEARCH" => "", "submitCreate" =>"", "keyword" => $this->piVars["keyword"], "showUid" => "", "cmd" => ""), 1, 0);
                    $content .= '</form>';
                    // Add Edit Tools
                    $content .= $this->cObj->getSubpart($this->templateCode, "###EDIT_TOOLS###");
                    $content .= $this->getUsedTemplateList($this->getFieldContent("body"));
                }

                return $content;
            }
        }
        /**
         * Replaces the signature tags: If in Ddiscussion NS, the tags are replaced
         * if in "normal" NS, the tags are simply removed
         */
       function replaceSignature($str){
       
            if ($this->currentNameSpace == "Discussion"){$str = preg_replace('/~~~~~/', date('Y-m-d H:i:s'), $str );}
                else {$str = preg_replace('/~~~~~/', "", $str);}
        
            if ($this->currentNameSpace == "Discussion"){$str = preg_replace('/~~~~/', $this->getUser()." (".date('Y-m-d H:i:s').")", $str );}
                else {$str = preg_replace('/~~~~/', "", $str);}
        
            if ($this->currentNameSpace == "Discussion"){$str = preg_replace('/~~~/', $this->getUser(), $str );}
                else {$str = preg_replace('/~~~/', "", $str);}
            
            return $str;
        }
        
        /**
         * Returns the list of the used wiki templates
         */
        function getUsedTemplateList ($str) {
		    
		    $templateSelector = '';
		    $str = preg_replace('/\{\{\{(.*?)\}\}\}/U', '', $str);
		    $templateCounter = preg_match_all('/\{\{(?!'.$this->noTemplates.')(.*?)\}\}/U', $str, $usedTemplates, PREG_SET_ORDER);
		    if ($templateCounter){
			    //remove dubble entires - not elegant, but it works
			    foreach ($usedTemplates as $usedTemplate) {
			    	$templateName = explode('|',$usedTemplate[1]);
			    	$temlateArray[$this->nameSpaces["Template"].":".$templateName[0]] = $this->nameSpaces["Template"].":".$templateName[0]; 
			    }
			    
			    $templateSelector = '<br /><div class="wiki-box-blue"><h3>'.$this->pi_getLL('pi_edit_usedtmpl', 'Used Wiki Templates').':</h3><ul>';
			    foreach ($temlateArray as $dummy) {
			           $templateSelector .= '<li>'.
			           						$this->makeIconLink(
				                				$this->cObj->cObjGetSingle($this->conf["iconEdit"], $this->conf["iconEdit."]),
				                				$this->pi_linkTP_keepPIvars_url(array("showUid" => "", "keyword" => $dummy, "cmd" => "edit", "section" => "", "submit" => ""), 1, 0)) .' '.
			           						$this->pi_linkTP_keepPIvars($dummy, array("showUid" => "", "keyword" => $dummy, "cmd" => "", "section" => "", "submit" => ""), 1, 0).' '.
			           						$this->pi_linkTP_keepPIvars($this->pi_getLL('pi_edit_tmpledit', '[Edit]'), array("showUid" => "", "section" => "", "keyword" => $dummy, "cmd" => "edit", "submit" => ""), 1, 0).
											 '</li>';
											
			    }
			    $templateSelector .= '</ul></div>';
		    }
		    return $templateSelector;
        }
        
        /**
         * toolarray an array of arrays which each include the filename of
         * the button image (without path), the opening tag, the closing tag,
         * and optionally a sample text that is inserted between the two when no
         * selection is highlighted.
         * The tip text is shown when the user moves the mouse over the button.
         *
         * Already here are accesskeys (key), which are not used yet until someone
         * can figure out a way to make them work in IE. However, we should make
         * sure these keys are not defined on the edit page.
         */
        
	function getEditToolbar() {
         
            $toolarray=array(
                array(	'image'=>'button_bold.png',
                                'open'	=>	"\'\'\'",
                                'close'	=>	"\'\'\'",
                                'sample'=>	$this->pi_getLL("tb_bold_sample", "Bold text"),
                                'tip'	=>	$this->pi_getLL("tb_bold_tip", "Bold text"),
                                'key'	=>	'B'
                        ),
                array(	'image'=>'button_italic.png',
                                'open'	=>	"\'\'",
                                'close'	=>	"\'\'",
                                'sample'=> $this->pi_getLL("tb_italic_sample", "Italic text"),
                                'tip'	=>	$this->pi_getLL("tb_italic_tip", "Italic text"),
                                'key'	=>	'I'
                        ),
                array(	'image'=>'button_link.png',
                                'open'	=>	'[[',
                                'close'	=>	']]',
                                'sample'=>	$this->pi_getLL("tb_link_sample", "Link title"),
                                'tip'	=>	$this->pi_getLL("tb_link_tip", "Internal link"),
                                'key'	=>	'L'
                        ),
                array(	'image'=>'button_extlink.png',
                                'open'	=>	'[',
                                'close'	=>	']',
                                'sample'=>	$this->pi_getLL("tb_extlink_sample", "http://www.example.com link title"),
                                'tip'	=>	$this->pi_getLL("tb_extlink_tip", "External link (remember http:// prefix)"),
                                'key'	=>	'X'
                        ),
                array(	'image'=>'button_headline.png',
                                'open'	=>	"\\n== ",
                                'close'	=>	" ==\\n",
                                'sample'=>	$this->pi_getLL("tb_headline_sample", "Headline text"),
                                'tip'	=>	$this->pi_getLL("tb_headline_tip", "Level 2 headline"),
                                'key'	=>	'H'
                        ),
                array(	'image'	=>'button_hr.png',
                                'open'	=>	"\\n----\\n",
                                'close'	=>	'',
                                'sample'=>	'',
                                'tip'	=>	$this->pi_getLL("tb_hr_tip", "Horizontal line (use sparingly)"),
                                'key'	=>	'R'
                        ),
                array(	'image'	=>'button_sig.png',
                                'open'	=>	"\\n--~~~~\\n",
                                'close'	=>	'',
                                'sample'=>	'',
                                'tip'	=>	$this->pi_getLL("tb_sig_tip", "Signature"),
                                'key'	=>	'S'
                        ),
                array(	'image'	=>'button_nowiki.png',
                                'open'	=>	"<nowiki>",
                                'close'	=>	'</nowiki>',
                                'sample'=>	$this->pi_getLL("tb_nowiki_sample", "This is not parsed"),
                                'tip'	=>	$this->pi_getLL("tb_nowiki_tip", "Nowiki"),
                                'key'	=>	'N'
                        ),
                array(	'image'	=>'button_sub.png',
                                'open'	=>	"<sub>",
                                'close'	=>	'</sub>',
                                'sample'=>	'',
                                'tip'	=>	$this->pi_getLL("tb_sub_tip", "Sub"),
                                'key'	=>	'D'
                        ),
                array(	'image'	=>'button_sup.png',
                                'open'	=>	"<sup>",
                                'close'	=>	'</sup>',
                                'sample'=>	'',
                                'tip'	=>	$this->pi_getLL("tb_sup_tip", "Sup"),
                                'key'	=>	'U'
                        ),
                array(	'image'	=>'button_strike.png',
                                'open'	=>	"<s>",
                                'close'	=>	'</s>',
                                'sample'=>	$this->pi_getLL("tb_strike_sample", "This text is strike through"),
                                'tip'	=>	$this->pi_getLL("tb_strike_tip", "Strike Through"),
                                'key'	=>	''
                        ),
                array(	'image'	=>'button_ref.png',
                                'open'	=>	"<ref>",
                                'close'	=>	'</ref>',
                                'sample'=>	$this->pi_getLL("tb_ref_sample", "This is a reference"),
                                'tip'	=>	$this->pi_getLL("tb_ref_tip", "Add reference"),
                                'key'	=>	'R'
                        ),
            );
            $toolbar ="<script type='text/javascript'>\n/*<![CDATA[*/\n";
    
            $toolbar.="document.writeln(\"<div id='toolbar'>\");\n";
            //TODO get toolbar image path into TS
            foreach($toolarray as $tool) {    
                $image=$this->sitePath.'res/buttons/'.$tool['image'];
                $open=$tool['open'];
                $close=$tool['close'];
                $sample = $tool['sample'];
                $tip = $tool['tip'];
                $key = $tool["key"]; // accesskey for the buttons
    
                $toolbar.="addButton('$image','$tip','$open','$close','$sample','$key');\n";
            }
    
            $toolbar.="addInfobox('".$this->pi_getLL("tb_infobox")."','".$this->pi_getLL("tb_infobox_alert")."');\n";
            $toolbar.="document.writeln(\"</div>\");\n";
    
            $toolbar.="/*]]>*/\n</script>";
            
            // Add Scripts
            $JS_Param = "var drWikiEditor='".$this->prefixId."[body]'; //Handler for Editor\n";
            $this->loadExtJS("res/wiki_script.js", $JS_Param);
            
            return $toolbar;		
        }
	
	function loadExtJS($filePath, $additionalCode="")
	{
		if (!$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId]) {
			//prepare general script to be loaded into the Header
			$jsCodeArr = file(t3lib_extMgm::extPath('dr_wiki').$filePath);
			// Add Configuration for JS-Script parts.
			$jsCode = $additionalCode;
			foreach ($jsCodeArr as $val) {
				$jsCode = $jsCode . $val;
			}
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = t3lib_div::wrapJS($jsCode);
		}
	}
	
        function getHTMLFile($content, $conf)
        {
            $this->internal["currentTable"] = "tx_drwiki_pages";
            $this->internal["currentRow"] = $this->pi_getRecord("tx_drwiki_pages", $this->piVars["showUid"], 1);
            
            $fname =  str_replace(' ', '_',$this->piVars["keyword"]) . '.html';
            header("Cache-control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Transfer-Encoding: binary");
            header("Content-Type: text/html");
            header("Content-Disposition: attachment; filename=$fname");
            header("Cache-control: private");
            echo $this->parse($this->getFieldContent("body"),1);
            set_time_limit(0);
            exit;
        }
    /**
     * singleView
     *
     * Displays a single pageversion of the page given by the url (keyword or showUid)).
     *
     * Uses the template for layout (subpart: DEFAULT_VIEW)
     * @param	[string]		$content: Content of the extension output
     * @param	[array]		        $conf: Configuration Array of the extension
     * @return	[string]		Current page content
     */
        function singleView($content, $conf)
        {
            // if we have no uid we search it by the keyword
            if (!$this->piVars["showUid"])
            {
                $this->piVars["showUid"] = $this->getUid($this->piVars["keyword"]);
            }

            // get the wiki record
            $this->internal["currentTable"] = "tx_drwiki_pages";
            $this->internal["currentRow"] = $this->pi_getRecord("tx_drwiki_pages", $this->piVars["showUid"], 1);
           
            // set the keyword from the current record
            if (!$this->piVars["keyword"])
            {
                $this->piVars["keyword"] = $this->getFieldContent("keyword");
            }

       

            // This sets the title of the page for use in indexed search results:
            if ($this->internal["currentRow"]["title"]) $GLOBALS["TSFE"]->indexedDocTitle = $this->internal["currentRow"]["title"];

            // Caching Functions for the Wiki
            // ******************************
            
            $pageIsCached = false;
            $wikiPageContent = "";
            
            // check for multile redirects to other wiki-page
            $numRedirect = preg_match_all( '/\#REDIRECT \[\[(.*?)\]\]/e', $this->getFieldContent("body"), $redirects );
            
            $old_keyword = '';
            while ($redirects[1][0] AND ($old_keyword != $this->piVars["keyword"]) )
            {
            	$old_keyword = $this->piVars["keyword"];
            	$this->piVars["keyword"] = $redirects[1][0];
                
            	$this->piVars["showUid"] = $this->getUid($this->piVars["keyword"]);
                
                // if there is no page to display, go to edit-view
                if (!$this->piVars["showUid"]) {
                    // unset save variable
                    $this->piVars["submitCreate"] = "";
                    // go to create view
                    return $this->createView($content, $conf);
                }
                
            	$this->internal["currentTable"] = "tx_drwiki_pages";
            	$this->internal["currentRow"] = $this->pi_getRecord("tx_drwiki_pages", $this->piVars["showUid"], 1);
            	
            	if ($this->internal["currentRow"]["title"]) $GLOBALS["TSFE"]->indexedDocTitle = $this->internal["currentRow"]["title"];
            	$this->redirectLink = '<table class="wiki-box-yellow"><tr><td>'.$this->cObj->cObjGetSingle($this->conf["sys_Redirect"], $this->conf["sys_Redirect."]).$this->pi_linkTP_keepPIvars($this->pi_getLL("pi_msg_redirect") .'<b>	&rArr; </b>"'.$old_keyword.'"', array("keyword" => $old_keyword, "showUid" => "", "cmd" => "edit"), 1, 0).'</td></tr></table>';
            	$numRedirect = preg_match_all( '/\#REDIRECT \[\[(.*?)\]\]/e', $this->getFieldContent("body"), $redirects );
            }

            // get Namespace for variables
            $getNS = preg_match_all( '/(.*)(:)(.*)/e', $this->piVars["keyword"] , $NSmatches );
            $this->currentNameSpace = $NSmatches[1][0];
            
            if ($this->enableWikiCaching) {
            
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    '*',
                    'tx_drwiki_cache',
                    'tx_drwiki_cache.cache_uid=('.intval($this->piVars['showUid']).')'
                );
              
                // set the cache entries
                $this->internal["cacheTable"] = "tx_drwiki_cache";	        	
                $this->internal["currentCache"] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				
                    // If no cache-version is present --> create it
                    if (!$this->internal["currentCache"]) {
                        // create cachable HTML-output in $this->cacheContents by creating a normal wiki-page
                        // cp. $this->parse() for details
                        $wikiPageContent = $this->parse($this->getFieldContent("body"));
                        $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
                            'tx_drwiki_cache',
                            array(
                                'pid' => $this->storagePid,
                                'crdate' => $this->getFieldContent('crdate'),
                                'tstamp' => $this->getFieldContent('tstamp'),
                                'keyword' => trim($this->piVars["keyword"]),
                                'html_cache' => $this->cacheContents,
                                'cache_uid' => intval($this->piVars["showUid"])
                            )
                        );
                        
                    } else {
                        // get cached item
                        $wikiPageContent = $this->internal["currentCache"]["html_cache"];
                        //Removed mode
                        $wikiPageContent = $this->finalise_parse($wikiPageContent);
                        $this->addJavaToggleScript();
                        $pageIsCached = true;                        
                    }
            
            
            // In case "Caching" is disenabled by FlexForms:
            } else {
                    // ...parse wiki-page and return it
                    $wikiPageContent = $this->parse($this->getFieldContent("body"));
            }

            // Fill in the data
            $subpart = $this->cObj->getSubpart($this->templateCode, "###DEFAULT_VIEW###");

            $markerArray["###KEYWORD###"] = $this->pi_linkTP_keepPIvars($this->piVars["keyword"], array("keyword" => $this->piVars["keyword"], "showUid" => "", "cmd" => ""), 1, 0);
            // Already in a discussion? do not add "Discussion" Tag
            $str_discuss = "";
            if ((substr_count($this->getFieldContent("keyword"),$this->keyDiscussion)>=1)) 
            {
                $str_discuss = "";
                $originalKeyword = str_replace($this->keyDiscussion, "",$this->getFieldContent("keyword"));
                $str_discuss = $this->divDiscussion . $this->pi_linkTP_keepPIvars($this->pi_getLL("pi_backToKeyword").$originalKeyword, array("keyword" => $originalKeyword, "showUid" => "", "cmd" => ""), 1, 0);
            }
            else 
            {
                $dummy = $this->keyDiscussion . $this->piVars["keyword"];
                $str_discuss = $this->divDiscussion . $this->pi_linkTP_keepPIvars($this->pi_getLL("pi_discussion"), array("keyword" => $dummy, "showUid" => "", "cmd" => ""), 1, 0);
            }
            if ($this->read_only xor $this->getFieldContent("locked")) 
            {
                $markerArray["###DISCUSSION###"]="";
                $markerArray["###DISCUSSIONACTIVE###"]="";
            }
            else 
            {
                $markerArray["###DISCUSSION###"] =  $str_discuss;
                if ($this->getDiscussionLink($this->piVars["keyword"],true)) {$markerArray["###DISCUSSIONACTIVE###"] =  "&laquo;";} //add Image
                    else {$markerArray["###DISCUSSIONACTIVE###"] =  ""; }
            }
            
            $markerArray["###BODY###"] = $wikiPageContent;
            
            // add Category Footer
            $markerArray["###CATEGORY###"] = $this->createCategoryFooter();
            
            // add Category Listing
            if ($this->currentNameSpace == $this->nameSpaces["Category"])
            	$markerArray["###CATEGORYLIST###"] = $this->indexListFormatter->main($this,$this->piVars["keyword"]);
            else $markerArray["###CATEGORYLIST###"] = '';
            
            // check if caching is active - if yes: display cache ID (CID)
            if ($this->enableWikiCaching) {$cacheStr = " (CID: ". $this->internal["currentCache"]["cache_uid"] .")";}
                else {$cacheStr = "";}
            $markerArray["###DATE###"] = $this->getFieldContent("tstamp").$cacheStr;
            
            $markerArray["###AUTHOR###"] = $this->getFieldContent("author");
            $markerArray["###ICON_HOME###"] = $this->pi_linkTP_keepPIvars($this->cObj->cObjGetSingle($this->conf["iconHome"], $this->conf["iconHome."]), array("showUid" => "", "keyword" => $this->wikiHomePage), 1, 0);
            $markerArray["###ICON_RELOAD###"] = $this->makeIconLink(
            					$this->cObj->cObjGetSingle($this->conf["iconReload"], $this->conf["iconReload."]),
            					$this->pi_linkTP_keepPIvars_url(array("submitEdit" => "", "keyword" => $this->piVars["keyword"], "showUid" => "", "cmd" => "", "sort" => "", "section" => ""), 1, 0)
            					);
			// show lock/unlock button and edit buttons depending on user rights
			//$markerArray["###ICON_LOCK###"] = $this->getAdminLockButton();
	        if($this->isUserWikiAdmin()) {
				// Add Edit and versions button as standard buttons for admins
                $markerArray["###ICON_EDIT###"] = $this->makeIconLink(
                				$this->cObj->cObjGetSingle($this->conf["iconEdit"], $this->conf["iconEdit."]),
                				$this->pi_linkTP_keepPIvars_url(array("cmd" => "edit", "submit" => ""), 1, 0)
                				); 
                $markerArray["###ICON_VERSIONS###"] = $this->makeIconLink(
                				$this->cObj->cObjGetSingle($this->conf["iconVersions"], $this->conf["iconVersions."]),
                				$this->pi_linkTP_keepPIvars_url(array("cmd" => "list", "showUid" => ""), 1, 0)
                				); 
	   			
	        	if ($this->isRecordLocked($this->piVars["keyword"])) {
	        		$markerArray["###ICON_LOCK###"] = $this->pi_linkTP_keepPIvars($this->cObj->cObjGetSingle($this->conf["iconUnLock"], $this->conf["iconUnLock."]), array("cmd" => "lock", "showUid" => ""), 1, 0);
	        	} else {
	        		$markerArray["###ICON_LOCK###"] = $this->pi_linkTP_keepPIvars($this->cObj->cObjGetSingle($this->conf["iconLock"], $this->conf["iconLock."]), array("cmd" => "lock", "showUid" => ""), 1, 0);
	        	}
	        } else {
	        	//for the other user show padlock icon without links
	        	if (!$this->getFieldContent("locked") xor $this->read_only){
	        		$markerArray["###ICON_LOCK###"] = '';
	                $markerArray["###ICON_EDIT###"] = $this->makeIconLink(
	                				$this->cObj->cObjGetSingle($this->conf["iconEdit"], $this->conf["iconEdit."]),
	                				$this->pi_linkTP_keepPIvars_url(array("cmd" => "edit", "submit" => ""), 1, 0)
	                				); 
	                $markerArray["###ICON_VERSIONS###"] = $this->makeIconLink(
	                				$this->cObj->cObjGetSingle($this->conf["iconVersions"], $this->conf["iconVersions."]),
	                				$this->pi_linkTP_keepPIvars_url(array("cmd" => "list", "showUid" => ""), 1, 0)
	                				); 	        		
	        	} else {
	        		$markerArray["###ICON_LOCK###"] = $this->cObj->cObjGetSingle($this->conf["iconLock"], $this->conf["iconLock."]);
	        		$markerArray["###ICON_EDIT###"] = "";
                	$markerArray["###ICON_VERSIONS###"] = "";
	        	}
	        }
 
            $markerArray["###ICON_GETHTML###"] = $this->makeIconLink(
            					$this->cObj->cObjGetSingle($this->conf["iconGetHTML"], $this->conf["iconGetHTML."]),
            					$this->pi_linkTP_keepPIvars_url(array("cmd" => "getHTML"), 1, 0)
            					);
                                    
            $markerArray["###EDIT_PANEL###"] = $this->pi_getEditPanel();
           
           // exchange Ratings Marker
            if($this->ffConf['enableRatings'] == 1){
            	$markerArray["###RATINGS###"] = $this->getRatings($this->internal["currentRow"]);
            }else{
            	$markerArray["###RATINGS###"]='';
            }
            
            return $this->cObj->substituteMarkerArrayCached($subpart, $markerArray);
        }

	function makeIconLink($text,$link,$title =''){

        $temp_conf = $this->conf["typolink."];
        $temp_conf["parameter"] = "{$link}";
        $temp_conf["ATagParams"] = 'title="'.$title.'" rel="nofollow"';

        return $this->cObj->typoLink($text, $temp_conf);
	}

    /**
     * Ratings Display
     *
     * @param array $page to fech the UID
     * @return 'HTML'
     */
    	function getRatings($page) {
		if ($this->ratingsApiObj) {
			$conf=$this->ratingsApiObj->getDefaultConfig();
			$conf['storagePid'] = $this->ffConf['ratingsStoragePid'];
			$conf['templateFile'] = $this->ffConf['ratingsTemplateFile'];
			return $this->ratingsApiObj->getRatingDisplay('tx_drwiki_pages_' . $page['keyword'], $conf);
		}
		return '';
	}

    /**
     * getFieldContent
     *
     * Returns the formatted field-value
     *
     * @param	[string]		$fN: The Field to display
     * @return	[string]		formatted field-value
     */
        function getFieldContent($fN, $linkUID=1)
        {
            switch($fN)
            {
                case "uid":
                    if ($linkUID==1)
                        {return $this->makeIconLink($this->internal["currentRow"][$fN], $this->pi_linkTP_keepPIvars_url(array("cmd" => "", "showUid" => $this->internal["currentRow"][$fN], "keyword" => "", "sort" => ""), 1, 0));}
                    else
                        {return $this->internal["currentRow"][$fN];}
                case "tstamp":
                    return strftime("%d.%m.%Y %H:%M", $this->internal["currentRow"][$fN]);
                 break;
                 case "diff":
                    return $this->makeIconLink($this->pi_getLL('pi_diff_link_latest_ver','compare to newest'), $this->pi_linkTP_keepPIvars_url(array("showUid" => "","pointer" => "", "cmd" => "list", "diff_uid" => $this->internal["currentRow"]["uid"], "keyword" => $this->internal["currentRow"]["keyword"], "sort" => ""), 1, 0)) ;
                 break;

                default:
                    if ($linkUID==0 && $fN=="keyword")
                        {return $this->makeIconLink($this->internal["currentRow"][$fN], $this->pi_linkTP_keepPIvars_url(array("cmd" => "", "showUid" => "", "keyword" => $this->internal["currentRow"][$fN], "sort" => ""), 1, 0));}
                    else
                        {return $this->internal["currentRow"][$fN];}
                 break;
            }
        }
        
    /**
     * getCacheContent
     *
     * Returns the formatted field-value
     *
     * @param	[string]		$fN: The Field to display
     * @return	[string]		formatted field-value
     */
        function getCacheContent($fN)
        {
                return $this->internal["currentCache"][$fN];
        }     
  
     /**
     * sanitizeValues
     *
     */
           
	  function sanitizeValues($markerArray) {
	
			foreach ($markerArray as $key => $value) {
				if(is_array($value)) {
					$key = htmlentities($this->sanitizer->sanitize($key));
					$sanitizedArray[$key] = $this->sanitizeValues($value);
				} else {
					$value = str_replace("\t","",$value);
					$key = htmlentities($this->sanitizer->sanitize($key));
					$sanitizedArray[$key] = $this->sanitizer->sanitize($value);
				}
			}
			return $sanitizedArray;
		}

// Wiki Functions
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    /**
     * @return string Complete article text, or null if error
     */
    function replaceSection($section, $text, $oldtext) {
  
        if ($section != '') {

			# strip NOWIKI etc. from oldtext to avoid confusion 
	    	$unique = '23133214jlsdflj235l23j4l-nowiki-';
	        $numMatches = preg_match_all( '/<nowiki>(.*?)<\/nowiki>/xis', $text, $striparray );
	        for($i=0; $i<$numMatches; $i++){
	            $oldtext = str_replace($striparray[0][$i], $unique . $i, $oldtext);
	        }
            # now that we can be sure that no pseudo-sections are in the source,
            # split it up
            # Unfortunately we can't simply do a preg_replace because that might
            # replace the wrong section, so we have to use the section counter instead
            $secs=preg_split('/(^=+.+?=+|^<h[1-6].*?>.*?<\/h[1-6].*?>)(?!\S)/mi',
              $oldtext,-1,PREG_SPLIT_DELIM_CAPTURE);
            $secs[$section*2]=$text."\n\n"; // replace with edited

            # section 0 is top (intro) section
            if($section!=0) {

                # headline of old section - we need to go through this section
                # to determine if there are any subsections that now need to
                # be erased, as the mother section has been replaced with
                # the text of all subsections.
                $headline=$secs[$section*2-1];
                preg_match( '/^(=+).+?=+|^<h([1-6]).*?>.*?<\/h[1-6].*?>(?!\S)/mi',$headline,$matches);
                $hlevel=$matches[1];

                # determine headline level for wikimarkup headings
                if(strpos($hlevel,'=')!==false) {
                    $hlevel=strlen($hlevel);
                }

                $secs[$section*2-1]=''; // erase old headline
                $count=$section+1;
                $break=false;
                while(!empty($secs[$count*2-1]) && !$break) {

                    $subheadline=$secs[$count*2-1];
                    preg_match(
                     '/^(=+).+?=+|^<h([1-6]).*?>.*?<\/h[1-6].*?>(?!\S)/mi',$subheadline,$matches);
                    $subhlevel=$matches[1];
                    if(strpos($subhlevel,'=')!==false) {
                        $subhlevel=strlen($subhlevel);
                    }
                    if($subhlevel > $hlevel) {
                        // erase old subsections
                        $secs[$count*2-1]='';
                        $secs[$count*2]='';
                    }
                    if($subhlevel <= $hlevel) {
                        $break=true;
                    }
                    $count++;
                }
            }
            $text=join('',$secs);
	        //re-insert html comments
			for($i=0; $i<$numMatches; $i++){
			    $text = str_replace($unique . $i, $striparray[0][$i], $text);
			}
        }
        return trim($text);
    }
    
    
	/**
	 * This function returns the text of a section, specified by a number ($section).
	 * A section is text under a heading like == Heading == or <h1>Heading</h1>, or
	 * the first section before any such heading (section 0).
	 *
	 * If a section contains subsections, these are also returned.
	 *
	 * @param string $text text to look in
	 * @param integer $section section number
	 * @return string text of the requested section
	 */
	function getSection($text,$section) {
		# strip NOWIKI etc. to avoid confusion 
    	$unique = '23133214jlsdflj235l23j4l-nowiki-';
        $numMatches = preg_match_all( '/<nowiki>(.*?)<\/nowiki>/xis', $text, $striparray );
        for($i=0; $i<$numMatches; $i++){
            $text = str_replace($striparray[0][$i], $unique . $i, $text);
        }

        # now that we can be sure that no pseudo-sections are in the source,
        # split it up by section
        $secs =
          preg_split(
          '/(^=+.+?=+|^<h[1-6].*?>.*?<\/h[1-6].*?>)(?!\S)/mi',
          $text, -1,
          PREG_SPLIT_DELIM_CAPTURE);
        if($section==0) {
            $rv=$secs[0];
        } else {
            $headline=$secs[$section*2-1];
            preg_match( '/^(=+).+?=+|^<h([1-6]).*?>.*?<\/h[1-6].*?>(?!\S)/mi',$headline,$matches);
            $hlevel=$matches[1];

            # translate wiki heading into level
            if(strpos($hlevel,'=')!==false) {
                $hlevel=strlen($hlevel);
            }

            $rv=$headline. $secs[$section*2];
            $count=$section+1;

            $break=false;
            while(!empty($secs[$count*2-1]) && !$break) {

                $subheadline=$secs[$count*2-1];
                preg_match( '/^(=+).+?=+|^<h([1-6]).*?>.*?<\/h[1-6].*?>(?!\S)/mi',$subheadline,$matches);
                $subhlevel=$matches[1];
                if(strpos($subhlevel,'=')!==false) {
                    $subhlevel=strlen($subhlevel);
                }
                if($subhlevel > $hlevel) {
                    $rv.=$subheadline.$secs[$count*2];
                }
                if($subhlevel <= $hlevel) {
                    $break=true;
                }
                $count++;

            }
        }
		//re-insert html comments
		for($i=0; $i<$numMatches; $i++){
		    $rv = str_replace($unique . $i, $striparray[0][$i], $rv);
		}
		return $rv;

	}

    /**
     * keywordExists
     *
     * Returns the keyword or NULL, if no keyword is found
     *
     * @param	[string]		$keyword: The keyword to search for
     * @return	[string]		keyword or NULL
     */
        function keywordExists($keyword) {
            $pidList = $this->pi_getPidList($this->conf["pidList"], $this->conf["recursive"]);
            $keyword = $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($keyword),'tx_drwiki_pages');
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'keyword',
                'tx_drwiki_pages',
                'tx_drwiki_pages.pid IN ('.$pidList.')'.$this->cObj->enableFields('tx_drwiki_pages').' AND keyword = '.$keyword,
                '',
                'uid DESC',
                '1'
            );
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            return $row["keyword"];
        }
        
    /**
     * isRecordLocked
     *
     * Returns if the last record is locked
     *
     * @param	[string]		$keyword: The keyword to search for
     * @return	[Boolean]		is record locked?
     */
        function isRecordLocked($keyword) {
             $pidList = $this->pi_getPidList($this->conf["pidList"], $this->conf["recursive"]);
            
            $keyword = $GLOBALS['TYPO3_DB']->fullQuoteStr($keyword,'tx_drwiki_pages');

            // The newest version has the highest uid
            // Patch for latest UID Query provided by Kasper M. Petersen
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('locked','tx_drwiki_pages',
            'tx_drwiki_pages.pid IN ('.$pidList.')'.$this->cObj->enableFields("tx_drwiki_pages").' AND keyword='.$keyword,
            '','uid DESC');
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	        return $row["locked"];
        }   
/**
 * togglePageLock
 * 
 * toggles the lock property of a wiki page
 * 
 * @param [string]	$keyword: The page to be un-/locked
 * 
 */        
        function togglePageLock($keyword) {
        	$where = "uid=". $this->getUid($keyword);
			$what = array("locked" => !$this->isRecordLocked($keyword));
			$db = $GLOBALS["TYPO3_DB"];
			$db->exec_UPDATEquery("tx_drwiki_pages",$where,$what);
        }     
        
/**
 * activateHiddenItem
 * 
 */        
        function activateHiddenItem($uid) {
        	$where = "uid=". $uid;
			$what = array("hidden" => false);
			$db = $GLOBALS["TYPO3_DB"];
			$db->exec_UPDATEquery("tx_drwiki_pages",$where,$what);
        }             
        
        function getNameSpace ($keyword) {
        	$getNS = preg_match_all( '/(.*)(:)(.*)/', $keyword, $matchesNS );
	        // return Namespace entry
	            return $matchesNS[1][0];
        }
        
       function getKeywordFromNameSpace ($keyword) {
        	$getNS = preg_match_all( '/(.*)(:)(.*)/', $keyword, $matchesNS );
	        // return Namespace entry
	            return $matchesNS[3][0];
        }
        
        function isNameSpace($namespace){
            $dummy = "";
            $dummy = $this->nameSpaces[$namespace];
            if ($dummy ==$this->nameSpaces["Template"]) {return false;}
            if ($dummy ==$this->nameSpaces["User"]) {return false;}
            if ($dummy ==$this->nameSpaces["Help"]) {return false;}
            if ($dummy ==$this->nameSpaces["Category"]) {return false;}
            if ($dummy ==$this->nameSpaces["Discussion"]) {return false;}
            if ($dummy) {return true;}
                else {return false;}
        }
    /**
     * linkKeyword
     *
     * returns a link-wrapped keyword. If the Keyword exist a link to the single-display it is
     * wrapped around, if not (?) is added and the links leads to the creation of a new page
     *
     * [[Wikipedia:FAQ|Fragen und Antworten]].
     * Hide Namespaces (array?)
     *
     *
     * Furthermore, the Namespaces and the InterWiki links are handled in this function
     *
     * @param	[string]		$keyword: The keyword to be linked
     * @return	[string]		link-wrapped keyword
     */
        function linkKeyword($keyword, $trail="")
        {
            $trail .= " ";
            
            $numMatches = preg_match_all( '/(.*?)\|(.*)/', $keyword, $matches );
            $keyword = $matches[1][0] ? $matches[1][0] : $keyword;
            
            $getNS = preg_match_all( '/(.*)(:)(.*)/', $keyword, $matchesNS );  
            $specificNS = $matchesNS[1][0];
            $specificNSKeyword = $matchesNS[3][0];

            $word = $matchesNS[3][0] ? $matchesNS[3][0] : $keyword;            
            $LinkText = $matches[2][0] ? $matches[2][0] : $keyword;
            // This also works, when displaying a Namespace.... better code needed:
            // $LinkText = $matchesNS[3][0] ? $matchesNS[3][0] : $keyword;

            // is trail a HTML tag, do not add it to LinkText
            if (!(substr_count($trail,"<")>=1))
            {
                $LinkText .= trim($trail);
                $trail = " ";
            }
            
            $this->piVars["pluginSEARCH"]["sword"] = "";
            $this->piVars["pluginSEARCH"]["submit"] = "";
            
            
            // handle Category Links differently, so they are later on displayed in the footer
            // Check if the $keyword is a category and also check if $keyword is 
            // the current page keyword --> if yes, just remove the link in order to not have circular
            // categories, which can cause problems
            if ($specificNS == $this->nameSpaces["Category"] and $keyword != $this->piVars["keyword"]) {
                // prevent double occurences of categories in footer array AND
                // empty Category entries
                if (!in_array($keyword, $this->categoryIndex) AND $word != $this->nameSpaces["Category"].':') {
	                $categoryLink = $this->pi_linkTP_keepPIvars($specificNSKeyword, array("keyword" => $keyword, "showUid" => ""), 1, 0);
	            	$this->categoryIndex[$keyword] = array("keyword" => $specificNSKeyword, "specificNS" => $specificNS, "linkText" => $LinkText, "catgoryLink" => $categoryLink);
                } else return;        	
            	return;
            } else if ($specificNS == $this->nameSpaces["Category"] and $keyword == $this->piVars["keyword"]) return;     
            
            // check for external Links and InterWiki links, but exclude real namespaces
            // uses the $specificNS function for checking Namespaces
            if ($getNS AND $this->isNameSpace($specificNS)) {
                if ($this->nameSpaces[$specificNS] == "") {
                    $link = "";
                } else {
                    $link = $this->nameSpaces[$specificNS].$word;
                    $link = preg_replace("| |","%20",$link); //replace whitespaces
                }

                $temp_conf = $this->conf["typolink."];
                $temp_conf["parameter"] = "{$link} {$this->extLinkTarget}";
                $temp_conf["ATagParams"] = "title='{$specificNS}: {$word}'";

                //return $this->cObj->typoLink($matchesNS[3][0], $temp_conf).$trail;
                return $this->cObj->typoLink($LinkText, $temp_conf).$trail;
            } else {
                if ($this->keywordExists(trim($keyword)) == NULL)
                {
                    // Create new KeyWord / Internal Link
                    $LinkText = '<span class="notCreated">'.$LinkText.'<sup><b>?</b></sup></span>';
                    return $this->pi_linkTP_keepPIvars($LinkText, array("keyword" => $keyword, "referer" => $this->piVars["keyword"], "showUid" => ""), 1, 0).$trail;
                } elseif($keyword ==$this->piVars["keyword"]) {
                    return $this->pi_linkTP_keepPIvars("<b>".$LinkText."</b>", array("keyword" => $keyword ,"showUid" => ""), 1, 0).$trail;  
                } else {
                    return $this->pi_linkTP_keepPIvars($LinkText, array("keyword" => $keyword, "showUid" => ""), 1, 0).$trail;
                }
            }
        }

    /**
     * getSubpart
     *
     * returns the content of the subpart marked with {###<$marker>###} out of $content
     *
     * @param	[string]		$content: strign to search $marker in
     * @param	[string]		$marker: the subpart-marker
     * @return	[string]		content of the subpart
     */
        function getSubpart($content, $marker)
        {
            $marker = "{" . $marker . "}";

            if ($marker && strstr($content, $marker))
            {
                $start = strpos($content, $marker)+strlen($marker);
                $stop = @strpos($content, $marker, $start);
                $sub = substr($content, $start, $stop-$start);

                return $sub;
            }
        }

    /**
     * substituteSubpart
     *
     * returns a string where a marked subpart is replaced by $subpartContent
     *
     * Parameters:
     * $content (string)  - strign to search $marker in
     * $marker (string)  - the subpart-marker
     * $subpartContent (string) - the text to replace the subpart with
     *
     *
     * 
     *
     * @param	[type]		$content: ...
     * @param	[type]		$marker: ...
     * @param	[type]		$subpartContent: ...
     * @return	[type]		...
     */
        function substituteSubpart($content, $marker, $subpartContent)
        {

            $orgMarker = $marker;
            $marker = "{" . $marker . "}";

            $start = strpos($content, $marker);
            $stop = @strpos($content, $marker, $start+strlen($marker))+strlen($marker);

            if (!($start === FALSE) && $start < $stop)
            {
                // gefunden
                $before = substr($content, 0, $start);
                $after = substr($content, $stop);
                $replaced = $before . $subpartContent . $this->substituteSubpart($after, $orgMarker, $subpartContent);

                return $replaced;
            }
            else
                {
                return $content;
            }
        }

    /**
    * substitutePlugins
    *
    * substitutes pluginblocks with the plugin-content
    *
    * plugin-blocks must be marked in the following form:
    * {###<PLUGIN_NAME>###}[Parameter1][,Parameter2]{###<PLUGIN_NAME>###}
    *
    * Example:
    * {###LASTCHANGED###}7{###LASTCHANGED###}
    *
    * @param	string		$str: the content to search in
    * @return	string		result of the Plugin
    */
        function substitutePlugins($str)
        {
            // let's do it the regex way...
            $unique = '23133214jlsdflj235l23j4l-plugin-';
            
            // match patterns
            $numMatches = preg_match_all( '/\{\#\#\#('.$this->pluginList.')\#\#\#\}(.*?)\{\#\#\#('.$this->pluginList.')\#\#\#\}/xis', $str, $matches );
            for($i=0; $i<$numMatches; $i++){
                $str = str_replace($matches[0][$i], $unique . $i, $str);
                //check parameters
                $params = '';
                if ($matches[2][$i]) {
	                // the plugin has parameters --> get them
	                $params = explode("|", $matches[2][$i]);
                } else {
	                // no parameters given, get the default ones
	                $params = $this->pluginArray[$matches[1][$i]]->getDefaultParams();
                }
                $dummy = $this->pluginArray[$matches[1][$i]]->main($this, $params);
                $str = str_replace($unique . $i, $dummy, $str);
            }
            return $str;
        }
        
    /**
    * testForPlugIns
    *
    */
    	function testForPlugIns($str) 
    	{
    	
            // reset the array
            reset($this->pluginArray);

            // any plugins present - if yes set true and exit
            while (list($pluginName, $pluginObject) = each($this->pluginArray))
            {
                if (strpos($str, "###".$pluginName."###") > 0) 
                    {return true;} 
                else {return false;}    		
            }
    	}

		/**
		 * TODO: Get rid of http: to be shown without a link
		 * TODO: Fix this regex to match all blanks that are first in a row
		 * 		 in order to prevent this to be matched as comments...
		 */
		function processNoWikiTags($str) {
			//preserve html comments
            $unique = '23133214jlsdflj235l23j4l-nowiki-';
            $numMatches = preg_match_all( '/<nowiki>(.*?)<\/nowiki>/xis', $str, $matches );
			$wikiEntities = array('#','[','[[',']',']]','*','{','|','}','\'','\r\n','\n','\r','=');
			$htmlReplacements = array('&#35;','&#91;','&#91;&#91;','&#93;','&#93;&#93;','&#42;','&#123;','&#124;','&#125;','&#96;','<br />','<br />','<br />','&#61;');            
            // TODO: Match for ' ' on the start of a line to avaiod <tt> formatting
            for($i=0; $i<$numMatches; $i++){
                $str = str_replace($matches[0][$i], $unique . $i, $str);
                $dummy = htmlspecialchars($matches[1][$i]);
                $dummy = str_replace($wikiEntities, $htmlReplacements, $dummy);
                $dummy = preg_replace('/(^|\n)-----*/', '&#45;&#45;&#45;&#45;&#45;', $dummy );
                
                //$dummy = preg_replace("/^.\b/msi", "&nbsp;", $dummy); 
                $str = str_replace($unique . $i, $dummy, $str);                
            }
           return $str; 
		} 

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$text: ...
	 * @return	[type]		...
	 */
        function doQuotes($text )
        {
            $arr = preg_split ("/(''+)/", $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            if (count ($arr) == 1)
                return $text;
            else
                {
                # First, do some preliminary work. This may shift some apostrophes from
                # being mark-up to being text. It also counts the number of occurrences
                # of bold and italics mark-ups.
                $i = 0;
                $numbold = 0;
                $numitalics = 0;
                foreach ($arr as $r)
                {
                    if (($i % 2) == 1)
                        {
                        # If there are ever four apostrophes, assume the first is supposed to
                        # be text, and the remaining three constitute mark-up for bold text.
                        if (strlen ($arr[$i]) == 4)
                            {
                            $arr[$i-1] .= "'";
                            $arr[$i] = "'''";
                        }
                        # If there are more than 5 apostrophes in a row, assume they're all
                        # text except for the last 5.
                        else if (strlen ($arr[$i]) > 5)
                        {
                            $arr[$i-1] .= str_repeat ("'", strlen ($arr[$i]) - 5);
                            $arr[$i] = "'''''";
                        }
                        # Count the number of occurrences of bold and italics mark-ups.
                        # We are not counting sequences of five apostrophes.
                        if (strlen ($arr[$i]) == 2) $numitalics++;
                        else
                        if (strlen ($arr[$i]) == 3) $numbold++;
                        else
                        if (strlen ($arr[$i]) == 5)
                        {
                            $numitalics++;
                            $numbold++;
                        }
                    }
                    $i++;
                }

                # If there is an odd number of both bold and italics, it is likely
                # that one of the bold ones was meant to be an apostrophe followed
                # by italics. Which one we cannot know for certain, but it is more
                # likely to be one that has a single-letter word before it.
                if (($numbold % 2 == 1) && ($numitalics % 2 == 1))
                    {
                    $i = 0;
                    $firstsingleletterword = -1;
                    $firstmultiletterword = -1;
                    $firstspace = -1;
                    foreach ($arr as $r)
                    {
                        if (($i % 2 == 1) and (strlen ($r) == 3))
                            {
                            $x1 = substr ($arr[$i-1], -1);
                            $x2 = substr ($arr[$i-1], -2, 1);
                            if ($x1 == " ")
                            {
                                if ($firstspace == -1) $firstspace = $i;
                            }
                            else if ($x2 == " ")
                            {
                                if ($firstsingleletterword == -1) $firstsingleletterword = $i;
                            }
                            else
                            {
                                if ($firstmultiletterword == -1) $firstmultiletterword = $i;
                            }
                        }
                        $i++;
                    }

                    # If there is a single-letter word, use it!
                    if ($firstsingleletterword > -1)
                        {
                        $arr [ $firstsingleletterword ] = "''";
                        $arr [ $firstsingleletterword-1 ] .= "'";
                    }
                    # If not, but there's a multi-letter word, use that one.
                    else if ($firstmultiletterword > -1)
                    {
                        $arr [ $firstmultiletterword ] = "''";
                        $arr [ $firstmultiletterword-1 ] .= "'";
                    }
                    # ... otherwise use the first one that has neither.
                    # (notice that it is possible for all three to be -1 if, for example,
                    # there is only one pentuple-apostrophe in the line)
                    else if ($firstspace > -1)
                    {
                        $arr [ $firstspace ] = "''";
                        $arr [ $firstspace-1 ] .= "'";
                    }
                }

                # Now let's actually convert our apostrophic mush to HTML!
                $output = '';
                $buffer = '';
                $state = '';
                $i = 0;
                foreach ($arr as $r)
                {
                    if (($i % 2) == 0)
                        {
                        if ($state == 'both')
                            $buffer .= $r;
                        else
                            $output .= $r;
                    }
                    else
                        {
                        if (strlen ($r) == 2)
                            {
                            if ($state == 'em')
                                {
                                $output .= "</em>";
                                $state = '';
                            }
                            else if ($state == 'strongem')
                            {
                                $output .= "</em>";
                                $state = 'strong';
                            }
                            else if ($state == 'emstrong')
                            {
                                $output .= "</strong></em><strong>";
                                $state = 'strong';
                            }
                            else if ($state == 'both')
                            {
                                $output .= "<strong><em>{$buffer}</em>";
                                $state = 'strong';
                            }
                            else # $state can be 'strong' or ''
                            {
                                $output .= "<em>";
                                $state .= 'em';
                            }
                        }
                        else if (strlen ($r) == 3)
                        {
                            if ($state == 'strong')
                                {
                                $output .= "</strong>";
                                $state = '';
                            }
                            else if ($state == 'strongem')
                            {
                                $output .= "</em></strong><em>";
                                $state = 'em';
                            }
                            else if ($state == 'emstrong')
                            {
                                $output .= "</strong>";
                                $state = 'em';
                            }
                            else if ($state == 'both')
                            {
                                $output .= "<em><strong>{$buffer}</strong>";
                                $state = 'em';
                            }
                            else # $state can be 'em' or ''
                            {
                                $output .= "<strong>";
                                $state .= 'strong';
                            }
                        }
                        else if (strlen ($r) == 5)
                        {
                            if ($state == 'strong')
                                {
                                $output .= "</strong><em>";
                                $state = 'em';
                            }
                            else if ($state == 'em')
                            {
                                $output .= "</em><strong>";
                                $state = 'strong';
                            }
                            else if ($state == 'strongem')
                            {
                                $output .= "</em></strong>";
                                $state = '';
                            }
                            else if ($state == 'emstrong')
                            {
                                $output .= "</strong></em>";
                                $state = '';
                            }
                            else if ($state == 'both')
                            {
                                $output .= "<em><strong>{$buffer}</strong></em>";
                                $state = '';
                            }
                            else // ($state == '')
                            {
                                $buffer = '';
                                $state = 'both';
                            }
                        }
                    }
                    $i++;
                }
                # Now close all remaining tags.  Notice that the order is important.
                if ($state == 'strong' || $state == 'emstrong')
                    $output .= "</strong>";
                if ($state == 'em' || $state == 'strongem' || $state == 'emstrong')
                    $output .= "</em>";
                if ($state == 'strongem')
                    $output .= "</strong>";
                if ($state == 'both')
                    $output .= "<strong><em>{$buffer}</em></strong>";
                return $output;
            }
        }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$text: ...
	 * @return	[type]		...
	 */
        function doAllQuotes($text )
        {
            $outtext = '';
            $lines = explode("\n", $text );
            foreach ($lines as $line )
            {
                $outtext .= $this->doQuotes ($line ) . "\n";
            }
            $outtext = substr($outtext, 0, -1);
            return $outtext;
        }

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
        function getHTMLattrs ()
        {
            $htmlattrs = array(# Allowed attributes--no scripting, etc.
            'title', 'align', 'lang', 'dir', 'width', 'height',
                'bgcolor', 'clear', /* BR */ 'noshade', /* HR */
            'cite', /* BLOCKQUOTE, Q */ 'size', 'face', 'color',
            /* FONT */ 'type', 'start', 'value', 'compact',
            /* For various lists, mostly deprecated but safe */
            'summary', 'width', 'border', 'frame', 'rules',
                'cellspacing', 'cellpadding', 'valign', 'char',
                'charoff', 'colgroup', 'col', 'span', 'abbr', 'axis',
                'headers', 'scope', 'rowspan', 'colspan', /* Tables */
            'id', 'class', 'name', 'style' /* For CSS */
            );
            return $htmlattrs ;
        }

        # Remove non approved attributes and javascript in css
        function fixTagAttributes ($t )
        {
            if (trim ($t ) == '' ) return '' ; # Saves runtime ;-)
            $htmlattrs = $this->getHTMLattrs() ;

            # Strip non-approved attributes from the tag
            $t = preg_replace(
            '/(\\w+)(\\s*=\\s*([^\\s\">]+|\"[^\">]*\"))?/e',
                "(in_array(strtolower(\"\$1\"),\$htmlattrs)?(\"\$1\".((\"x\$3\" != \"x\")?\"=\$3\":'')):'')",
                $t);

            return trim ($t ) ;
        }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$$url: ...
	 * @param	[type]		$alt: ...
	 * @return	[type]		...
	 */
	function makeImage( $url, $alt = '', $align ='', $hspace ='', $vspace='' )
	{
            if ( '' == $alt ) { $alt = $url; }
            if ( $align ) { 
            	$addAlign = '" align="'.$align.'"';
	    	} else {
	    		$addAlign = '';
	    	}
            if ( $hspace ) { 
            	$addHspace = '" hspace="'.$hspace.'"';
	    	} else {
	    		$addHspace = '';
	    	}
	    	if ( $vspace ) { 
            	$addVspace = '" vspace="'.$vspace.'"';
	    	} else {
	    		$addVspace = '';
	    	}
            $s = '<img src="'.$url.'" alt="'.$alt.$addAlign.$addVspace.$addVspace.'" />';
            return $s;
	}

        /* private */
        function replaceExternalLinks( $text ) { /* private */

            $text = $this->subReplaceExternalLinks( $text, 'http', true );
            $text = $this->subReplaceExternalLinks( $text, 'https', true );
            $text = $this->subReplaceExternalLinks( $text, 'ftp', false );
            $text = $this->subReplaceExternalLinks( $text, 'irc', false );
            $text = $this->subReplaceExternalLinks( $text, 'gopher', false );
            $text = $this->subReplaceExternalLinks( $text, 'news', false );
            $text = $this->subReplaceExternalLinks( $text, 'mailto', false );

            return $text;
	}

        /* private */
	function subReplaceExternalLinks( $s, $protocol, $autonumber ) {
            $unique = '4jzAfzB8hNvf4sqyO9Edd8pSmk9rE2in0Tgw3';
            $uc = "A-Za-z0-9_\\/~%\\-+&*#?!=()@\\x80-\\xFF";

            # this is  the list of separators that should be ignored if they
            # are the last character of an URL but that should be included
            # if they occur within the URL, e.g. "go to www.foo.com, where .."
            # in this case, the last comma should not become part of the URL,
            # but in "www.foo.com/123,2342,32.htm" it should.
            $sep = ",;\.:";
            $fnc = 'A-Za-z0-9_.,~%\\-+&;#*?!=()@\\x80-\\xFF';
            $images = 'gif|png|jpg|jpeg|svg';

            # PLEASE NOTE: The curly braces { } are not part of the regex,
            # they are interpreted as part of the string (used to tell PHP
            # that the content of the string should be inserted there).
            $e1 = "/(^|[^\\[])({$protocol}:)([{$uc}{$sep}]+)\\/([{$fnc}]+)\\." .
              "((?i){$images})([^{$uc}]|$)/";

            $e2 = "/(^|[^\\[])({$protocol}:)(([".$uc."]|[".$sep."][".$uc."])+)([^". $uc . $sep. "]|[".$sep."]|$)/";

            if ( $autonumber  ) { # Use img tags only for HTTP urls
                    $s = preg_replace( $e1, '\\1' . $this->makeImage( "{$unique}:\\3" .
                      '/\\4.\\5', '\\4.\\5' ) . '\\6', $s );
            }
            
            $s = preg_replace( $e2, '\\1' . "<a href=\"{$unique}:\\3\" target=\"{$this->extLinkTarget}\"" . ">" . "{$unique}:\\3"  .
              '</a>\\5', $s );
             
            $s = str_replace( $unique, $protocol, $s );

            $a = explode( "[{$protocol}:", " " . $s );
            $s = array_shift( $a );
            $s = substr( $s, 1 );

            $e1 = "/^([{$uc}"."{$sep}]+)](.*)\$/sD";
            $e2 = "/^([{$uc}"."{$sep}]+)\\s+([^\\]]+)](.*)\$/sD";

            foreach ( $a as $line ) {
                if ( preg_match( $e1, $line, $m ) ) {
                        $link = "{$protocol}:{$m[1]}";
                        $trail = $m[2];
                        if ( $autonumber ) { $text = "[" . ++$this->mAutonumber . "]"; }
                        else { $text = $link; }
                } else if ( preg_match( $e2, $line, $m ) ) {
                        $link = "{$protocol}:{$m[1]}";
                        $text = $m[2];
                        $trail = $m[3];
                } else {
                        $s .= "[{$protocol}:" . $line;
                        continue;
                }
                
                
                if( $link == $text || preg_match( "!$protocol://" . preg_quote( $text, "/" ) . "/?$!", $link ) ) {
                        $paren = '';
                } else {
                        # Expand the URL for printable version
                        $paren = "<span class=\"tx-drwiki-pi1-urlextension\"> (<i>" . htmlspecialchars ( $link ) . "</i>)</span>";
                }
                $myLinkTitle = urldecode( $link );
                $myLinkTitle = str_replace( '_', ' ', $myLinkTitle);
                
                //$s .= "<a href='{$link}' title='{$myLinkTitle}' target='{$this->extLinkTarget}'>{$text}</a>{$paren}{$trail}";
                $temp_conf = $this->conf["typolink."];
                //$temp_conf["wrap"] = "|{$paren}{$trail}"; //Typolink seems to be buggy... large strings are swallowed!
                $temp_conf["parameter"] = "{$link} {$this->extLinkTarget}";
                $temp_conf["ATagParams"] = "title={$myLinkTitle}";

                // Add image links?
                if(!$this->turnOffImageLink)
                {
                    $imageLink = "";
                    if ($protocol == 'http' || $protocol == 'https') {
                        $imageLink = $this->cObj->cObjGetSingle($this->conf["iconExtLink"], $this->conf["iconExtLink."]);
                    } else if ($protocol == 'mailto') {
                        $imageLink = $this->cObj->cObjGetSingle($this->conf["iconMailto"], $this->conf["iconMailto."]);
                    } else {
                        $imageLink = "";
                    }
                }
                // create the link including the image - add leading space " " for formatting 
                $s .= " ". $this->cObj->typoLink($text.$imageLink, $temp_conf).$paren.$trail;
            }
            return $s;
	}

######################
# These next three functions open, continue, and close the list
# element appropriate to the prefix character passed into them.
#

	function closeParagraph() {
		$result = '';
		if ( '' != $this->mLastSection ) {
			$result = '</' . $this->mLastSection  . ">\n";
		}
		$this->mInPre = false;
		$this->mLastSection = '';
		return $result;
	}

	# getCommon() returns the length of the longest common substring
	# of both arguments, starting at the beginning of both.
	#
	function getCommon( $st1, $st2 ) {
		$fl = strlen( $st1 );
		$shorter = strlen( $st2 );
		if ( $fl < $shorter ) { $shorter = $fl; }

		for ( $i = 0; $i < $shorter; ++$i ) {
			if ( $st1{$i} != $st2{$i} ) { break; }
		}
		return $i;
	}

	# These next three functions open, continue, and close the list
	# element appropriate to the prefix character passed into them.
	#
	function openList( $char ) {
		$result = $this->closeParagraph();

		if ( '*' == $char ) { $result .= '<ul><li>'; }
		else if ( '#' == $char ) { $result .= '<ol><li>'; }
		else if ( ':' == $char ) { $result .= '<dl><dd>'; }
		else if ( ';' == $char ) {
			$result .= '<dl><dt>';
			$this->mDTopen = true;
		}
		else { $result = '<!-- ERR 1 -->'; }

		return $result;
	}

	function nextItem( $char ) {
		if ( '*' == $char || '#' == $char ) { return '</li><li>'; }
		else if ( ':' == $char || ';' == $char ) {
			$close = '</dd>';
			if ( $this->mDTopen ) { $close = '</dt>'; }
			if ( ';' == $char ) {
				$this->mDTopen = true;
				return $close . '<dt>';
			} else {
				$this->mDTopen = false;
				return $close . '<dd>';
			}
		}
		return '<!-- ERR 2 -->';
	}

	function closeList( $char ) {
		if ( '*' == $char ) { $text = '</li></ul>'; }
		else if ( '#' == $char ) { $text = '</li></ol>'; }
		else if ( ':' == $char ) {
			if ( $this->mDTopen ) {
				$this->mDTopen = false;
				$text = '</dt></dl>';
			} else {
				$text = '</dd></dl>';
			}
		}
		else {	return '<!-- ERR 3 -->'; }
		return $text."\n";
	}
	/**#@-*/

	/**
	 * Make lists from lines starting with ':', '*', '#', etc.
	 *
	 * @access private
	 * @return string the lists rendered as HTML
	 */
	function doBlockLevels( $text, $linestart = true ) {

		# Parsing through the text line by line.  The main thing
		# happening here is handling of block-level elements p, pre,
		# and making lists from lines starting with * # : etc.
		#
		$textLines = explode( "\n", $text );

		$lastPrefix = $output = '';
		$this->mDTopen = $inBlockElem = false;
		$prefixLength = 0;
		$paragraphStack = false;

		if ( !$linestart ) {
			$output .= array_shift( $textLines );
		}
		foreach ( $textLines as $oLine ) {
			$lastPrefixLength = strlen( $lastPrefix );
			$preCloseMatch = preg_match('/<\\/pre/i', $oLine );
			$preOpenMatch = preg_match('/<pre/i', $oLine );
			if ( !$this->mInPre ) {
				# Multiple prefixes may abut each other for nested lists.
				$prefixLength = strspn( $oLine, '*#:;' );
				$pref = substr( $oLine, 0, $prefixLength );

				# eh?
				$pref2 = str_replace( ';', ':', $pref );
				$t = substr( $oLine, $prefixLength );
				$this->mInPre = !empty($preOpenMatch);
			} else {
				# Don't interpret any other prefixes in preformatted text
				$prefixLength = 0;
				$pref = $pref2 = '';
				$t = $oLine;
			}

			# List generation
			if( $prefixLength && 0 == strcmp( $lastPrefix, $pref2 ) ) {
				# Same as the last item, so no need to deal with nesting or opening stuff
				$output .= $this->nextItem( substr( $pref, -1 ) );
				$paragraphStack = false;

				if ( substr( $pref, -1 ) == ';') {
					# The one nasty exception: definition lists work like this:
					# ; title : definition text
					# So we check for : in the remainder text to split up the
					# title and definition, without b0rking links.
					$term = $t2 = '';
					if ($this->findColonNoLinks($t, $term, $t2) !== false) {
						$t = $t2;
						$output .= $term . $this->nextItem( ':' );
					}
				}
			} elseif( $prefixLength || $lastPrefixLength ) {
				# Either open or close a level...
				$commonPrefixLength = $this->getCommon( $pref, $lastPrefix );
				$paragraphStack = false;

				while( $commonPrefixLength < $lastPrefixLength ) {
					$output .= $this->closeList( $lastPrefix{$lastPrefixLength-1} );
					--$lastPrefixLength;
				}
				if ( $prefixLength <= $commonPrefixLength && $commonPrefixLength > 0 ) {
					$output .= $this->nextItem( $pref{$commonPrefixLength-1} );
				}
				while ( $prefixLength > $commonPrefixLength ) {
					$char = substr( $pref, $commonPrefixLength, 1 );
					$output .= $this->openList( $char );

					if ( ';' == $char ) {
						# FIXME: This is dupe of code above
						if ($this->findColonNoLinks($t, $term, $t2) !== false) {
							$t = $t2;
							$output .= $term . $this->nextItem( ':' );
						}
					}
					++$commonPrefixLength;
				}
				$lastPrefix = $pref2;
			}
			if( 0 == $prefixLength ) {
				# No prefix (not in list)--go to paragraph mode
				$uniq_prefix = UNIQ_PREFIX;
				// XXX: use a stack for nestable elements like span, table and div
				$openmatch = preg_match('/(<table|<blockquote|<h1|<h2|<h3|<h4|<h5|<h6|<pre|<tr|<p|<ul|<li|<\\/tr|<\\/td|<\\/th)/iS', $t );
				$closematch = preg_match(
					'/(<\\/table|<\\/blockquote|<\\/h1|<\\/h2|<\\/h3|<\\/h4|<\\/h5|<\\/h6|'.
					'<td|<th|<div|<\\/div|<hr|<\\/pre|<\\/p|'.$uniq_prefix.'-pre|<\\/li|<\\/ul)/iS', $t );
				if ( $openmatch or $closematch ) {
					$paragraphStack = false;
					$output .= $this->closeParagraph();
					if($preOpenMatch and !$preCloseMatch) {
						$this->mInPre = true;
					}
					if ( $closematch ) {
						$inBlockElem = false;
					} else {
						$inBlockElem = true;
					}
				} else if ( !$inBlockElem && !$this->mInPre ) {
					if ( ' ' == $t{0} and ( $this->mLastSection == 'pre' or trim($t) != '' ) ) {
						// pre
						if ($this->mLastSection != 'pre') {
							$paragraphStack = false;
							$output .= $this->closeParagraph().'<pre>';
							$this->mLastSection = 'pre';
						}
						$t = substr( $t, 1 );
					} else {
						// paragraph
						if ( '' == trim($t) ) {
							if ( $paragraphStack ) {
								$output .= $paragraphStack.'<br />';
								$paragraphStack = false;
								$this->mLastSection = 'p';
							} else {
								if ($this->mLastSection != 'p' ) {
									$output .= $this->closeParagraph();
									$this->mLastSection = '';
									$paragraphStack = '<p>';
								} else {
									$paragraphStack = '</p><p>';
								}
							}
						} else {
							if ( $paragraphStack ) {
								$output .= $paragraphStack;
								$paragraphStack = false;
								$this->mLastSection = 'p';
							} else if ($this->mLastSection != 'p') {
								$output .= $this->closeParagraph().'<p>';
								$this->mLastSection = 'p';
							}
						}
					}
				}
			}
			if ($paragraphStack === false) {
				$output .= $t."\n";
			}
		}
		while ( $prefixLength ) {
			$output .= $this->closeList( $pref2{$prefixLength-1} );
			--$prefixLength;
		}
		if ( '' != $this->mLastSection ) {
			$output .= '</' . $this->mLastSection . '>';
			$this->mLastSection = '';
		}
		return $output;
	}

	/**
	 * Split up a string on ':', ignoring any occurences inside
	 * <a>..</a> or <span>...</span>
	 * @param string $str the string to split
	 * @param string &$before set to everything before the ':'
	 * @param string &$after set to everything after the ':'
	 * return string the position of the ':', or false if none found
	 */
	function findColonNoLinks($str, &$before, &$after) {
		# I wonder if we should make this count all tags, not just <a>
		# and <span>. That would prevent us from matching a ':' that
		# comes in the middle of italics other such formatting....
		$pos = 0;
		do {
			$colon = strpos($str, ':', $pos);

			if ($colon !== false) {
				$before = substr($str, 0, $colon);
				$after = substr($str, $colon + 1);

				# Skip any ':' within <a> or <span> pairs
				$a = substr_count($before, '<a');
				$s = substr_count($before, '<span');
				$ca = substr_count($before, '</a>');
				$cs = substr_count($before, '</span>');

				if ($a <= $ca and $s <= $cs) {
					# Tags are balanced before ':'; ok
					break;
				}
				$pos = $colon + 1;
			}
		} while ($colon !== false);
		return $colon;
	}

# parse the wiki syntax used to render tables
function doTableStuff ($t )
{
    $t = explode ("\n" , $t ) ;
    $td = array ();
    # Is currently a td tag open?
    $ltd = array ();
    # Was it TD or TH?
    $tr = array ();
    # Is currently a tr tag open?
    $ltr = array ();
    # tr attributes
    foreach ($t AS $k => $x )
    {
        $x = trim ($x ) ;
        $fc = substr ($x , 0 , 1 ) ;
        if ('{|' == substr ($x , 0 , 2 ) )
            {
            $t[$k] = "\n<table " . $this->fixTagAttributes (substr ($x , 3 ) ) . '>' ;

            array_push ($td , false ) ;
            array_push ($ltd , '' ) ;
            array_push ($tr , false ) ;
            array_push ($ltr , '' ) ;
        }
        else if (count ($td ) == 0 )
        {
        }
        # Don't do any of the following
        else if ('|}' == substr ($x , 0 , 2 ) )
        {
            $z = "</table>\n" ;
            $l = array_pop ($ltd ) ;
            if (array_pop ($tr ) ) $z = '</tr>' . $z ;
            if (array_pop ($td ) ) $z = "</{$l}>" . $z ;
            array_pop ($ltr ) ;
            $t[$k] = $z ;
        }
        /*      else if ( "|_" == substr ( $x , 0 , 2 ) ) # Caption
        {
        $z = trim ( substr ( $x , 2 ) ) ;
        $t[$k] = "<caption>{$z}</caption>\n" ;
        }*/
        else if ('|-' == substr ($x , 0 , 2 ) ) # Allows for |---------------
        {
            $x = substr ($x , 1 ) ;
            while ($x != '' && substr ($x , 0 , 1 ) == '-' ) $x = substr ($x , 1 ) ;
            $z = '' ;
            $l = array_pop ($ltd ) ;
            if (array_pop ($tr ) ) $z = '</tr>' . $z ;
            if (array_pop ($td ) ) $z = "</{$l}>" . $z ;
            array_pop ($ltr ) ;
            $t[$k] = $z ;
            array_push ($tr , false ) ;
            array_push ($td , false ) ;
            array_push ($ltd , '' ) ;
            array_push ($ltr , $this->fixTagAttributes ($x ) ) ;
        }
        else if ('|' == $fc || '!' == $fc || '|+' == substr ($x , 0 , 2 ) ) # Caption
        {
            if ('|+' == substr ($x , 0 , 2 ) )
                {
                $fc = '+' ;
                $x = substr ($x , 1 ) ;
            }
            $after = substr ($x , 1 ) ;
            if ($fc == '!' ) $after = str_replace ('!!' , '||' , $after ) ;
            $after = explode ('||' , $after ) ;
            $t[$k] = '' ;
            foreach ($after AS $theline )
            {
                $z = '' ;
                if ($fc != '+' )
                    {
                    $tra = array_pop ($ltr ) ;
                    if (!array_pop ($tr ) ) $z = "<tr {$tra}>\n" ;
                    array_push ($tr , true ) ;
                    array_push ($ltr , '' ) ;
                }

                $l = array_pop ($ltd ) ;
                if (array_pop ($td ) ) $z = "</{$l}>" . $z ;
                if ($fc == '|' ) $l = 'td' ;
                else if ($fc == '!' ) $l = 'th' ;
                else if ($fc == '+' ) $l = 'caption' ;
                else $l = '' ;
                array_push ($ltd , $l ) ;
                $y = explode ('|' , $theline , 2 ) ;
                //patch provided by Daniel Minder 
				//TODO: get a better regex to unify these cases
                if (count ($y) != 1 and strpos($y[0], "[[") !== false) $y = array ( 0 => "{$y[0]}|{$y[1]}" );
                if (count ($y ) == 1 ) $y = "{$z}<{$l}>{$y[0]}" ;
                else $y = $y = "{$z}<{$l} ".$this->fixTagAttributes($y[0]).">{$y[1]}" ;
                $t[$k] .= $y ;
                array_push ($td , true ) ;
            }
        }
    }

    # Closing open td, tr && table
    while (count ($td ) > 0 )
    {
        if (array_pop ($td ) ) $t[] = '</td>' ;
        if (array_pop ($tr ) ) $t[] = '</tr>' ;
        $t[] = '</table>' ;
    }

    $t = implode ("\n" , $t ) ;
    return $t ;
}
   # Parse headers and return html
	/* private */ function doHeadings( $text ) {
            for ( $i = 6; $i >= 1; --$i ) {
                    $h = substr( '======', 0, $i );
                    $text = preg_replace( "/^{$h}(.+){$h}(\\s|$)/m",
                      "<h{$i}>\\1</h{$i}>\\2", $text );
            }
            return $text;
	}


    //Format heading
    //*********************************************************

    function tocIndent($level) {
            return str_repeat( '<div class="tocindent">'."\n", $level>0 ? $level : 0 );
    }

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$level: ...
	 * @return	[type]		...
	 */
    function tocUnindent($level) {
            return str_repeat( "</div>\n", $level>0 ? $level : 0 );
    }

    # parameter level defines if we are on an indentation level
    function doTocLine( $anchor, $tocline, $level ) {
            //$link = '<a href="'.'/intern/'.$_SERVER['QUERY_STRING'].'#'.$anchor.'">'.$tocline.'</a><br />';
            $link = '<a href="'.'/intern/#'.$anchor.'">'.$tocline.'</a><br />';
            if($level) {
                    return $link."\n";
            } else {
                    return '<div class="tocline">'.$link."</div>\n";
            }
    }
 /**
 * Adds the TOC table and the needed Toggle-Javascript to the page if needed.     * @param   string  toc
 *
 * @param	[string]		$toc: string containing the TOC
 * @return	void
 */
    function tocTable($toc) {


        $this->addJavaToggleScript();
        

        # note to CSS fanatics: putting this in a div does not work -- div won't auto-expand
        # try min-width & co when somebody gets a chance
        $hideline = ' <script type="text/javascript">showTocToggle()</script>';
        return
        '<table border="0" id="toc"><tr id="toctitle"><td align="center">'."\n".
        '<b>'.$this->pi_getLL('pi_toc_header', 'Contents').'</b>' .
        $hideline .
        '</td></tr><tr id="tocinside"><td>'."\n".
        $toc."</td></tr></table>\n";
    }
    
/**
 * Adds needed Toggle-Javascript to the page if needed. 
 */  
     function addJavaToggleScript() {
        //check if JavaScript is already registered
			$addParams = "var textShow ='".$this->pi_getLL('pi_toc_show', 'Show')."'\n var textHide ='".$this->pi_getLL('pi_toc_hide', 'Hide')."'\n";
            // add code to header
            $this->loadExtJS("res/wiki_toggle.js", $addParams); 
     }
	/**
	 * 
	 * 
	 */
	function getEditLink ($section, $linkText = 'Edit') {
		$url = $this->makeIconLink(
			   $linkText,
				$this->pi_linkTP_keepPIvars_url(array("cmd" => "edit", "submit" => "","section" => $section+1), 1, 0));
		$res = '<span class="editsection">['. $url .']</span>';
		return $res;
	}

    /**
     * Get all headlines for numbering them and adding funky stuff like page-links
     *
     * @param	string		text of the wiki-page
     * @return	string		formated wiki-page
     */
    function formatHeadings( $text ) {
        $forceTocHere = false; // add to wiki config

        // links - this is for later, but we need the number of headlines right now
        $numMatches = preg_match_all( '/<H([1-6])(.*?' . '>)(.*?)<\/H[1-6]>/i', $text, $matches );

        // if there are fewer than minHeaderCount headlines in the article, do not show TOC
        if( $numMatches < $this->minHeaderCount ) { //put in FF Config
            $this->doShowToc = 0;
        }

        // headline counter
        $headlineCount = 0;
        $sectionCount = 0; // headlineCount excluding template sections

        # Ugh .. the TOC should have neat indentation levels which can be
        # passed to the skin functions. These are determined here
        $toclevel = 0;
        $toc = '';
        $full = '';
        $head = array();
        $sublevelCount = array();
        $level = 0;
        $prevlevel = 0;
        foreach( $matches[3] as $headline ) {

                $numbering = '';
                if( $level ) {
                        $prevlevel = $level;
                }
                $level = $matches[1][$headlineCount];
                if( ( $this->doNumberHeadings || $this->doShowToc ) && $prevlevel && $level > $prevlevel ) {
                        # reset when we enter a new level
                        $sublevelCount[$level] = 0;
                        $toc .= $this->tocIndent( $level - $prevlevel );
                        $toclevel += $level - $prevlevel;
                }
                if( ( $this->doNumberHeadings || $this->doShowToc ) && $level < $prevlevel ) {
                        # reset when we step back a level
                        $sublevelCount[$level+1]=0;
                        $toc .= $this->tocUnindent( $prevlevel - $level );
                        $toclevel -= $prevlevel - $level;
                }
                # count number of headlines for each level
                @$sublevelCount[$level]++;
                if( $this->doNumberHeadings || $this->doShowToc ) {
                        $dot = 0;
                        for( $i = 1; $i <= $level; $i++ ) {
                                if( !empty( $sublevelCount[$i] ) ) {
                                        if( $dot ) {
                                                $numbering .= '.';
                                        }
                                        $numbering .= $sublevelCount[$i];
                                        $dot = 1;
                                }
                        }
                }

            # strip out HTML
            $canonized_headline = preg_replace( '/<.*?' . '>/','',$headline );
            $tocline = trim( $canonized_headline );
            $canonized_headline = urlencode( str_replace(' ', '_', $tocline));
            $replacearray = array(
                    '%3A' => ':',
                    '%' => '.'
            );
            $canonized_headline = str_replace(array_keys($replacearray),array_values($replacearray),$canonized_headline);
            //TODO: Check if this is OK
            $refers[$headlineCount] = $canonized_headline;

            # count how many in assoc. array so we can track dupes in anchors
            @$refers[$canonized_headline]++;
            $refcount[$headlineCount]=$refers[$canonized_headline];

            # Prepend the number to the heading text

            if( $this->doNumberHeadings || $this->doShowToc ) {
                    $tocline = $numbering . ' ' . $tocline;

                    # Don't number the heading if it is the only one (looks silly)
                    if( $this->doNumberHeadings && count( $matches[3] ) > 1) {
                            # the two are different if the line contains a link
                            $headline=$numbering . ' ' . $headline;
                    }
            }

            # Create the anchor for linking from the TOC to the section
            $anchor = $canonized_headline;
            if($refcount[$headlineCount] > 1 ) {
                    $anchor .= '_' . $refcount[$headlineCount];
            }
            if( $this->doShowToc && ( !isset($this->maxTocLevel) || $toclevel<$this->maxTocLevel ) ) {
                    $toc .= $this->doTocLine($anchor,$tocline,$toclevel);
            }

			# give headline the correct <h#> tag
            $editLink = $this->getEditLink($headlineCount);
            @$head[$headlineCount] .= "<a name=\"$anchor\"></a><h".$level.$matches[2][$headlineCount] . $editLink. $headline."</h".$level."> ";
            $headlineCount++;
        }
        # split up and insert constructed headlines
        if( $this->doShowToc ) {
                $toclines = $headlineCount;
                $toc .= $this->tocUnindent( $toclevel );
                $toc = $this->tocTable( $toc );
        }

        $blocks = preg_split( '/<H[1-6].*?' . '>.*?<\/H[1-6]>/i', $text );
        $i = 0;
		//TODO: Try to get this into the configuration for editing sections...
		$showEditLink = TRUE;
        foreach( $blocks as $block ) {
                if( $showEditLink && $headlineCount > 0 && $i == 0 && $block != "\n" ) {
                    # This is the [edit] link that appears for the top block of text when
                    # section editing is enabled


                    # Disabled because it broke block formatting
                    # For example, a bullet point in the top line
                    # $full .= $sk->editSectionLink(0);
                }
                $full .= $block;
                if( $this->doShowToc && !$i ){    //&& $isMain && !$forceTocHere) {
                # Top anchor now in skin
                        $full = $full.$toc;
                }

                if( !empty( $head[$i] ) ) {
                        $full .= $head[$i];
                }
                $i++;
        }
                return $full;
    }
    
/**
 * getWikiInfos
 *
 * Queries the database for general infos of the Wiki.
 * Especially used for the variables in the parse functions
 * 
 * @param	[string]		$what: field for the query
 * @param	[string]		$distinct: query database distinct?
 * @return	[string]		Result of the query
 */ 
    function getWikiInfos($what, $distinct = True, $keyword="", $getArray = False, $addWhereStatements=""){
    
        $modifier = $distinct ? "DISTINCT" : "";
        $pidList = $this->pi_getPidList($this->conf["pidList"], $this->conf["recursive"]);
        
        $query  =  "select ".$modifier." " .$what." from tx_drwiki_pages";
        $query .= " WHERE tx_drwiki_pages.pid IN (".$pidList.")".chr(10). $this->cObj->enableFields("tx_drwiki_pages").chr(10);
        $query .= $addWhereStatements;
        if ($keyword) {$query .= " AND keyword = '" . trim($keyword) . "'";}
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        $i = 0;
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $result[$i] = $row;
            $i = $i + 1;
        }
        
        
        if ($what == "author" AND !$getArray) {
            $str = "";
            foreach ($result as $entry) {
                $str = $str . " [[".$this->nameSpaces["User"].":".$entry[$what]."]]";
            }
            return $str;
        } else {
            return $result;
        }
    }
 
/**
 * processTemplate
 *
 * Cal-Back function for the template handling
 * Gets a template from the Template namespace of the wiki and returns the
 * pre-rendered Template, if the cache is enabled, or pre-renders the content
 * on-the-fly
 * 
 * also the variables are parsed, processed and replaced
 * 
 *     	$matches[0] --> complete string
 *   	$matches[1] --> linestart
 *    	$matches[2] --> template name
 *    	$matches[3] --> params
 * 
 * @param	[array]			array containing the name and the template variables
 * @return	[string]		Contents of the template
 */ 
    function processTemplate($matches) {
 		$args = $this->getTemplateArgs($matches[3]);
 		$argc = count ($args);
    	$template = $this->nameSpaces["Template"].':'.$matches[2];
    	$str = '';
    	$linestart = $matches[1];
    	
    	$templateExits = $this->keywordExists($template);

	    if ($templateExits) {
	        $pidList = $this->pi_getPidList($this->conf["pidList"], $this->conf["recursive"]);
	          
	            if ($this->enableWikiCaching) {
	                $table = 'tx_drwiki_cache';
	            } else {
	                $table = 'tx_drwiki_pages';
	            }
	    
	            $keyword = $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($template),$table);
	            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$table,$table.'.pid IN ('.$pidList.')'.$this->cObj->enableFields($table).' AND keyword = '.$keyword,'','uid DESC','1');
	            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	            
	            $str = $this->enableWikiCaching ? $row["html_cache"] : $row["body"];
	            
	
	            //remove noinclude directive
	            /*$dummy = preg_replace("'<noinclude[^>]*?>.*?</noinclude>'xsi", "", $row["body"]);*/
	            $str = preg_replace("'<noinclude[^>]*?>.*?</noinclude>'xsi", "", $str);

	            if ($argc > 1) {
	            	for($i=0; $i<$argc; $i++){
	            		$j = $i + 1;
	            		$str = preg_replace('/\{\{\{'.$j.'\}\}\}/',$args[$i],$str);
	            	}
	            } else {
	            	//remove left overs of template arguments
    				$str = preg_replace('/\{\{\{(.*?)\}\}\}/U', '', $str);
	            }
	            

	            // get caching to work, even if the template is not rendered yet, because
	            // the cache has been emptied before.
	            // this is triggered when the str is empty and the query was done on the 
	            // cache table --> Ergo nothing was cached, so we need to build a cached
	            // version of the template being requested.
	            //if ($str =="" AND $table == 'tx_drwiki_cache') {
	            //    $table = 'tx_drwiki_pages';
	            //    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$table,$table.'.pid IN ('.$pidList.')'.$this->cObj->enableFields($table).' AND keyword = '.$keyword,'','uid DESC','1');
	            //    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	            //    $str = $this->parse($row["body"]);
	            //}
	    } 
		
		if ($linestart && $templateExits) {
	    	//get rid of remaining template arguments
	    	$str = preg_replace('/\{\{\{(.*?)\}\}\}/U', '', $str);
	    } 
	    
	    if (!$linestart && !$templateExits) {
	    	//get rid of remaining template arguments
	    	$str = preg_replace('/\{\{\{(.*?)\}\}\}/U', '', $str);
	    	//create a link to make a template
	    	$str = '[['.$template.'|'.$template." ".$this->pi_getLL("pi_wiki_createtmpl", "did not exist - click here to create it").' ]]';
	    } 

	    if ($linestart && $templateExits) {
	    	$str = preg_replace('/\{\{\{(.*?)\}\}\}/U', '', $str);
	    }
		
    	$str = $this->enableWikiCaching ? $str : $this->parse($str,2);
		            
        return $str;
    }
    
    function getWikiPageSource() {
        
        $wikiPageSource = $this->sanitizer->sanitize($this->getFieldContent("body"));
        $textLines = explode("\n", $wikiPageSource );
        
        $result = '<nowiki><br /><pre>';
        $i = 1;
        foreach($textLines as $line){
            $result .= ' '.$i.' :'.$line.'';
            $i++;
        }
        $result .= '</pre></nowiki>';
        return $result;
    }
    
	/*
	 * 
	 */
	function createReference($str) {
            $compiledReferences = '';
            //unique ID to replace the ref markup
            $unique = '23133214jlsdflj235l23j4l-note-';
            //search for ref tags in $str
            $numMatches = preg_match_all( '/<ref>(.*?)<\/ref>/xis', $str, $matches );
            //start replacing ref tags
            for($i=0; $i<$numMatches; $i++){
                $j = $i +1;
                $str = str_replace($matches[0][$i], $unique . $i, $str);
                $dummy = '<sup id="_ref-'.$j.'" class="reference"><a href="#_note-'.$j.'" title="" rel="nofollow">['.$j.']</a></sup>';
                $compiledReferences .=  '<li id="_note-'.$j.'"><a href="#_ref-'.$j.'" title="" rel="nofollow">^</a> '.$matches[1][$i].'</li>';
                //test:
                $str = str_replace($unique . $i, $dummy, $str);                
            }
            
            //exchange <references/> tags for single/multi column display
            $str = str_replace('<references-2col/>', '<ol class="references-2column">'.$compiledReferences.'</ol>', $str);
            $str = str_replace('<references/>', '<ol class="references">'.$compiledReferences.'</ol>', $str);
            
           return $str; 		
	}
	
	
    function replaceVariables($str){
    
        // replace variables
        $str = preg_replace('/\{\{PAGENAME\}\}/', $this->piVars["keyword"], $str );
        $str = preg_replace('/\{\{GETDISCUSSIONLINK\}\}/', $this->getDiscussionLink($this->piVars["keyword"]), $str);
        $str = preg_replace('/\{\{REVISIONID\}\}/', $this->getUid($this->piVars["keyword"]), $str );
        if ($this->currentNameSpace) {$str = preg_replace('/\{\{NAMESPACE\}\}/', $this->currentNameSpace, $str );}
            else {$str = preg_replace('/\{\{NAMESPACE\}\}/', "Wiki", $str );}
        $str = preg_replace('/\{\{DATE\}\}/', date('Y-m-d H:i:s'), $str );
        $str = preg_replace('/\{\{SWATCHBEATS\}\}/', date('B'), $str );
        $str = preg_replace('/\{\{CURRENTMONTH\}\}/', date('m'), $str );
        $str = preg_replace('/\{\{CURRENTMONTHNAME\}\}/', date('F'), $str );
        $str = preg_replace('/\{\{CURRENTMONTHNAMEGEN\}\}/', date('M'), $str );
        $str = preg_replace('/\{\{CURRENTDAY\}\}/', date('j'), $str );
        $str = preg_replace('/\{\{CURRENTDAYNAME\}\}/', date('D'), $str );
        $str = preg_replace('/\{\{CURRENTYEAR\}\}/', date('Y'), $str );
        $str = preg_replace('/\{\{CURRENTTIME\}\}/', date('H:i'), $str );
        $str = preg_replace('/\{\{PAGEAUTHOR\}\}/', $this->showList(""  ,2,array("author")) , $str );
        $str = preg_replace('/\{\{VERSION\}\}/', $this->drWikiVersion , $str );
        $str = preg_replace('/\{\{NUMBEROFARTICLES\}\}/', count($this->getWikiInfos("keyword")) , $str );
        // Gets the current user
        $str = preg_replace('/\{\{CURRENTUSER\}\}/',$this->getUser(1), $str );
        
        //Sys_Images that come with dr_wiki
        //not the most elegant way, but it works quite well
        //TODO: Replace in own function trying to modulize the TS and the params
        $str = preg_replace('/\{\{IMAGE_ERROR\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Error"], $this->conf["sys_Error."]), $str );
        $str = preg_replace('/\{\{IMAGE_ERROR2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Error2"], $this->conf["sys_Error2."]), $str );
        $str = preg_replace('/\{\{IMAGE_WARNING\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Warning"], $this->conf["sys_Warning."]), $str );
        $str = preg_replace('/\{\{IMAGE_WARNING2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Warning2"], $this->conf["sys_Warning2."]), $str );
        $str = preg_replace('/\{\{IMAGE_INFO\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Info"], $this->conf["sys_Info."]), $str );
        $str = preg_replace('/\{\{IMAGE_INFO2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Info2"], $this->conf["sys_Info2."]), $str );
        $str = preg_replace('/\{\{IMAGE_COPY\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Copy"], $this->conf["sys_Copy."]), $str );
        $str = preg_replace('/\{\{IMAGE_COPY2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Copy2"], $this->conf["sys_Copy2."]), $str );
        $str = preg_replace('/\{\{IMAGE_TRASH\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Trash"], $this->conf["sys_Trash."]), $str );
        $str = preg_replace('/\{\{IMAGE_TRASH2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Trash2"], $this->conf["sys_Trash2."]), $str );
        $str = preg_replace('/\{\{IMAGE_RETURN\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Return"], $this->conf["sys_Return."]), $str );
        $str = preg_replace('/\{\{IMAGE_RETURN2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Return2"], $this->conf["sys_Return2."]), $str );
        $str = preg_replace('/\{\{IMAGE_FOLDER\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Folder"], $this->conf["sys_Folder."]), $str );
        $str = preg_replace('/\{\{IMAGE_FOLDER2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Folder2"], $this->conf["sys_Folder2."]), $str );
        $str = preg_replace('/\{\{IMAGE_COMPUTER\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Computer"], $this->conf["sys_Computer."]), $str );
        $str = preg_replace('/\{\{IMAGE_COMPUTER2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Computer2"], $this->conf["sys_Computer2."]), $str );
        $str = preg_replace('/\{\{IMAGE_WEBPAGE\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Webpage"], $this->conf["sys_Webpage."]), $str );
        $str = preg_replace('/\{\{IMAGE_WEBPAGE2\}\}/',$this->cObj->cObjGetSingle($this->conf["sys_Webpage2"], $this->conf["sys_Webpage2."]), $str);
        
        // Create Links for editing the current Page
        $str = preg_replace('/\{\{EDITPAGELINK\}\}/',$this->pi_linkTP_keepPIvars("Edit", array("cmd" => "edit", "submit" => "", "showUid" =>""), 1, 0), $str );
        $str = preg_replace('/\{\{EDITPAGEICON\}\}/',$this->pi_linkTP_keepPIvars($this->cObj->cObjGetSingle($this->conf["iconEdit"], $this->conf["iconEdit."]), array("showUid" =>"", "cmd" => "edit", "submit" => ""), 1, 0), $str );
        
        $str = preg_replace('/\{\{ALLCONTRIBUTINGAUTHORS\}\}/', $this->getWikiInfos("author") , $str );
        $str = preg_replace('/\{\{CONTRIBUTINGAUTHORS\}\}/', $this->getWikiInfos("author",TRUE,$this->piVars["keyword"]) , $str );
        
        return($str);
    }


	
/** Split template arguments
 * 
 */
function getTemplateArgs( $argsString ) {
	if ( $argsString === '' ) {
		return array();
	}

	$args = explode( '|', substr( $argsString, 1 ) );

	# If any of the arguments contains a '[[' but no ']]', it needs to be
	# merged with the next arg because the '|' character between belongs
	# to the link syntax and not the template parameter syntax.
	$argc = count($args);
	
	for ( $i = 0; $i < $argc-1; $i++ ) {
		if ( substr_count ( $args[$i], '[[' ) != substr_count ( $args[$i], ']]' ) ) {
			$args[$i] .= '|'.$args[$i+1];
			array_splice($args, $i+1, 1);
			$i--;
			$argc--;
		}
	}

	return $args;
}

/**
 * parse
 *
 * parses the given string and replaces wiki- and security-related stuff
 *
 * @param	[string]		$str: the content to search in
 * @return	[string]		HTML rendered wiki-page
 * @mode        [int]                   Disenables the creation of fancy headers (html)
 */
function parse($str, $mode=0)
{
    //add header and footer if this is not a template
    if ($this->currentNameSpace != $this->nameSpaces["Template"] AND $mode!=2) {
    $str = $this->wikiHeader . chr(10) . $str . chr(10) . $this->wikiFooter;
    }
    //show wiki page source
    $str = preg_replace('/\{\{SHOWWIKIPAGESOURCE\}\}/',$this->getWikiPageSource(), $str );
    //process nowiki directive
    $str = $this->processNoWikiTags($str);
    // strip security-related stuff
    $str = $this->sanitizer->sanitize($str);
    // insert bars
    $str = preg_replace('/(^|\n)-----*/', '\\1<hr />', $str );
    // remove quotes
    $str = $this->doAllQuotes($str);
    // render tables:
    $str = $this->doTableStuff($str);
    // replace headings and format them
    $str = $this->doHeadings($str);
    // replace ul- und pre-blocks
    $str = $this->doBlockLevels($str);
    $str = preg_replace('|<monobr>|', '' . "\r\n", $str);


    // Store cachable version of the Wiki-Page for saving to DB
    $this->cacheContents = $str;
    // finalise the parsed page
    $str = $this->finalise_parse($str, $mode);
    
    return $str;
}

function createCategoryFooter () {
	// re-insert category links into the page
	$categoryFooter = '';
	if ($this->categoryIndex) {
	    $categoryFooter = '<div class="wiki-box-catlinks"> Related Categories: [ ';
	    // sort Array case-insensitive
	    $keywordArr = $this->categoryIndex;
	    $array_lowercase = array_map('strtolower', $keywordArr);
	    array_multisort($array_lowercase, SORT_ASC, $this->categoryIndex);
	    foreach ( $this->categoryIndex as $catEntry ) {
	    	$categoryFooter .= $catEntry['catgoryLink'] . ' | ';
	    }
	    $categoryFooter = substr($categoryFooter, 0 , strlen($categoryFooter)-2);
	    $categoryFooter .=' ]</div>';
	}
    
    return $categoryFooter;
}

/**
 * finalise_parse
 *
 * finalises the parsing process and adds the dynamic elements, such as plug-ins, headings, etc.
 *
 * @param	[string]		$str: the content to search in
 * @return	[string]		HTML rendered wiki-page
 * @mode    [int]                   Disenables the creation of fancy headers (html)
 */
function finalise_parse($str, $mode=0)
{
    // repslace ext URLs
    $str = $this->replaceExternalLinks($str);
    
    // do the numbering...
    if (preg_match('/__NOTOC__/',$str)) {$this->doShowToc = false;}
    //remove directive
    $str = preg_replace('/__NOTOC__((\s*?)<br \/>|.*?)/','',$str); 

    if (preg_match('/__NONUMBERHEADINGS__/',$str)) {$this->doNumberHeadings = false;}
    //remove directive
    $str = preg_replace('/__NONUMBERHEADINGS__((\s*?)<br \/>|.*?)/','',$str);

    if ($mode==0) {
        $str = $this->formatHeadings($str);
    }
    
    //replace template markups and process parameter (template:name|var_1|..|var_n)
    $regex = '/(\\n|{)?{{(?!'.$this->noTemplates.')(['.$this->legalChars.']*)(\\|.*?|)}}/s';
    $str = preg_replace_callback( $regex, array( &$this, 'processTemplate' ), $str );
    
    // replace plugin-blocks
    $str = $this->substitutePlugins($str);
    
    // replace variables
    $str = $this->replaceVariables($str);
    
    $str = $this->createReference($str);

    // Replace int Wiki-Pages
    if ($mode==0) {
    	$numMatches = preg_match_all( '/\[\[(.*?)]\]((.*?)\s|\s)/', $str, $matches );
        	for($i=0; $i<$numMatches; $i++){
        		$str = str_replace($matches[0][$i], $this->linkKeyword($matches[1][$i],$matches[3][$i]), $str);
        	}        	
    }
    
    // Save-HTML Mode. The internal Markups are removed.
    if ($mode==1) {
    // TODO: Add link wrap for non-wiki output (FlexForms)
        $str = str_replace('[[', '',$str);
        $str = str_replace(']]', '',$str);
    }
    
    // Add redirect 
    return $this->redirectLink . $str;
}

	//Hook: to handle content before insert. To manipulate the content or deny the insert (spam...)
    function hook_submit_beforeInsert($pageContent) {
        if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_wiki']['drwiki_SubmitBeforeInsert'])) { 
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_wiki']['drwiki_SubmitBeforeInsert'] as $_classRef) {
                $_procObj = & t3lib_div::getUserObj($_classRef);
                return $_procObj->drwiki_SubmitBeforeInsert($pageContent); 
            }
        } else { // if hook is not set
            return TRUE; // Return True is default 
        }
 	}
    //Hook: to do something after insert. Send emails, write something in database, clean older versions....
    function hook_submit_afterInsert($pageContent) {
        if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_wiki']['drwiki_SubmitAfterInsert'])) { 
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dr_wiki']['drwiki_SubmitAfterInsert'] as $_classRef) {
                $_procObj = & t3lib_div::getUserObj($_classRef);
                return $_procObj->drwiki_SubmitAfterInsert($pageContent); 
            }
        } else { // if hook is not set
            return TRUE; // Return True is default 
        }
 	} 

	/**
	 * mailAdmin
	 *
	 * Christian Bieber , christian.bieberl@dkd.de
	 * 9.1.2009
	 * 
	 * send Admin a mail that a new entry has been inserted
	 *
	 * @param	[int]	id of new entry
	 * @param	[string]	name of the page 
	 * @param	[string]	body of the message (wiki markup)
	 */
	function mailAdmin($lastID, $pageName, $text = "") {
		
		$text = $this->parse($text, 1);
		$mail = t3lib_div::makeInstance('t3lib_htmlmail');
		$mail->recipient = $mail->recipient = $this->mailRecipient;
		$mail->subject = $mail->subject = $this->mailSubject; 
		
		$mail->from_email = $this->mailFromEmail;
		$mail->from_name = $this->mailFromName;
		$mail->charset = 'utf-8';
		$mail->mailer = '';
		$mail->start();
		$mail->useQuotedPrintable();
		
		$user = '<a href="mailto:'.$GLOBALS["TSFE"]->fe_user->user["email"].'">'.$GLOBALS["TSFE"]->fe_user->user["username"].'</a>';
		$activationLink = $this->pi_linkTP_keepPIvars("Activate page: ".$pageName, array("keyword" => $pageName, "cmd" => "activateHidden", "showUid" => $lastID), 1, 0);
		
		$mail->message = '<html><head><title>Wiki Revision</title></head><body>There is a new wiki page version ( ID ) = '.$lastID.' (From: '.$user.') <br />'
						 .$activationLink.'? Note: You need to be logged-in as admin user!<br />'
						 .'Contained text: <br /><br /><hr />'.$text.'</body></html>';
		$mail->theParts['html']['content'] = $mail->message;
		$mail->send($mail->recipient);	

	}


/**
 * inGroup
 *
 * checks if a given userid (or the current user is none given) is in a given group (mostly a flexform field of the type "group") 
 *
 * @param	[string]		$groups: a comma separated list of group_id; mostly the flexform field of type "group" 
 * @param	[string]		$user: the userid, if ommitted the currently logged in user is taken
 * @return	[string]		true or false
 */
function inGroup ($groups, $user = -1)
{
    if ($user == -1) {
    $user = $GLOBALS['TSFE']->fe_user->user[uid];
   } 
   if (is_string ($groups)) {
     $groups =  split(',', $groups);
   } 
   foreach ($GLOBALS['TSFE']->fe_user->groupData['uid'] as $group)
   {
    if (in_array ($group, $groups)) {
     return true;
    }
   }
   return false;
  } 
}
// end of DR_WIKI

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dr_wiki/pi1/class.tx_drwiki_pi1.php"])
	{include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dr_wiki/pi1/class.tx_drwiki_pi1.php"]);}
?>
