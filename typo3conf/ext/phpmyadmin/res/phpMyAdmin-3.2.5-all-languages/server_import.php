<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @version $Id: server_import.php 30792 2010-03-05 22:55:26Z mehrwert $
 * @package phpMyAdmin
 */

/**
 *
 */
require_once './libraries/common.inc.php';

/**
 * Does the common work
 */
require './libraries/server_common.inc.php';


/**
 * Displays the links
 */
require './libraries/server_links.inc.php';

$import_type = 'server';
require './libraries/display_import.lib.php';
/**
 * Displays the footer
 */
require './libraries/footer.inc.php';
?>

