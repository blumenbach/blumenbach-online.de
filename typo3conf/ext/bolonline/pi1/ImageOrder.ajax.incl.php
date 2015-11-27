<?php
/*
 * Xajax-Anwendung
 */
session_save_path("/tmp/");
@ session_start();
require_once (t3lib_extMgm :: extPath('xajax') . 'class.tx_xajax.php');

function setImageOrder($table, $id, $order){
    $Bol = new BlumenbachOnline();
    $Bol->setImageOrder($table, $id, $order);

  	//$response = new tx_xajax_response();
    //$out = '<span>'.$res.'</span>';
    //$response->assign('log','innerHTML',$out);
    //return $response;
}
