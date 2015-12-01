<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_piiframe_iframe_url" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:pi_iframe/locallang_db.php:tt_content.tx_piiframe_iframe_url:ESQ",		
		"config" => Array (
			"type" => "input",	
			"size" => "48",	
			"eval" => "required",
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,pages";
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_piiframe_iframe_url;;;;1-1-1";


t3lib_extMgm::addPlugin(Array("LLL:EXT:pi_iframe/locallang_db.php:tt_content.list_type", $_EXTKEY."_pi1"),"list_type");
?>