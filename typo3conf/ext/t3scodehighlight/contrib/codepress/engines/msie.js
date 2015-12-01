/*
 * CodePress - Real Time Syntax Highlighting Editor written in JavaScript - http://codepress.org/
 * 
 * Copyright (C) 2007 Fernando M.A.d.S. <fermads@gmail.com>
 *
 * Contributors :
 *
 * 	Michael Hurni <michael.hurni@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the 
 * GNU Lesser General Public License as published by the Free Software Foundation.
 * 
 * Read the full licence: http://www.opensource.org/licenses/lgpl-license.php
 */


CodePress = {
	scrolling : false,
	autocomplete : true,

	// set initial vars and start sh
	initialize : function() {
		if(typeof(editor)=='undefined'&&!arguments[0]) return;
		chars = '|32|46|62|'; // charcodes that trigger syntax highlighting
		cc = '\u2009'; // control char
		editor = document.getElementById('code');
		editor.contentEditable = 'true';
		document.attachEvent('onkeydown', this.metaHandler);
		document.attachEvent('onkeypress', this.keyHandler);
		window.attachEvent('onscroll', function() { if(!CodePress.scrolling) setTimeout(function(){CodePress.syntaxHighlight('scroll')},1)});
		completeChars = this.getCompleteChars();
		CodePress.syntaxHighlight('init');
		setTimeout(function() { window.scroll(0,0) },50); // scroll IE to top
	},
	
	// treat key bindings
	keyHandler : function(evt) {
		charCode = evt.keyCode;
		
		if(charCode==13 && !evt.ctrlKey && !evt.metaKey && !evt.shiftKey) {
			evt.returnValue = false;
			CodePress.syntaxHighlight('newline',evt);
		}
                if(evt.ctrlKey && charCode==115) { // CTRL-S
                        if (this.saveCode != "" ) {
                            eval(this.saveCode);
                               // Stop the event to avoid browser actions
                            if (evt.preventDefault) {
                               evt.preventDefault();
                               evt.stopPropagation();
                            } else {
                               evt.returnValue = false;
                               evt.cancelBubble = true;
                            }
                        }

                }
		else
		if(completeChars.indexOf('|'+String.fromCharCode(charCode)+'|')!=-1 && parent.CodePress.complete) { // auto complete
			CodePress.complete(String.fromCharCode(charCode))
		}
	    else if(chars.indexOf('|'+charCode+'|')!=-1) { // syntax highlighting
		 	CodePress.syntaxHighlight('generic');
		}
		else if(charCode==46||charCode==8) { // save to history when delete or backspace pressed
		 	CodePress.actions.history[CodePress.actions.next()] = editor.innerHTML;
		}
		else if((charCode==122||charCode==121||charCode==90) && evt.ctrlKey) { // undo and redo
			(charCode==121||evt.shiftKey) ? CodePress.actions.redo() :  CodePress.actions.undo(); 
			evt.returnValue = false;
		}
		else if(charCode==86 && evt.ctrlKey)  { // paste
			// TODO: pasted text should be parsed and highlighted
		}
	},

	metaHandler : function(evt) {
		charCode = evt.keyCode;
		if(charCode==9 || evt.tabKey) { 
			CodePress.snippets(evt);
			evt.returnValue = false;
		}
		else if(charCode==34||charCode==33) { // handle page up/down for IE
			self.scrollBy(0, (charCode==34) ? 200 : -200); 
			evt.returnValue = false; // TODO: fix the parent scrolling
		}
		else if((evt.ctrlKey || evt.metaKey) && evt.shiftKey && charCode!=90)  { // shortcuts = ctrl||appleKey+shift+key!=z(undo) 
			CodePress.shortcuts(charCode);
			evt.returnValue = false;
		}
	},

	// put cursor back to its original position after every parsing
	findString : function() {
	    range = self.document.body.createTextRange();
		if(range.findText(cc)){
			range.select();
			range.text = '';
			range.select();
		}
	},
	
	// split big files, highlighting parts of it
	split : function(code,flag) {
		if(flag=='scroll') { this.scrolling = true; return code; }
		else {
			this.scrolling = false;
			mid = code.indexOf(cc);
			if(mid-2000<0) {ini=0;end=4000;}
			else if(mid+2000>code.length) {ini=code.length-4000;end=code.length;}
			else {ini=mid-2000;end=mid+2000;}
			code = code.substring(ini,end);
			return code.substring(code.indexOf('<P>'),code.lastIndexOf('</P>')+4);
		}
	},
	
	// syntax highlighting parser
	syntaxHighlight : function(flag) {
		if(flag!='init' && flag!='snippets')
			document.selection.createRange().text = cc;
			
		var o = this.parseCode();
		x = z = this.split(o,flag);
		
		if(flag=="newline") {
			var indent = this.getIndent(x);
			if(!indent) {
				x = x.replace(cc,"</P><P>"+cc);
				x = x.replace(new RegExp('<P>'+cc+'<\/P>','g'),'<P>'+cc+'&nbsp;</P>');
				x = x.replace(/<P><\/P>/g,'<P><BR/></P>');
				//x = x.replace(/<P><\/P>/g,'');
				//x = x.replace(/<P>\r\n<\/P>/g,'<P><BR/></P>');
			}
			else x = x.replace(cc,"</P><P>"+indent+cc);
		}
		
		if(arguments[1]&&arguments[2]) x = x.replace(arguments[1],arguments[2]);
	
		for(i=0;i<Language.syntax.length;i++) 
			x = x.replace(Language.syntax[i].input,Language.syntax[i].output);
		
		editor.innerHTML = this.actions.history[this.actions.next()] = (flag=='scroll') ? x : o.replace(z,x);
		if(flag!='init') this.findString();
	},

	snippets : function(evt) {
		var snippets = Language.snippets;
		var trigger = this.getLastWord();
		document.selection.createRange().text = cc;
		for (var i=0; i<snippets.length; i++)
			if(snippets[i].input == trigger) {
				var content = snippets[i].output.replace(/</g,'&lt;');
				content = content.replace(/>/g,'&gt;');
				if(content.indexOf('$0')<0) content += cc;
				else content = content.replace(/\$0/,cc);
				content = content.replace(/\n/g,"\n"+this.getIndent(this.parseCode()));
				content = content.replace(/\n/g,'</P><P>');
				var pattern = new RegExp(trigger+cc);
				this.syntaxHighlight('snippets',pattern,content);
				return true;
			}
		this.syntaxHighlight('snippets',cc,"\t"+cc);
	},
	
	complete : function(trigger) {
		var complete = Language.complete;
		for (var i=0; i<complete.length; i++) {
			if(complete[i].input == trigger) {
				var pattern = new RegExp('\\'+trigger+cc);
				var content = complete[i].output.replace(/\$0/g,cc);
				setTimeout(function () { CodePress.syntaxHighlight('complete',pattern,content)},0); // wait for char to appear on screen
			}
		}
	},
	
	getCompleteChars : function() {
		var cChars = '';
		for(var i=0;i<Language.complete.length;i++)
			cChars += '|'+Language.complete[i].input;
		return cChars+'|';
	},

	shortcuts : function() {
		var cCode = arguments[0];
		if(cCode==13) cCode = '[enter]';
		else if(cCode==32) cCode = '[space]';
		else cCode = '['+String.fromCharCode(charCode).toLowerCase()+']';
		for(var i=0;i<Language.shortcuts.length;i++)
			if(Language.shortcuts[i].input == cCode)
				this.insertCode(Language.shortcuts[i].output,false);
	},
	
	getLastWord : function() {
		var rangeAndCaret = CodePress.getRangeAndCaret();
		var words = rangeAndCaret[0].substring(rangeAndCaret[1]-40,rangeAndCaret[1]);
		words = words.replace(/[\s\r\);]/g,"\n").split("\n");
		return words[words.length-1];
	},

	getRangeAndCaret : function() {	
		var range = document.selection.createRange();
		var caret = Math.abs(range.moveStart("character", -1000000)+1);
		range = this.getCode();		
		range = range.replace(/\n\r/gi,'  ');
		range = range.replace(/\n/gi,'');
		range = range.replace(/&shy;/gi,'');
		return [range.toString(),caret];
	},
	
	insertCode : function(code,replaceCursorBefore) {
		var repdeb = "";
		var repfin = "";
		
		if(replaceCursorBefore) repfin = code;
		else repdeb = code;
		
		if(typeof document.selection != 'undefined') {
			var range = document.selection.createRange();
			range.text = repdeb + repfin;
			range = document.selection.createRange();
			range.move('character', -repfin.length);
			range.select();	
		}	
	},
	
	getIndent : function(code) {
		var lines = code.split("<P>");
		var indent = currentLine = "";
		for (k=1;k<lines.length;k++)
			if(lines[k].indexOf(cc)!=-1) {
				currentLine = lines[k];
				break;
			}
		if(!currentLine) return "";
		for (l=0;l<currentLine.length;l++) {
			if(currentLine.split('')[l]=="\t") indent+="\t";
			else break;
		}	
		return indent;
	},
	
	parseCode : function() {
		var o = editor.innerHTML;
		o = o.replace(/<P>&nbsp;<\/P>/g,'<P><BR/></P>');
		o = o.replace(/&nbsp;/g,'');			
		o = o.replace(/<P>/g,'\n');
		o = o.replace(/<\/P>/g,'\r');
		o = o.replace(/<.*?>/g,'');
		o = '<PRE><P>'+o+'</P></PRE>';
		//o = o.replace(/\n(\n)+/g,'\r');
		o = o.replace(/\n\r/g,'<P></P>');
		o = o.replace(/\n/g,'<P>');
		o = o.replace(/\r/g,'<\/P>');
		o = o.replace(/<P>(<P>)+/,'<P>');
		o = o.replace(/<\/P>(<\/P>)+/,'</P>');
		return o.replace(/<P><\/P>/g,'<P><BR/></P>');
	},

	// get code from editor	
	getCode : function() {
		var code = editor.innerHTML;
		code = code.replace(/<br>/g,'\n');
		code = code.replace(/<\/p>/gi,'\r');
		code = code.replace(/<p>/i,''); // IE first line fix
		code = code.replace(/<p>/gi,'\n');
		code = code.replace(/&nbsp;/gi,'');
		code = code.replace(/\u2009/g,'');
		code = code.replace(/<.*?>/g,'');
		code = code.replace(/&lt;/g,'<');
		code = code.replace(/&gt;/g,'>');
		code = code.replace(/&amp;/gi,'&');
		return code;
	},

	// put code inside editor
	setCode : function() {
		var code = arguments[0];
		code = code.replace(/\u2009/gi,'');
		code = code.replace(/&/gi,'&amp;');		
       	code = code.replace(/</g,'&lt;');
        code = code.replace(/>/g,'&gt;');
		editor.innerHTML = '<pre>'+code+'</pre>';
	},
	
	// undo and redo methods
	actions : {
		pos : -1, // actual history position
		history : [], // history vector
		undo : function() {
			if(editor.innerHTML.indexOf(cc)==-1){
				document.selection.createRange().text = cc;
			 	this.history[this.pos] = editor.innerHTML;
			}
			this.pos--;
			if(typeof(this.history[this.pos])=='undefined') this.pos++;
			editor.innerHTML = this.history[this.pos];
			CodePress.findString();
		},	
		redo : function() {
			this.pos++;
			if(typeof(this.history[this.pos])=='undefined') this.pos--;
			editor.innerHTML = this.history[this.pos];
			CodePress.findString();
		},	
		next : function() { // get next vector position and clean old ones
			if(this.pos>20) this.history[this.pos-21] = undefined;
			return ++this.pos;
		}
	}
}

Language={};
window.attachEvent('onload', function() { CodePress.initialize('new');});
