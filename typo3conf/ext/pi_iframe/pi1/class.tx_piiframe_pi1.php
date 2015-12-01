<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002 Kasper Skårhøj (kasper@typo3.com)
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
 * Plugin 'IFRAME' for the 'pi_iframe' extension.
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_piiframe_pi1 extends tslib_pibase {
	var $prefixId = "tx_piiframe_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_piiframe_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "pi_iframe";	// The extension key.
	
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_loadLL();
	
		if (trim($this->cObj->data["tx_piiframe_iframe_url"]))	{
			$content='<IFRAME src="'.htmlspecialchars(trim($this->cObj->data["tx_piiframe_iframe_url"])).'" style="height=100%; width=100%"></IFRAME>';
			$content=$this->cObj->stdWrap($content,$this->conf["stdWrap."]);
		} else {
			$content='[Plugin configuration:] Please enter an URL in the url-field in the content element!';
		}

		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/pi_iframe/pi1/class.tx_piiframe_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/pi_iframe/pi1/class.tx_piiframe_pi1.php"]);
}

?>