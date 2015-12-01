<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_iframe2_iframe_url" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:iframe2/locallang_db.php:tt_content.tx_iframe2_iframe_url",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"max" => "255",	
			"eval" => "required,trim",
		)
	),
	"tx_iframe2_iframe_width" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:iframe2/locallang_db.php:tt_content.tx_iframe2_iframe_width",		
		"config" => Array (
			"type" => "input",	
			"size" => "10",	
			"max" => "10",	
			"eval" => "trim",
		)
	),
	"tx_iframe2_iframe_height" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:iframe2/locallang_db.php:tt_content.tx_iframe2_iframe_height",		
		"config" => Array (
			"type" => "input",	
			"size" => "10",	
			"max" => "10",
			"eval" => "trim",
		)
	),
	"tx_iframe2_iframe_scrolling" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:iframe2/locallang_db.php:tt_content.tx_iframe2_iframe_scrolling",		
		"config" => Array (
			"type" => "input",	
			"size" => "5",	
			"max" => "5",	
			"eval" => "trim",
		)
	),
	"tx_iframe2_iframe_border" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:iframe2/locallang_db.php:tt_content.tx_iframe2_iframe_border",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,pages";
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_iframe2_iframe_url;;;;1-1-1, tx_iframe2_iframe_width, tx_iframe2_iframe_height, tx_iframe2_iframe_scrolling, tx_iframe2_iframe_border";


t3lib_extMgm::addPlugin(Array("LLL:EXT:iframe2/locallang_db.php:tt_content.list_type", $_EXTKEY."_pi1"),"list_type");


//if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_iframe2_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_iframe2_pi1_wizicon.php";
?>
