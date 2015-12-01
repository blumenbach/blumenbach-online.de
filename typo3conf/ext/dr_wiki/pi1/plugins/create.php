<?php
// This plugin inserts a form into the page that allows you to create
// a new wiki page without the need to link to it first.

class tx_drwiki_pi1_create {
		
	function getDefaultParams(){
		return array();
	}
	
	function main($object, $params){

		$prefix = get_class($this);

		$content .= '<form action="" onsubmit="'.$prefix.'_submit(this);return false">';
		$content .= '  <input type="text" name="'.$prefix.'_keyword" size="30">';
  	$content .= '  &nbsp;<input type="submit" value="Create">';
  	$content .= '</form>';

  	$content .= '<script type="text/javascript">';
  	$content .= 'function '.$prefix.'_submit(f) {';
  	$content .= '  uri = "'.$object->pi_linkTP_keepPIvars_url(array("keyword" => "__marker__", "showUid" => ""), 1, 0).'";';
	  $content .= '  uri = uri.replace("__marker__", f.'.$prefix.'_keyword.value);';
	  $content .= '  document.location = uri;';
	  $content .= '}';
	  $content .= '</script>';
	  
	  return $content;

	}
}
?>