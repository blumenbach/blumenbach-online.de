<?php

if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
t3lib_extMgm::addPageTSConfig('
	# default page TSconfig
');
t3lib_extMgm::addUserTSConfig('
	# default user TSconfig
');

## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,"editorcfg","
	tt_content.CSS_editor.ch.tx_drwiki_pi1 = < plugin.tx_drwiki_pi1.CSS_editor
",43);


t3lib_extMgm::addPItoST43($_EXTKEY,"pi1/class.tx_drwiki_pi1.php","_pi1","list_type",0);


t3lib_extMgm::addTypoScript($_EXTKEY,"setup","
	tt_content.shortcut.20.0.conf.tx_drwiki_pages = < plugin.".t3lib_extMgm::getCN($_EXTKEY)."_pi1
	tt_content.shortcut.20.0.conf.tx_drwiki_pages.CMD = singleView
",43);

// add handler for eID and ajax based search
$TYPO3_CONF_VARS['FE']['eID_include']['tx_drwiki_search'] = 'EXT:dr_wiki/lib/class.eid_ajax.search.php';

?>