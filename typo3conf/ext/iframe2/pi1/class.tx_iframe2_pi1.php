<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002 Daniel Brün (dbruen@saltation.de)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Plugin 'IFrame2' for the 'iframe2' extension.
 *
 * @author	Daniel Brün <dbruen@saltation.de>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_iframe2_pi1 extends tslib_pibase {
	var $prefixId = "tx_iframe2_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_iframe2_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "iframe2";	// The extension key.
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
	
		if (trim($this->cObj->data["tx_iframe2_iframe_url"]))	{

			/* Process parameter values. If none are given, use Defaults */
			/* Default values */
			$if_width = (trim($this->cObj->data["tx_iframe2_iframe_width"]) != "") 
				 ? trim($this->cObj->data["tx_iframe2_iframe_width"]) : "100%";

			$if_height = (trim($this->cObj->data["tx_iframe2_iframe_height"]) != "") 
				 ? trim($this->cObj->data["tx_iframe2_iframe_height"]) : "100%";

			$if_scroll = (trim($this->cObj->data["tx_iframe2_iframe_scrolling"]) != "")
				 ? trim($this->cObj->data["tx_iframe2_iframe_scrolling"]) : "auto";

			$if_border = (trim($this->cObj->data["tx_iframe2_iframe_border"]) != "")
				 ? trim($this->cObj->data["tx_iframe2_iframe_border"]) : "1";
			
			/* Rules for $params: Always prepend with a space! */
			
			/* Add URL to tag-parameters */
			$params = ' src="'.htmlspecialchars(trim($this->cObj->data["tx_iframe2_iframe_url"])). '"';
			
			/* Add width/height to tag-parameters */
			$params .= ' width="' . $if_width 
				 . '" height="' . $if_height 
				 . '" style="width:' . $if_width . ';height:' . $if_height . ';"';
			
			/* Add frameborder to tag-parameters */
			$params .= ' frameborder="' . $if_border . '"';
			
			/* Add scrolling to tag-parameters */
			$params .= ' scrolling="' . $if_scroll . '"';
			
			if($this->conf["width"] != "" && $this->conf["height"] != "")
				$size_params = ' width="' . $this->conf["width"] . '" height="' . $this->conf["height"] . '"';
			else
				$size_params = ' style="height=100%; width=100%;"';
			
			$content='<IFRAME'. $params. '></IFRAME>';
			$content=$this->cObj->stdWrap($content,$this->conf["stdWrap."]);
		} else {
			$content='[Plugin configuration:] Please enter an URL in the url-field of the content element!';
		}
		
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/iframe2/pi1/class.tx_iframe2_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/iframe2/pi1/class.tx_iframe2_pi1.php"]);
}

?>