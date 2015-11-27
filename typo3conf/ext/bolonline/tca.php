<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_bolonline_Kerndaten'] = array (
	'ctrl' => $TCA['tx_bolonline_Kerndaten']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,a2afe6efea'
	),
	'feInterface' => $TCA['tx_bolonline_Kerndaten']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'exclude' => 0,		
		'label' => 'LLL:EXT:bolonline/locallang_db.xml:tx_bolonline_Kerndaten.a2afe6efea',		
		'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, a2afe6efea')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>