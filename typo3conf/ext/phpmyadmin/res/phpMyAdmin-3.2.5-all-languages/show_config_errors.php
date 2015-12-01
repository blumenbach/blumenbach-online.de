<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Simple wrapper just to enable error reporting and include config
 *
 * @version $Id: show_config_errors.php 30792 2010-03-05 22:55:26Z mehrwert $
 * @package phpMyAdmin
 */

echo "Starting to parse config file...\n";

error_reporting(E_ALL);
/**
 * Read config file.
 */
require './config.inc.php';

?>
