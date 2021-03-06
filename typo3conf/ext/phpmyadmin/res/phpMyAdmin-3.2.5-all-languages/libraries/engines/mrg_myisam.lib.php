<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * @version $Id: mrg_myisam.lib.php 30792 2010-03-05 22:55:26Z mehrwert $
 * @package phpMyAdmin-Engines
 */

/**
 *
 */
include_once './libraries/engines/merge.lib.php';

/**
 *
 * @package phpMyAdmin-Engines
 */
class PMA_StorageEngine_mrg_myisam extends PMA_StorageEngine_merge
{
    /**
     * returns string with filename for the MySQL helppage
     * about this storage engne
     *
     * @return  string  mysql helppage filename
     */
    function getMysqlHelpPage()
    {
        return 'merge';
    }
}

?>
