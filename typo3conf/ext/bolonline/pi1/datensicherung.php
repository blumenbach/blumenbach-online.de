<?php
/*
 * Xajax-Anwendung
 */
session_save_path("/tmp/");
@ session_start();
require_once (t3lib_extMgm :: extPath('xajax') . 'class.tx_xajax.php');

function getDBDumpfile(){
	$response = new tx_xajax_response();
	include_once(t3lib_extMgm :: extPath('bolonline')."/pi1/db_backup.php");

	$content = 'Aktueller Datenbankbackup: <a style="text-decoration:underline" target="_blank" href="'.$backuplink_rel.$backupfile.'">'.$backupfile.'</a>';
	$response->assign('db_backuplink', 'style.display', 'none');
	$response->assign('db_backuplink', 'innerHTML', $content);
	$response->assign('db_backuplink', 'style.display', 'block');
	return $response;
//}
}

?>
<?PHP
/*
mysqldump --compact --no-create-info --replace -uroot -p BlumenbachOnline_productive --tables tx_bolonline_Kerndaten tx_bolonline_Mediafiles tx_bolonline_Mediafiles_PartI tx_bolonline_Mediafiles_PartII tx_bolonline_Mediafiles_PartIII tx_bolonline_Mediafiles_PartIV  tx_bolonline_PartI tx_bolonline_PartII tx_bolonline_PartIII tx_bolonline_PartIV > BlumenbachOnline_productive.tx_bolonline_xyz.dump


mysqldump --opt --no-create-db -uroot -p BlumenbachOnline_productive --tables tx_bolonline_Kerndaten tx_bolonline_Mediafiles tx_bolonline_Mediafiles_PartI tx_bolonline_Mediafiles_PartII tx_bolonline_Mediafiles_PartIII tx_bolonline_Mediafiles_PartIV  tx_bolonline_PartI tx_bolonline_PartII tx_bolonline_PartIII tx_bolonline_PartIV > BlumenbachOnline_productive.tx_bolonline_xyz.dump



mysqldump 
tx_bolonline_Kerndaten K, 
tx_bolonline_Mediafiles M,
tx_bolonline_Mediafiles_PartI MP1,
tx_bolonline_Mediafiles_PartII MP2,
tx_bolonline_Mediafiles_PartIII MP3,
tx_bolonline_Mediafiles_PartIV MP4,
tx_bolonline_PartI P1,
tx_bolonline_PartII P2,
tx_bolonline_PartIII P3,
tx_bolonline_PartIV P4
 --opt -w="
K.kerndaten_id=M.kerndaten_id AND
MP1.partI_id=P1.id AND P1.kerndaten_id=K.id AND
MP2.partII_id=P2.id AND P2.kerndaten_id=K.id AND
MP3.partIII_id=P3.id AND P3.kerndaten_id=K.id AND
MP4.partIV_id=P4.id AND P4.kerndaten_id=K.id AND





mysqldump BlumenbachOnline_productive tx_bolonline_Mediafiles_PartI tx_bolonline_PartI -w="tx_bolonline_Mediafiles_PartI.partI_id=tx_bolonline_PartI.id AND tx_bolonline_PartI.kerndaten_id=4" >test.dump

SELECT K.* FROM tx_bolonline_Kerndaten K WHERE K.kerndaten_id=4;
SELECT M.* FROM tx_bolonline_Mediafiles M WHERE M.kerndaten_id=4;

SELECT MP.* FROM 
tx_bolonline_Mediafiles_PartI MP, 
tx_bolonline_PartI P 
WHERE 
MP.partI_id=P.id AND 
P.kerndaten_id=4;

SELECT MP.* FROM 
tx_bolonline_Mediafiles_PartII MP, 
tx_bolonline_PartII P 
WHERE 
MP.partII_id=P.id AND 
P.kerndaten_id=4;

SELECT MP.* FROM 
tx_bolonline_Mediafiles_PartIII MP, 
tx_bolonline_PartIII P 
WHERE 
MP.partIII_id=P.id AND 
P.kerndaten_id=4;

SELECT MP.* FROM 
tx_bolonline_Mediafiles_PartIV MP, 
tx_bolonline_PartIV P 
WHERE 
MP.partIV_id=P.id AND 
P.kerndaten_id=4;



SELECT P.* FROM 
tx_bolonline_PartI P 
WHERE 
P.kerndaten_id=4;

SELECT P.* FROM 
tx_bolonline_PartII P 
WHERE 
P.kerndaten_id=4;

SELECT P.* FROM 
tx_bolonline_PartIII P 
WHERE 
P.kerndaten_id=4;

SELECT P.* FROM 
tx_bolonline_PartIV P 
WHERE 
P.kerndaten_id=4;
*/

?>

