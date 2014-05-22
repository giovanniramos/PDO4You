<?php

namespace PDO4You;

// Importing classes
use PDOException;
use PDO4You\Singleton;

/**
 * Describe class
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2014, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/giovanniramos/PDO4You
 * @package PDO4You
 */
class Describe
{

    /**
     * Method which shows and describes the tables of the database
     * 
     * @access public static
     * @param string $schema Name of the schema used
     * @return void
     */
    public static function showTables($schema = null)
    {
        try {
            $driver = Singleton::getDriver();

            switch ($driver) {
                case 'mysql': self::showMySqlTables();
                    break;
                case 'pgsql': self::showPgSqlTables($schema);
                    break;
                case 'cubrid': self::showCubridTables();
                    break;
                case 'mssql':
                case 'sqlsrv': self::showMsSqlTables($schema);
                    break;
                default:
                    throw new PDOException(Singleton::$exception['not-implemented'] . ' PDO4You::showTables()');
            }
        } catch (PDOException $e) {
            Singleton::stackTrace($e, false);
        }
    }

    /**
     * MySQL method to display the tables of the database
     * 
     * @access private static
     * @param void 
     * @return void
     */
    private static function showMySqlTables()
    {
        Singleton::setStyle();

        $tables = Singleton::select("SHOW TABLES;");
        $index = array_keys($tables[0]);
        $database = preg_replace('~tables_in_~i', '', $index[0]);

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $database . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br />';
        foreach ($tables as $k1 => $v1) {
            foreach ($v1 as $k2 => $v2) {
                $desc = Singleton::select("DESCRIBE " . $database . "." . $v2);

                $html.= '<div class="code title">Table: <span>' . $v2 . '</span></div>';
                $html.= '<div class="code trace">';
                foreach ($desc as $k3 => $v3) {
                    $html.= '<div class="number">&nbsp;</div> <span><i style="color:#00B;">' . $v3['field'] . "</i> - " . strtoupper($v3['type']) . '</span><br />';
                }
                $html.= '</div>';
            }
        }
        $html.= '</div>';

        echo $html;
    }

    /**
     * PostgreSQL method to display the tables of the database
     * 
     * @access private static
     * @param string $schema Name of scheme
     * @return void
     */
    private static function showPgSqlTables($schema)
    {
        Singleton::setStyle();

        $table_schema = !is_null($schema) ? "table_schema = '" . $schema . "'" : "table_schema NOT SIMILAR TO '(information_schema|pg_%)'";
        $tables = Singleton::select("SELECT table_catalog, table_schema, table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND " . $table_schema . ";");
        $database = $tables[0]['table_catalog'];

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $database . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br />';
        foreach ($tables as $k1 => $v1) {
            $desc = Singleton::select("SELECT d.datname, n.nspname, a.attname AS field, t.typname AS type FROM pg_database d, pg_namespace n, pg_class c, pg_attribute a, pg_type t WHERE d.datname = '" . $v1['table_catalog'] . "' AND n.nspname = '" . $v1['table_schema'] . "' AND c.relname = '" . $v1['table_name'] . "' AND c.relnamespace = n.oid AND a.attnum > 0 AND not a.attisdropped AND a.attrelid = c.oid AND a.atttypid = t.oid ORDER BY a.attnum");

            $html.= '<div class="code title">Table: <span>' . $v1['table_schema'] . '.' . $v1['table_name'] . '</span></div>';
            $html.= '<div class="code trace">';
            foreach ($desc as $k2 => $v2) {
                $html.= '<div class="number">&nbsp;</div> <span><i style="color:#00B;">' . $v2['field'] . "</i> - " . strtoupper($v2['type']) . '</span><br />';
            }
            $html.= '</div>';
        }
        $html.= '</div>';

        echo $html;
    }

    /**
     * CUBRID method to display the tables of the database
     * 
     * @access private static
     * @param void 
     * @return void
     */
    private static function showCubridTables()
    {
        Singleton::setStyle();

        $tables = Singleton::select("SHOW TABLES;");
        $index = array_keys($tables[0]);
        $database = preg_replace('~tables_in_~i', '', $index[0]);

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $database . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br />';
        foreach ($tables as $k1 => $v1) {
            foreach ($v1 as $k2 => $v2) {
                $desc = Singleton::select("SHOW COLUMNS IN " . $v2);

                $html.= '<div class="code title">Table: <span>' . $v2 . '</span></div>';
                $html.= '<div class="code trace">';
                foreach ($desc as $k3 => $v3) {
                    $html.= '<div class="number">&nbsp;</div> <span><i style="color:#00B;">' . $v3['Field'] . "</i> - " . strtoupper($v3['Type']) . '</span><br />';
                }
                $html.= '</div>';
            }
        }
        $html.= '</div>';

        echo $html;
    }

    /**
     * Microsoft SQL Server method to display the tables of the database
     * 
     * @access private static
     * @param string $schema Name of scheme
     * @return void
     */
    private static function showMsSqlTables($schema)
    {
        Singleton::setStyle();

        $table_schema = !is_null($schema) ? "table_schema = '" . $schema . "'" : "table_schema IS NOT NULL";
        $tables = Singleton::select("SELECT table_catalog, table_schema, table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND " . $table_schema . ";");
        $database = $tables[0]['table_catalog'];

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $database . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br />';
        foreach ($tables as $k1 => $v1) {
            $desc = Singleton::select("SELECT table_catalog, table_schema, table_name, column_name AS field, data_type AS type FROM information_schema.columns WHERE table_catalog = '" . $v1['table_catalog'] . "' AND table_name = '" . $v1['table_name'] . "';");

            $html.= '<div class="code title">Table: <span>' . $v1['table_schema'] . '.' . $v1['table_name'] . '</span></div>';
            $html.= '<div class="code trace">';
            foreach ($desc as $k2 => $v2) {
                $html.= '<div class="number">&nbsp;</div> <span><i style="color:#00B;">' . $v2['field'] . "</i> - " . strtoupper($v2['type']) . '</span><br />';
            }
            $html.= '</div>';
        }
        $html.= '</div>';

        echo $html;
    }

}