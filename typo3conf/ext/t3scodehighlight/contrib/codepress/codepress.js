/*
 * CodePress - Real Time Syntax Highlighting Editor written in JavaScript - http://codepress.org/
 * 
 * Copyright (C) 2006 Fernando M.A.d.S. <fermads@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the 
 * GNU Lesser General Public License as published by the Free Software Foundation.
 * 
 * Read the full licence: http://www.opensource.org/licenses/lgpl-license.php
 */

var cpTheme = 'default';
var cpModule = 'php';
 
CodePress = function(id) {
	var id,filename,language,img,cpBody,cpWindow,cpEditor,cpMenu,cpWindowHeight,cpEditorHeight,cpFilename,cpLanguage,cpMenuOptions,cpMenuLanguages,cpFullscreen;
	var saveCode = "";

	this.initialize = function(i) {
		cpWindow = document.createElement('div');
		cpWindow.className = 'cp-window fullscreen-off';
		filename = $('cp_'+id).title;

		var pgCode = ($('cp_'+id).firstChild) ? $('cp_'+id).firstChild.nodeValue : '';
		$('cp_'+id).innerHTML = '';

		this.setLanguage();
		this.setContent(i);
		setTimeout(function(){eval(id+'.setHeight()')},10); // FF needs a delay

		cpEditor = cpWindow.firstChild;
		cpMenu = cpWindow.getElementsByTagName('div')[0];
		cpMenuLanguages = cpMenu.getElementsByTagName('div')[1];
		cpMenuOptions = cpMenu.getElementsByTagName('div')[0];
		cpFullscreen = cpMenuOptions.getElementsByTagName('input')[0];
		cpFilename = cpWindow.getElementsByTagName('em')[0];
		cpLanguage = cpWindow.getElementsByTagName('span')[2];
		cpBody = cpEditor.contentWindow;
		
		this.setFilename(filename);

		if(pgCode.match(/\w/)) CodePress.addEvent(cpEditor,'load', function() { eval(id+'.edit(filename,pgCode)'); }); 
		CodePress.addEvent(window,'resize', function() { eval(id+'.resizeFullScreen()'); });
	}
	
	// set height for codepress window
	this.setHeight = function() {
		cpWindowHeight = $('cp_'+id).clientHeight;
		if(cpWindowHeight) {
			cpEditorHeight = ($('cp_'+id).className.match('hideMenu')) ? cpWindowHeight : cpWindowHeight-20 ;
			cpEditor.style.height = cpEditorHeight + 'px';
		}
		else {
			setTimeout(function(){eval(id+'.setHeight()')},10);
		}
	}
	
	this.edit = function() {
		cpEditor.onload='';
		this.setFilename(arguments[0]);
		this.setLanguage();
		if(!arguments[1]||arguments[1]=='') { // file name of the source code (to open from server)
			cpEditor.src = cpPath+'modules/codepress.'+cpModule+'?action=edit&file='+filename+'&language='+language+'&engine='+cpEngine+'&theme='+cpTheme;
		}
		else { // open code from textarea or directly from parameter
			this.setLanguage(language);
			if($(arguments[1])) this.setCode($(arguments[1]).firstChild.nodeValue); // id name of the source code
			else if(arguments[1].match(/\w/)) this.setCode(arguments[1]);  // text of the source code
			else if(typeof(arguments[1])=='Object') this.setCode(arguments[1].firstChild.nodeValue); // object of the source code
		}
	}

	this.toggleComplete = function(obj) {
		cpBody.CodePress.autocomplete = obj.checked ? true : false ;
		this.hideMenu();
	}
	
	this.setFilename = function() {
		cpFilename.innerHTML = filename = arguments[0] ? arguments[0] : Content.menu.untitledFile;
	}
		
	this.resizeFullScreen = function() {
		if(cpFullscreen.checked) {
			cH = cpWindow.innerHeight ? cpWindow.innerHeight : document.documentElement.clientHeight;
			cW = cpWindow.innerWidth ? cpWindow.innerWidth : document.documentElement.clientWidth;
			cpEditor.style.height = cH-20 + 'px';
			cpWindow.style.height = cH + 'px';
			cpWindow.style.width = cW + 'px';
			if(cpWindow.offsetParent.offsetTop!=0||cpWindow.offsetParent.offsetLeft!=0) {
				cpWindow.style.top = - cpWindow.offsetParent.offsetTop-3 +'px';
				cpWindow.style.left = - cpWindow.offsetParent.offsetLeft-3 +'px';
			}
		} else {
			cH = cpWindow.getHeight() ? cpWindow.getHeight()-20 : cpEditor.clientHeight;
			cW = cpWindow.getWidth() ? cpWindow.getWidth() : cpEditor.clientWidth;
			cpEditor.style.height = cH + 'px';
		}
	}
	
	this.toggleFullScreen = function(obj) {
	    if(obj.checked) {
			for(var i=0,n=codes.length;i<n;i++) if(cpWindow.parentNode!=codes[i])codes[i].style.visibility = 'hidden';
			cpWindow.className = 'cp-window fullscreen-on';
			document.getElementsByTagName('html')[0].style.overflow = 'hidden';
			window.scrollTo(0,0); 
			this.resizeFullScreen(obj);
	    }
	    else {
			for(var i=0,n=codes.length;i<n;i++) codes[i].style.visibility = 'visible';
			cpWindow.className = 'cp-window fullscreen-off';
			document.getElementsByTagName('html')[0].style.overflow = 'auto';
			cpWindow.style.width = '100%';
			cpWindow.style.height = cpWindowHeight+'px';
			cpEditor.style.height = cpEditorHeight+'px';
			if(cpWindow.offsetParent.offsetTop!=0||cpWindow.offsetParent.offsetLeft!=0) 
				cpWindow.style.top = cpWindow.style.left = 'auto'
	    }
		this.hideMenu();
	}

	this.toggleLineNumbers = function(obj) {
	    cpBody.document.getElementsByTagName('body')[0].className = obj.checked ? 'show-line-numbers' : 'hide-line-numbers';
		this.hideMenu();
	}
	
	this.setLanguage = function() {
		if(arguments[0]) {
			language = (typeof(Content.languages[arguments[0]])!='undefined') ? arguments[0] : this.setLanguage();
			cpLanguage.innerHTML = Content.languages[language].name;			
			if(cpBody.document.designMode=='on') cpBody.document.designMode = 'off';
			CodePress.loadScript(cpBody.document, '../languages/'+language+'.js', function () { cpBody.CodePress.syntaxHighlight('init'); })
			cpBody.document.getElementById('cp-lang-style').href = '../languages/'+language+'.css';
			this.hideMenu();
		}
		else {
			var extension = filename.replace(/.*\.([^\.]+)$/,'$1');
			var aux = false;
			for(lang in Content.languages) {
				extensions = ','+Content.languages[lang].extensions+',';
				if(extensions.match(','+extension+',')) aux = lang;
			}
			language = (aux) ? aux : 'generic';
			if(cpLanguage)cpLanguage.innerHTML = Content.languages[language].name;
		}
	}

	this.toogleMenu = function(obj) {
		var img = obj.getElementsByTagName('img')[1];
		var menu = obj.nextSibling;
		menu.className = menu.className.match('hide') ? menu.className.replace('hide','show') : menu.className.replace('show','hide') ;
		img.src = menu.className.match('show') ? cpPath+'themes/'+cpTheme+'/menu-arrow-down.gif' : cpPath+'themes/'+cpTheme+'/menu-arrow-up.gif' ;
	}	
	
	this.hideMenu = function() {
		cpMenuOptions.className = 'cp-options-menu hide';
		cpMenu.getElementsByTagName('img')[1].src = cpPath+'themes/'+cpTheme+'/menu-arrow-up.gif';
		cpMenuLanguages.className = 'cp-languages-menu hide';
		cpMenu.getElementsByTagName('img')[3].src = cpPath+'themes/'+cpTheme+'/menu-arrow-up.gif';		
	}

	this.setContent = function(i) {
		CodePress.detect();
		var allLanguages = '';
		for(lang in Content.languages) allLanguages += '<input type=radio name=lang id="'+i+'-language-'+lang+'" onclick="'+i+'.setLanguage(\''+lang+'\',this)" '+( lang==language ? 'checked="checked"' : ''  )+' /><label for="'+i+'-language-'+lang+'">'+Content.languages[lang].name+'</label><br />';

		cpWindow.innerHTML = '<iframe class="cp-editor" src="'+cpPath+'modules/codepress.'+cpModule+'?engine='+cpEngine+'&language='+language+'&file='+filename+'&theme='+cpTheme+'"></iframe>'+
			'<form><div class="cp-menu">'+
				'<em class="cp-filename"></em>'+
				'<span class="cp-options" onclick="'+i+'.toogleMenu(this)">'+
					'<img src="'+cpPath+'themes/'+cpTheme+'/menu-icon-options.gif" align="top" /> '+Content.menu.options+' <img src="'+cpPath+'themes/'+cpTheme+'/menu-arrow-up.gif" align="top" class="cp-arrow-options" />'+
			    '</span>'+
				'<div class="cp-options-menu hide">'+
   					'<input type="checkbox" id="'+i+'-fullscreen" onclick="'+i+'.toggleFullScreen(this)"><label for="'+i+'-fullscreen">'+Content.menu.fullScreen+'</label><br>'+
					'<input type=checkbox id="'+i+'-linenumbers" onclick="'+i+'.toggleLineNumbers(this)" checked="checked"><label for="'+i+'-linenumbers">'+Content.menu.lineNumbers+'</label><br>'+
					'<input type=checkbox id="'+i+'-complete" onclick="'+i+'.toggleComplete(this)" checked="checked"><label for="'+i+'-complete">'+Content.menu.autoComplete+'</label>'+
				'</div>'+
				'<span class="cp-language" onclick="'+i+'.toogleMenu(this)">'+
					'<img src="'+cpPath+'themes/'+cpTheme+'/menu-icon-languages.gif" align="top" /> <span class="cp-language-name">'+Content.languages[language].name+'</span> <img src="'+cpPath+'themes/'+cpTheme+'/menu-arrow-up.gif" align=top class="cp-arrow-languages" />'+
				'</span>'+
				'<div class="cp-languages-menu hide">'+allLanguages+'</div>'+
			'</div></form>';
			
		$('cp_'+id).appendChild(cpWindow);
	}

	// get code from editor
	this.getCode = function() {
		return cpBody.CodePress.getCode();
	}

	// put some code inside editor
	this.setCode = function(code) {
		cpBody.CodePress.setCode(code);
	}
	
this.initialize(id);
}

