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
***************************************************************
* This File contains the eid/ajax code for the inclusion of an 
* interactive search engine for the wiki
* 
* @author Denis Royer <info@indigi.de>
* 
* Code is inspired, based on the following websites:
* http://www.typo3-tutorials.org/tutorials/extensions/eid-mechanismus.html
* http://www.blogix.net/2009/02/08/typo3-eid-oder-daten-mit-ajax-anfordern/
* http://blog.phpsystem.de/2007/07/26/typo3-und-ajax-mit-eid/
*/

if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_drwiki_eidSearch extends tslib_pibase {
  function main(){
    $feUserObj = tslib_eidtools::initFeUser(); // Initialize FE user object		
    tslib_eidtools::connectDB(); //Connect to database
    
	
    $table = 'tx_drwiki_pages';
    $myPid = intval(t3lib_div::_GET('myPid'));
    $myKeyword = $GLOBALS['TYPO3_DB']->fullQuoteStr(trim(t3lib_div::_GET('myKeyword')),$table);
    $myKeyword = substr($myKeyword, 1 , strlen($myKeyword)-2);

    $searchString = $table.".pid IN (".$myPid.") AND keyword like '%" . $myKeyword . "%'";
    
    // get Database entries
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$table,$searchString);
    
    $results = array();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
    	$results[$row["uid"]] = $row["keyword"];
    }
	// Wir geben der Anfrage ein XML Objekt zurŸck
	$ajax_return_data = t3lib_div::array2xml($results);
	header('Expires: Mon, 26 Jul 2000 03:00:00 GMT');
	header('Last-Modified: ' . gmdate( "D, d M Y H:i:s" ) . 'GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	header('Content-Length: '.strlen($ajax_return_data));
	header('Content-Type: text/xml');
	echo $ajax_return_data;
	
  }
}


$output = t3lib_div::makeInstance('tx_drwiki_eidSearch');
$output->main();


?>