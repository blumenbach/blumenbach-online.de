<?PHP
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/ImageResizer.class.php");
/**
 * @author Sven Thomas
 * @copyright Copyright (c) 2008 by Sven, thomas@mutonline.de
 * @license License is limited for this procect
 * @08.09.2010
 *
 **/
class FileUploader {
	var $IR = null;
	var $UPLOADFILE = null;
	var $labelname = null;


	 function __construct() {
//		require_once ("ImageResizer.class.php");
//		$this->IR = new ImageResizer();
		//		extract($GLOBALS);
		$this->UPLOADFILE = $_FILES;
	}
	
	function FileUploader(){
		$this->__construct();
	}

	 function MediaUploadConverter() {
		@session_start();
//		require_once ("ImageResizer.class.php");
//		$this->IR = new ImageResizer();
		//		extract($GLOBALS);
		$this->UPLOADFILE = $_FILES;
		//__construct();

	}

	/**
		 * @ sets name of upload-field label
		 */
	function setUploadFieldLabelname($name) {
		$this->labelname = $name;
	}

	/**
	 * @ return size of uploaded file
	 */
	function getUploadFilesize() {
		return $this->UPLOADFILE[$this->labelname]['size'];
	}

	/**
	 * @ return temporary name of uploaded file
	 */
	function getUploadFileTmpName() {
		if (strlen($this->labelname) < 1) {
			echo "set first Uploadfields labelname with setUploadFieldLabelname('Labelname'')";
			return;
		}
		return $this->UPLOADFILE[$this->labelname]['tmp_name'];
	}

	/**
	 * @ return name of uploaded file
	 */
	function getUploadFileName() {
		if (strlen($this->labelname) < 1) {
			echo "set first Uploadfields labelname with setUploadFieldLabelname('Labelname'')";
			return;
		}
		return $this->UPLOADFILE[$this->labelname]['name'];
	}

		

	function getUploadFileMimeType() {
		return $this->UPLOADFILE[$this->labelname]["type"];
	}

	function getMimeType($media_type = "") {
		$fielcontents = split("\n", file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/classes/mimetypes.txt"));
		$mimetypes = array ();
		
		foreach ($fielcontents as $key => $value) {
			$tmp = split("\t", $value);
			$mimetypes[trim($tmp[0])] = trim($tmp[1]);
		}
		if (!$mimetypes[$media_type]) {
			echo "\nFehler: Media-Typ \"$media_type\" nicht bekannt!";
			return "";
		}
		//		print_r($mimetypes);
		return $mimetypes[$media_type];
	}

	function getMediaType($mime_type = "") {
		$mediatypes = array ();
		$fielcontents = split("\n", file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/classes/mimetypes.txt"));
		foreach ($fielcontents as $key => $value) {
			$tmp = split("\t", $value);
			$mediatypes[trim($tmp[1])] = trim($tmp[0]);
		}

		if (!$mediatypes[$mime_type]) {
			echo "\nFehler: Mime-Typ \"$mime_type\" nicht bekannt!";
			return "";
		}
		//		print_r($mediatypes);
		return $mediatypes[$mime_type];
	}

	function cleanUploadFilename($dateiname) {
		$fileendung = preg_replace('/.*(\..*$)/', "\$1", $dateiname); #alle sonderzeichen entfernen!
		//		echo"Dateiendung: ".$fileendung."<br>";
		$dateiname = preg_replace('/[^a-zA-Z0-9]/', '', $dateiname); #alle sonderzeichen entfernen!
		$dateiname = substr($dateiname, 0, (strlen($dateiname) - strlen($fileendung) + 1)) . (microtime(true) * 100) . $fileendung;

		return $dateiname;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bolonline/pi1/class.tx_nwstmpl_pi1.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bolonline/pi1/class.tx_nwstmpl_pi1.php']);
}
?>