CodePress.detect = function() {
	cpEngine = 'older';
	var ua = navigator.userAgent;
	if(ua.match('MSIE')) cpEngine = 'msie';
	else if(ua.match('KHTML')) cpEngine = 'khtml'; 
	else if(ua.match('Opera')) cpEngine = 'opera'; 
	else if(ua.match('Gecko')) cpEngine = 'gecko';
}

CodePress.loadScript = function(target, src, callback) {
	var node = target.createElement('script');
	if (node.addEventListener) node.addEventListener('load', callback, false);
	else node.onreadystatechange = function() { if (this.readyState=='loaded'||this.readyState=='complete') { callback.call(this);} }
	node.src = src;
	target.getElementsByTagName('head').item(0).appendChild(node);
	node = null;
}

CodePress.run = function() {
	codes = document.getElementsByTagName('code');
	for(var i=0;i<codes.length;i++) {
		if(codes[i].className.match('cp')) {
			id = codes[i].id;
			codes[i].style.color = 'silver';
			$(codes[i].id).id = 'cp_'+codes[i].id;	
			eval(id+' = new CodePress("'+id+'")');
		} 
//		else { codes[i] = ' '; }
	}
}

CodePress.addEvent = function(element,event,callback) {
	if (element.addEventListener) element.addEventListener (event,callback,false);
	else if (element.attachEvent) element.attachEvent ('on'+event,callback);
	else eval('element.'+event+' = callback');
}

CodePress.loadStyle = function(href,callback) {
	var node = document.createElement('link');
	node.href = href;
	node.rel = 'stylesheet';
	document.getElementsByTagName('head').item(0).appendChild(node);
	if (node.addEventListener) callback.call();
	else node.onload = function() { callback.call();}
	node = null;
}

Content={};

// Confilcts with prototype!
// $ = function() { return document.getElementById(arguments[0]); }
var cpPath = $('cp-script').src.replace('codepress.js','');

// load css then load script
CodePress.loadStyle(cpPath+'themes/'+cpTheme+'/codepress-editor.css', function() {
	setTimeout(function() {
			if ($('cp-script').lang) var lang = $('cp-script').lang;
			else var lang = "en-us"; // default lang
			CodePress.loadScript(document, cpPath+'content/'+lang+'.js', function() { CodePress.run(); });
		},500)
});
