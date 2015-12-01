/*
 * CodePress regular expressions for HTML syntax highlighting
 */

/**
 * The order of the expressions DOES MATTER!!!!
 */

Language.syntax = [
		// special characters
 	{ input : /(\s+)(=|&lt;|&gt;|=&lt;)(\s+)/g, output : '$1<a>$2</a>$3' },		// = < > =<
 	{ input : /(\.|\||&amp;|:)/g, output : '<a>$1</a>' },						// . | & :

		// constants
	{ input : /\{\$(.*?)\}/g, output : '<span>{$$$1}</span>' },

		// reserved words
	{ input : /\b(PAGE|FRAMESET|FRAME|META|COA|COA_INT|COBJ_ARRAY|CARRAY|CONTENT|TEXT|HTML|FILE|IMAGE|IMG_RESOURCE|CLEARGIF|RECORDS|CTABLE|OTABLE|COLUMNS|HRULER|IMGTEXT|CASE|LOAD_REGISTER|RESTORE_REGISTER|FORM|SEARCHRESULT|USER|USER_INT|PHP_SCRIPT|PHP_SCRIPT_INT|PHP_SCRIPT_EXT|TEMPLATE|MULTIMEDIA|EDITPANEL|GIFBUILDER|HMENU|GMENU|GMENU_LAYERS|GMENU_FOLDOUT|TMENU|TMENU_LAYERS|IMGMENU|JSMENU|_LOCAL_LANG|_CSS_DEFAULT_STYLE|_DEFAULT_PI_VARS)\b/g, output : '<b>$1</b>' }, 			// TLO

		// conditions
	{ input : /\[(.*?)\]/g, output : '<big>[$1]</big>' },

		// number (indexes)
	{ input : /(>|\s)(\d+)/g, output : '$1<cite>$2</cite>' },

		// HTML tags
	{ input : /&lt;([^\s^&^<]{1}[^&]*)&gt;/g, output : '<u>&lt;$1&gt;</u>' },

		// bold some special things
	{ input : /(tx_|ux_)(.*?)(\s|<br|<\/P)/g, output : '<strong>$1$2</strong>$3' },

		// comments
	{ input : /#(.*?)(<br|<\/P)/g, output: '<i>#$1</i>$2' },			// #
	{ input : /\/\/(.*?)(<br|<\/P)/g, output: '<i>//$1</i>$2' },		// //
	{ input : /\/\*(.*?)\*\//g, output : '<i>/*$1*/</i>' } 			// /* */
	
	
]

Language.snippets = [
	{ input : '/d',		output : '/**\n * $0Place your description here\n */' },
	{ input : '/*', 	output : ' $0 */' },
	{ input : '/co',	output: '[globalVar = GP:$0 = 1]\n\n[global]' }
]

Language.complete = [
	{ input : '{', output : '{\n\t$0\n}' },
	{ input : '(', output : '(\n\t$0\n)' },
	{ input : '[', output : '[$0]' }
]

Language.shortcuts = [
	{ input : '[ctrl][p]', output : 'page = PAGE\nconfig.admPanel = 1' }
]
