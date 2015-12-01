<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Denis Royer (webadmin@myasterisk.de)
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
 * Plugin 'DR Wiki' for the 'dr_wiki' extension.
 *
 * @author	Denis Royer <webadmin@myasterisk.de>
 *
 * I've written this plugin for the EU project FIDIS (www.fidis.net)
 *
 * The idea and parts of the code for this plugin came from:
 *  "A1 WIKI" by Mirko Balluff <balluff@miba-edv.de>
 *  "blastwiki" at http://www.roboticboy.com/blast/
 *  "wikimedia" at http://www.wikimedia.org
 *
 */

class tx_drwiki_pi1_plugin {

     // The array for Plugin Objects
	
	var $pluginArray = array();
	var $pluginString = '';
	
    function tx_drwiki_pi1_plugin (){
        return TRUE;
    }
	
    function getPluginString (){
        foreach($this->pluginArray as $pluginName => $pluginObject)
        {
        	$dummy .= $pluginName . '|';
        }
        //delete last "|" of the string
        $this->pluginString = substr($dummy, 0 , strlen($dummy)-1); 
        
        return $this->pluginString;
    }
    
    function getPlugIns() {
        $pluginArray = array();

        // Include the Search-Plugin
        // Description:	Inserts a Search-Form on the WikiPage and displays the result
        // Syntax: 	{###SEARCH###}{###SEARCH###}
        // Parameters: 	None
        // Example: 	{###SEARCH###}{###SEARCH###}

        include_once(dirname(__FILE__) ."/search.php");
        $this->pluginArray["SEARCH"] = new tx_drwiki_pi1_search;

        // Include the LastChanges-Plugin
        // Description:	Inserts a list of the pages changed in the last [days] days.
        // Syntax: 	{###LASTCHANGED###}[days]{###LASTCHANGED###}
        // Parameters: 	days (optional, default 14): Number of days to look for
        // Example:  	{###LASTCHANGED###}7{###LASTCHANGED###}
        //		        Displays the pages changed in the last 7 days

        include_once(dirname(__FILE__) ."/last_changed.php");
        $this->pluginArray["LASTCHANGED"] = new tx_drwiki_pi1_last_changed;

        include_once(dirname(__FILE__) ."/indexlist.php");
        $this->pluginArray["INDEXLIST"] = new tx_drwiki_pi1_indexlist;

        include_once(dirname(__FILE__) ."/author.php");
        $this->pluginArray["AUTHOR"] = new tx_drwiki_pi1_author;

        include_once(dirname(__FILE__) ."/template_list.php");
        $this->pluginArray["TEMPLATELIST"] = new tx_drwiki_pi1_templatelist;

        include_once(dirname(__FILE__) ."/ratings.php");
        $this->pluginArray["RATINGS"] = new tx_drwiki_pi1_ratings;

        include_once(dirname(__FILE__) ."/most_rated.php");
        $this->pluginArray["MOSTRATED"] = new tx_drwiki_pi1_mostrated;

        // Include the BackLinks-Plugin
        // Description:	Inserts a list of the pages that link to this page (or defined page).
        // Syntax: 	{###BACKLINKS###}[key]{###BACKLINKS###}
        // Parameters: key of the page you want to list backlinks for (optional, default current page)
        // Example: {###BACKLINKS###}Citation{###BACKLINKS###}
        // Displays the pages that link to the Citation wiki page.
        include_once(dirname(__FILE__) ."/backlink.php");
        $this->pluginArray["BACKLINKS"] = new tx_drwiki_pi1_backlink;

        include_once(dirname(__FILE__) ."/categoryindex.php");
        $this->pluginArray["CATEGORYINDEX"] = new tx_drwiki_pi1_categoryindex;

        include_once(dirname(__FILE__) ."/create.php");
        $this->pluginArray["CREATE"] = new tx_drwiki_pi1_create;

        include_once(dirname(__FILE__) ."/keyword_index.php");
        $this->pluginArray["KEYWORDINDEX"] = new tx_drwiki_pi1_keyword_index;

         // Bilder innerhalb des Wikis einfÃ¼gen
        //include_once(dirname(__FILE__) ."/picture.php");
        //$this->pluginArray["IMAGE"] = new tx_drwiki_pi1_picture;

         // Liest alle Plugin-Dateien automatisch ein, welche mit "private" beginnen.
         // Damit kšnnen individuelle Plugins erstellt werden.
        if ($handle = opendir(dirname(__FILE__))) {
          while (false !== ($file = readdir($handle))) {
            if (substr($file, 0, 7) =="private") {
               include_once(dirname(__FILE__))."/".$file;
            }
          }
          closedir($handle);
        }

        return $this->pluginArray;
    }
}
?>