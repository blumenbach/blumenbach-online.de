<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$TCA['tx_bolonline_Kerndaten'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:bolonline/locallang_db.xml:tx_bolonline_Kerndaten',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_bolonline_Kerndaten.gif',

);


if (TYPO3_MODE == 'BE')	{
	/*
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'tx_bolonline_cm1',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_bolonline_cm1.php'
	);*/
	
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
  'name' => 'tx_impexp_clickmenu',
  'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_impexp_clickmenu.php'
);
}


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:bolonline/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:bolonline/locallang_db.xml:tt_content.list_type_pi2',
	$_EXTKEY . '_pi2',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_bolonline_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_bolonline_pi1_wizicon.php';
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_bolonline_pi2_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_bolonline_pi2_wizicon.php';
}

$tempColumns = array (
	
);


t3lib_div::loadTCA('tx_bolonline_Kerndaten');
t3lib_extMgm::addTCAcolumns('tx_bolonline_Kerndaten',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tx_bolonline_Kerndaten','');

$tempColumns = array (
	
);


t3lib_div::loadTCA('tx_bolonline_Kerndaten');
t3lib_extMgm::addTCAcolumns('tx_bolonline_Kerndaten',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tx_bolonline_Kerndaten','');
 
 t3lib_div::loadTCA('tt_content');
 // This changes the upload limit for image elements
 $TCA['tt_content']['columns']['image']['config']['max_size'] = 100000;
 
 // This changes the upload limit for media elements
 $TCA['tt_content']['columns']['media']['config']['max_size'] = 100000;
 
 // This changes the upload limit for multimedia elements
 $TCA['tt_content']['columns']['multimedia']['config']['max_size'] = 100000;


?>
