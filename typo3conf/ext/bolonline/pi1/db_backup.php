<?PHP

$backupdir = t3lib_extMgm :: extPath('bolonline')."/db_backupfiles/";
$backuplink_rel = "/typo3conf/ext/bolonline/db_backupfiles/";
$backupfile = 'BlumenbachOnline_productive_'. date("Y-m-d-H-i-s") . '.dump';
chdir($backupdir);
if(preg_match("|produktion|",$_SERVER['HTTP_HOST'])){ //Entwicklungsserver
	$cmd = '/usr/bin/mysqldump --opt --no-create-db -uroot -pzrtyh9 BlumenbachOnline_productive --tables tx_bolonline_Kerndaten tx_bolonline_Mediafiles tx_bolonline_Mediafiles_PartI tx_bolonline_Mediafiles_PartII tx_bolonline_Mediafiles_PartIII tx_bolonline_Mediafiles_PartIV  tx_bolonline_PartI tx_bolonline_PartII tx_bolonline_PartIII tx_bolonline_PartIV tx_bolonline_Hauptkategorie tx_bolonline_HauptkategorieZuordnung tx_bolonline_PartIII_associations tx_bolonline_PartIV_associations> '.$backupfile;
} else { //Liveserver
	$cmd = '/usr/bin/mysqldump --opt --no-create-db -uroot -poderwas?! BlumenbachOnline_productive --tables tx_bolonline_Kerndaten tx_bolonline_Mediafiles tx_bolonline_Mediafiles_PartI tx_bolonline_Mediafiles_PartII tx_bolonline_Mediafiles_PartIII tx_bolonline_Mediafiles_PartIV  tx_bolonline_PartI tx_bolonline_PartII tx_bolonline_PartIII tx_bolonline_PartIV tx_bolonline_Hauptkategorie tx_bolonline_HauptkategorieZuordnung tx_bolonline_PartIII_associations tx_bolonline_PartIV_associations > '.$backupfile;
}
system($cmd);
?>

