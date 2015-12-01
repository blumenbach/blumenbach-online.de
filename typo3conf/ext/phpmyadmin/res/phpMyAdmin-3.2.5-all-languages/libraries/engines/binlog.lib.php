<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * @version $Id: binlog.lib.php 30792 2010-03-05 22:55:26Z mehrwert $
 * @package phpMyAdmin-Engines
 */

/**
 *
 * @package phpMyAdmin-Engines
 */
class PMA_StorageEngine_binlog extends PMA_StorageEngine
{
    /**
     * returns string with filename for the MySQL helppage
     * about this storage engne
     *
     * @return  string  mysql helppage filename
     */
    function getMysqlHelpPage()
    {
        return 'binary-log';
    }
}

?>
