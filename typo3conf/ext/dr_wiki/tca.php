<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_drwiki_pages"] = Array (
	"ctrl" => $TCA["tx_drwiki_pages"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,keyword,body,date,author,summary,locked" //starttime,endtime,fe_group,
	),
	"feInterface" => $TCA["tx_drwiki_pages"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"fe_group" => Array (
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"keyword" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_pages.keyword",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"max" => "255",
				"eval" => "required,trim",
			)
		),
		"body" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_pages.body",
			"config" => Array (
				"type" => "text",
				"cols" => "50",
				"rows" => "8",
			)
		),
		"date" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_pages.date",
			"config" => Array (
				"type" => "input",
				"size" => "19",
				"max" => "19",
				"eval" => "required,trim",
			)
		),
		"author" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_pages.author",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"locked" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_pages.locked",
			"config" => Array (
				"type" => "check",
			)
		),

                "summary" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_pages.summary",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"max" => "130",
				"eval" => "trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, keyword;;;;2-2-2, title;;;;3-3-3, description;;;;4-4-4, body;;;;5-5-5, date, author, locked")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "") //starttime, endtime, fe_group
	)
);

$TCA["tx_drwiki_cache"] = Array (
	"ctrl" => $TCA["tx_drwiki_cache"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,keyword,html_cache,cache_uid"
	),
	"feInterface" => $TCA["tx_drwiki_cache"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"keyword" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_cache.keyword",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"html_cache" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_cache.html_cache",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"cache_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dr_wiki/locallang_db.php:tx_drwiki_cache.cache_uid",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"default" => "0"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, keyword, html_cache, cache_uid")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>