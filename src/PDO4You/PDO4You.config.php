<?php

// Defining namespaces
namespace PDO4You;

/**
 * Initial Configuration
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2013, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/giovanniramos/PDO4You
 * @package PDO4You
 * 
 * */
interface Config
{
    // Connection adapter defined in: PDO4You.settings.ini
    // Examples: abc123, mysql, maria, pgsql, pgsql.dev, pgsql.pro, vcap
    const PDO4YOU_ADAPTER = 'abc123';

    // Debugging errors with exception handling
    const PDO4YOU_FIREDEBUG = true;

    // Tracking errors with sending notifications via e-mail to the system administrator
    const PDO4YOU_FIREALERT = false;

    // E-mail the system administrator to receive notifications
    const PDO4YOU_WEBMASTER = 'pdo4you@gmail.com';

}
