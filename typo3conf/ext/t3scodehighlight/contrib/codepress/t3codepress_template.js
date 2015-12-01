function sendAjaxData() {


	$$("code.cp").each(function(codeitem) {
			var id = codeitem.id;
			id = id.substr(3);
	    		  // copy code to the (hidden) textarea
			if ($(id+'_ta')) {
				eval("$('"+id+"_ta').value = "+id+".getCode();");
			}
		});

	formdata = "submitAjax=1&" + Form.serialize(document.editForm);
	url = document.editForm.action;

	var myAjax = new Ajax.Request(
			url, 
			{ method: "post",
			  parameters: formdata, 
			  onComplete: ajaxOnSuccess
			});
}

function ajaxOnSuccess(ajaxrequest) {
	if (ajaxrequest.status == 200) {

                $$(".cp-filename").each(
			function(item) { 	
				new Insertion.Bottom(item, "<span class=\'cp-saved\'>" + ajaxrequest.responseText + "</span>")
			});

		  // remove after 2 seconds
                setTimeout('$$(".cp-saved").each(function(i){Element.remove(i)});', 2000);

	} else {
		alert("Error!");
	};
}

function setSaveCode() {
	  $$(".cp-editor").each(function(item) {
	    item.contentDocument.saveCode = "parent.sendAjaxData();";
	  });
}

function page_loaded() {
	$$('code.cp').each(
 		function(codeitem) {
			if ($(codeitem.id+'_ta')) {
	  			Element.hide($(codeitem.id+'_ta'));
			}
		}
	);
	  // wait a second till codepress builds his iframe-stuff 
	setTimeout("setSaveCode();",1001);
}


Event.observe(window, "load", page_loaded, false);

