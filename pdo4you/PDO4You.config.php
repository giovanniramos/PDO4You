<?php

/**
 * Initial Configuration
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2013, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/giovanniramos/PDO4YOU
 * @package PDO4YOU
 * 
 * */

// Connection adapter defined in: settings.ini
// Examples: test, mysql, maria, pgsql, pgsql.dev, pgsql.pro, vcap
define('PDO4YOU_ADAPTER', getenv('SERVER_NAME') == 'localhost' ? 'test' : 'vcap');

// Debugging errors with exception handling
define('PDO4YOU_FIREDEBUG', true);

// Tracking errors with sending notifications via e-mail to the system administrator
define('PDO4YOU_FIREALERT', false);

// E-mail the system administrator to receive notifications
define('PDO4YOU_WEBMASTER', 'pdo4you@gmail.com');