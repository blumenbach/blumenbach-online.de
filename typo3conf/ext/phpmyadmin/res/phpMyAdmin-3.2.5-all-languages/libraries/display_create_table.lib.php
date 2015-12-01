<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Displays form for creating a table (if user has privileges for that)
 *
 * for MySQL >= 4.1.0, we should be able to detect if user has a CREATE
 * privilege by looking at SHOW GRANTS output;
 * for < 4.1.0, it could be more difficult because the logic tries to
 * detect the current host and it might be expressed in many ways; also
 * on a shared server, the user might be unable to define a controluser
 * that has the proper rights to the "mysql" db;
 * so we give up and assume that user has the right to create a table
 *
 * Note: in this case we could even skip the following "foreach" logic
 *
 * Addendum, 2006-01-19: ok, I give up. We got some reports about servers
 * where the hostname field in mysql.user is not the same as the one
 * in mysql.db for a user. In this case, SHOW GRANTS does not return
 * the db-specific privileges. And probably, those users are on a shared
 * server, so can't set up a control user with rights to the "mysql" db.
 * We cannot reliably detect the db-specific privileges, so no more
 * warnings about the lack of privileges for CREATE TABLE. Tested
 * on MySQL 5.0.18.
 *
 * @version $Id: display_create_table.lib.php 30792 2010-03-05 22:55:26Z mehrwert $
 * @package phpMyAdmin
 */
if (! defined('PHPMYADMIN')) {
    exit;
}

/**
 *
 */
require_once './libraries/check_user_privileges.lib.php';

$is_create_table_priv = true;

?>
<form method="post" action="tbl_create.php"
    onsubmit="return (emptyFormElements(this, 'table') &amp;&amp; checkFormElementInRange(this, 'num_fields', '<?php echo str_replace('\'', '\\\'', $GLOBALS['strInvalidFieldCount']); ?>', 1))">
<fieldset>
    <legend>
<?php
if ($GLOBALS['cfg']['PropertiesIconic']) {
    echo '<img class="icon" src="' . $pmaThemeImage . 'b_newtbl.png" width="16" height="16" alt="" />';
}
echo sprintf($strCreateNewTable, PMA_getDbLink());
?>
    </legend>
    <?php echo PMA_generate_common_hidden_inputs($db); ?>
    <div class="formelement">
        <?php echo $strName; ?>:
        <input type="text" name="table" maxlength="64" size="30" />
    </div>
    <div class="formelement">
        <?php echo $strNumberOfFields; ?>:
        <input type="text" name="num_fields" size="2" />
    </div>
    <div class="clearfloat"></div>
</fieldset>
<fieldset class="tblFooters">
    <input type="submit" value="<?php echo $strGo; ?>" />
</fieldset>
</form>