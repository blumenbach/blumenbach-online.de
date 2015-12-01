<?php

########################################################################
# Extension Manager/Repository config file for ext "dr_wiki".
#
# Auto generated 15-03-2010 12:31
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DR Wiki - Typo3 Wiki extension',
	'description' => 'Inserts an advanced Wiki into your page (MediaWiki compliant syntax). For details, please visit http://drwiki.myasterisk.de or http://forge.typo3.org/projects/show/extension-dr_wiki.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '1.8.2',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Denis Royer',
	'author_email' => 'info@indigi.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.0.0-0.0.0',
			'typo3' => '3.8.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:101:{s:13:"changelog.txt";s:4:"e450";s:12:"ext_icon.gif";s:4:"4625";s:17:"ext_localconf.php";s:4:"e7ab";s:14:"ext_tables.php";s:4:"b523";s:14:"ext_tables.sql";s:4:"d310";s:28:"ext_typoscript_constants.txt";s:4:"48fa";s:28:"ext_typoscript_editorcfg.txt";s:4:"91c9";s:24:"ext_typoscript_setup.txt";s:4:"581d";s:24:"icon_tx_drwiki_cache.gif";s:4:"475a";s:24:"icon_tx_drwiki_pages.gif";s:4:"90f7";s:13:"locallang.php";s:4:"a8d5";s:13:"locallang.xml";s:4:"2a47";s:16:"locallang_db.php";s:4:"7136";s:16:"locallang_db.xml";s:4:"9600";s:17:"module_drwiki.gif";s:4:"809c";s:7:"tca.php";s:4:"bd9b";s:8:"todo.txt";s:4:"4a04";s:14:"doc/manual.sxw";s:4:"8d89";s:29:"flexforms/flexform_ds_pi1.xml";s:4:"21c1";s:34:"flexforms/flexforms_ds_ratings.xml";s:4:"259c";s:26:"flexforms/locallang_db.php";s:4:"c7ce";s:26:"flexforms/locallang_db.xml";s:4:"2108";s:34:"flexforms/locallang_db_ratings.xml";s:4:"8741";s:29:"lib/class.eid_ajax.search.php";s:4:"2164";s:28:"lib/class.html_sanitizer.php";s:4:"0917";s:34:"lib/class.wiki.createIndexList.php";s:4:"f20b";s:25:"lib/class.wiki_parser.php";s:4:"0ee1";s:13:"mod1/conf.php";s:4:"a041";s:14:"mod1/index.php";s:4:"3f4e";s:18:"mod1/locallang.php";s:4:"ead8";s:18:"mod1/locallang.xml";s:4:"c89b";s:22:"mod1/locallang_mod.php";s:4:"b432";s:19:"mod1/moduleicon.gif";s:4:"3dfc";s:27:"pi1/class.tx_drwiki_pi1.php";s:4:"8059";s:35:"pi1/class.tx_drwiki_pi1_wizicon.php";s:4:"e929";s:17:"pi1/locallang.php";s:4:"3800";s:17:"pi1/locallang.xml";s:4:"58d6";s:22:"pi1/plugins/author.php";s:4:"c8ca";s:24:"pi1/plugins/backlink.php";s:4:"4ea5";s:29:"pi1/plugins/categoryindex.php";s:4:"e08c";s:22:"pi1/plugins/create.php";s:4:"529a";s:25:"pi1/plugins/indexlist.php";s:4:"49ba";s:29:"pi1/plugins/keyword_index.php";s:4:"8e7b";s:28:"pi1/plugins/last_changed.php";s:4:"a23e";s:35:"pi1/plugins/locallang_mostrated.xml";s:4:"0606";s:26:"pi1/plugins/most_rated.php";s:4:"ede2";s:25:"pi1/plugins/plugincfg.php";s:4:"817a";s:31:"pi1/plugins/private-picture.php";s:4:"0d56";s:23:"pi1/plugins/ratings.php";s:4:"f492";s:22:"pi1/plugins/search.php";s:4:"5fcc";s:29:"pi1/plugins/template_list.php";s:4:"2040";s:14:"res/ce_wiz.gif";s:4:"4b4d";s:13:"res/clear.gif";s:4:"cc11";s:15:"res/drwiki.tmpl";s:4:"a163";s:28:"res/icon_tx_drwiki_pages.gif";s:4:"8583";s:26:"res/most_rated_plugin.html";s:4:"f8b5";s:16:"res/redirect.png";s:4:"91cd";s:18:"res/wiki_script.js";s:4:"ea10";s:18:"res/wiki_toggle.js";s:4:"ee65";s:27:"res/buttons/button_bold.png";s:4:"92c5";s:30:"res/buttons/button_extlink.png";s:4:"3189";s:31:"res/buttons/button_headline.png";s:4:"74e9";s:25:"res/buttons/button_hr.png";s:4:"fa83";s:29:"res/buttons/button_italic.png";s:4:"fd9e";s:27:"res/buttons/button_link.png";s:4:"242e";s:29:"res/buttons/button_nowiki.png";s:4:"fe42";s:26:"res/buttons/button_ref.png";s:4:"9219";s:26:"res/buttons/button_sig.png";s:4:"0363";s:29:"res/buttons/button_strike.png";s:4:"aeeb";s:26:"res/buttons/button_sub.png";s:4:"abbf";s:26:"res/buttons/button_sup.png";s:4:"c963";s:31:"res/buttons/button_template.png";s:4:"a554";s:19:"res/mod1/delete.png";s:4:"8178";s:35:"res/mod1/delete_all_but_current.png";s:4:"4185";s:25:"res/mod1/delete_older.png";s:4:"83b0";s:17:"res/mod1/edit.png";s:4:"4737";s:17:"res/mod1/lock.png";s:4:"3660";s:19:"res/mod1/unlock.png";s:4:"257f";s:17:"res/mod1/view.png";s:4:"a81f";s:25:"res/sys/sys_clipboard.png";s:4:"0b02";s:24:"res/sys/sys_computer.png";s:4:"f2cf";s:20:"res/sys/sys_copy.png";s:4:"07ed";s:21:"res/sys/sys_error.png";s:4:"2be9";s:22:"res/sys/sys_folder.png";s:4:"b06c";s:20:"res/sys/sys_info.png";s:4:"3f36";s:22:"res/sys/sys_return.png";s:4:"c2ae";s:22:"res/sys/sys_search.png";s:4:"5e6e";s:21:"res/sys/sys_trash.png";s:4:"a63f";s:23:"res/sys/sys_warning.png";s:4:"0d14";s:23:"res/sys/sys_webpage.png";s:4:"8868";s:19:"res/ui/link_ext.gif";s:4:"03a6";s:22:"res/ui/link_mailto.gif";s:4:"c381";s:20:"res/ui/wiki_back.png";s:4:"6aba";s:20:"res/ui/wiki_diff.png";s:4:"bdc0";s:20:"res/ui/wiki_edit.png";s:4:"9717";s:23:"res/ui/wiki_getHTML.png";s:4:"c65f";s:20:"res/ui/wiki_help.png";s:4:"c381";s:20:"res/ui/wiki_home.png";s:4:"84b1";s:20:"res/ui/wiki_lock.png";s:4:"b508";s:22:"res/ui/wiki_unlock.png";s:4:"257f";s:25:"res/ui/wiki_versionen.png";s:4:"bdc0";}',
);

?>