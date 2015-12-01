<?php

########################################################################
# Extension Manager/Repository config file for ext "pi_iframe".
#
# Auto generated 12-04-2010 16:02
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'IFRAME',
	'description' => 'Inserts an IFRAME on the page and shows content from another URL inside of it.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Kasper Skårhøj',
	'author_email' => 'kasper@typo3.com',
	'author_company' => 'Curby Soft Multimedia',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.0.3',
	'constraints' => array(
		'depends' => array(
			'typo3' => '3.5.0-0.0.0',
			'php' => '3.0.0-0.0.0',
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"8597";s:14:"ext_tables.php";s:4:"8f9e";s:14:"ext_tables.sql";s:4:"49c5";s:28:"ext_typoscript_editorcfg.txt";s:4:"46be";s:24:"ext_typoscript_setup.txt";s:4:"91a8";s:16:"locallang_db.php";s:4:"f334";s:19:"doc/wizard_form.dat";s:4:"8c08";s:20:"doc/wizard_form.html";s:4:"0bbd";s:29:"pi1/class.tx_piiframe_pi1.php";s:4:"d16c";s:17:"pi1/locallang.php";s:4:"aaa1";}',
);

?>