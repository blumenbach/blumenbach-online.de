<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::addModule("web","txdrwikiM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

t3lib_extMgm::allowTableOnStandardPages("tx_drwiki_pages");
t3lib_extMgm::addToInsertRecords("tx_drwiki_pages");

$ICON_TYPES["drwiki"] = Array("icon" => t3lib_extMgm::extRelPath($_EXTKEY)."module_drwiki.gif");
$TCA["pages"]["columns"]["module"]["config"]["items"][] = Array("DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki|DR Wiki", "drwiki");

$TCA["tx_drwiki_pages"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_pages",
		"label" => "keyword",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY uid",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
			//"starttime" => "starttime",
			//"endtime" => "endtime",
			//"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_drwiki_pages.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, keyword, body, date, author, summary, locked", 
                //starttime, endtime, fe_group,
	)
);

$TCA["tx_drwiki_cache"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_cache",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_drwiki_cache.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, keyword, html_cache, cache_uid",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,pages";
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPlugin(Array("LLL:EXT:dr_wiki/flexforms/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","DR Wiki");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:dr_wiki/flexforms/flexform_ds_pi1.xml');
 
if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_drwiki_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_drwiki_pi1_wizicon.php";
?>