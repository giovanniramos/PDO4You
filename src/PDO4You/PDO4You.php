<?php

namespace PDO4You;

// Importing classes
use PDO4You\Pagination;

// Loading the configuration file
require_once 'PDO4You.config.php';

/**
 * PDO4You is a class that implements the Singleton design pattern for 
 * connecting the database using the PDO extension (PHP Data Objects)
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2014, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/giovanniramos/PDO4You
 * @package PDO4You
 * @version 4.4
 */
class PDO4You implements Config
{
    /**
     * Stores the path of the file that contains the settings for each adapter 
     * to connect to a database
     * 
     * @access private static
     * @var string
     */
    private static $settings;

    /**
     * Stores the name of the server machine on which the database resides
     * 
     * @access private static
     * @var string
     */
    private static $datahost;

    /**
     * Stores the name of the port on which the server is running
     * 
     * @access private static
     * @var string
     */
    private static $dataport;

    /**
     * Stores the name of the current instance of the connection
     * 
     * @access private static
     * @var string
     */
    private static $connection;

    /**
     * Stores an object instance PDO connection
     * 
     * @access private static
     * @var object
     */
    private static $instance;

    /**
     * Stores object instances PDO connection
     * 
     * @access private static
     * @var array
     */
    private static $handle = array();

    /**
     * Stores the definition of persistent connection
     * 
     * @access private static
     * @var boolean
     */
    private static $persistent = false;

    /**
     * Stores the ID of the last inserted row or sequence value
     * 
     * @access private static
     * @var string
     */
    private static $lastId;

    /**
     * Stores the total of affected rows in last CRUD operation
     * 
     * @access private static
     * @var string
     */
    private static $rowCount;

    /**
     * Stores messages Exception thrown
     * 
     * @access public static
     * @var array
     */
    public static $exception = array(
        'code-1044' => 'Access denied for user: \'%1$s\'',
        'code-1045' => 'Failed communication with the database using: \'%1$s\'@\'%2$s\'',
        'code-2002' => 'No connection could be made because the destination machine actively refused. This host is not known.',
        'code-2005' => 'No communication with the host provided. Check your settings.',
        'unrecognized' => 'The Adapter/DSN Instance was not recognized.',
        'no-database' => 'Database unknown. Check your settings.',
        'no-instance' => 'No instance of object PDO4You available. Unable to access the methods.',
        'no-instruction' => 'The SQL statement is missing.',
        'no-argument-sql' => 'The SQL argument is missing.',
        'not-implemented' => 'Method not implemented.',
        'critical-error' => 'Critical error detected in the system.',
        'json-error-depth' => 'Maximum stack depth exceeded.',
        'json-error-state-mismatch' => 'Mismatch or arithmetic operation modes impossible to be represented.',
        'json-error-ctrl-char' => 'Attribute control unexpected was found.',
        'json-error-syntax' => 'The query is poorly formatted JSON provided.'
    );

    /**
     * The constructor is set to private, preventing direct instance of the class
     * 
     * @access private
     */
    private function __construct()
    {
        exit;
    }

    /**
     * Method to set the file path which contains the settings for each adapter 
     * connection with a database
     * 
     * @access public static
     * @param string $settings Path of the file that contains the configuration of adapters
     */
    public static function setSettings($settings)
    {
        self::$settings = $settings;
    }

    /**
     * Method to retrieve the path of the file that contains the configuration of adapters
     * 
     * @access private static
     * @return string
     */
    private static function getSettings()
    {
        // File directory
        $directory = dirname(__FILE__);

        // INI file of the default configuration
        $settings = isset(self::$settings) ? self::$settings : $directory . '/PDO4You.settings.ini';

        return $settings;
    }

    /**
     * Method Singleton connection
     * 
     * @access private static
     * @param string $alias Pseudonym of a connection instance
     * @param string $driver Driver DSN connection
     * @param string $user Username of the database
     * @param string $pass Password of the database
     * @param array $option Configuration the connection driver
     * @return void
     * @throws \PDOException Throws an exception in case of connection failures
     */
    private static function singleton($alias, $driver, $user, $pass, $option)
    {
        try {
            try {
                $server_addr = $_SERVER['SERVER_ADDR'];

                // Force column names to lower case
                $option[\PDO::ATTR_CASE] = \PDO::CASE_LOWER;

                // Establishes a persistent connection to the database
                $option[\PDO::ATTR_PERSISTENT] = self::$persistent;

                // Throws exceptions in development environment and report errors in production
                $option[\PDO::ATTR_ERRMODE] = ($server_addr == '127.0.0.1' || $server_addr == '::1') ? \PDO::ERRMODE_EXCEPTION : \PDO::ERRMODE_SILENT;

                // Executes a command on the MySQL server to set the charset to UTF-8
                $option[defined(\PDO::MYSQL_ATTR_INIT_COMMAND) ? \PDO::MYSQL_ATTR_INIT_COMMAND : 1002] = "SET NAMES utf8";

                // Creates the instance with the settings
                $instance = @ new \PDO($driver, $user, $pass, $option);

                self::setHandle($alias, $instance);
                self::setInstance($alias);
            } catch (\PDOException $e) {
                $error = self::getErrorInfo($e);

                if ($e->getMessage() == 'could not find driver' || $e->getMessage() == 'invalid data source name') {
                    throw new \PDOException(self::$exception['unrecognized']);
                } elseif ($error['code'] == '2005') {
                    throw new \PDOException(self::$exception['code-2005']);
                } elseif ($error['code'] == '2002') {
                    throw new \PDOException(self::$exception['code-2002']);
                } elseif ($error['code'] == '1044') {
                    throw new \PDOException(sprintf(self::$exception['code-1044'], $user));
                } elseif ($error['code'] == '1045') {
                    throw new \PDOException(sprintf(self::$exception['code-1045'], $user, $pass));
                } else {
                    throw $e;
                }
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * Method for setting a connection instance
     * 
     * @access public static
     * @param string $alias Pseudonym of a connection instance
     */
    public static function setInstance($alias)
    {
        self::$instance = self::getHandle($alias == null ? 'standard' : $alias);
    }

    /**
     * Method to get a single instance of the database per connection
     * 
     * @access public static
     * @param string $alias Pseudonym that will be used as a pointer to an instance of established connection
     * @param string $type Connection type if using "Initial Setup" or "Full DSN"
     * @param string $user Username of the database
     * @param string $pass Password of the database
     * @param array $option Configuration the connection driver
     * @return object
     * @throws \PDOException Throws an exception in case of connection failures
     */
    public static function getInstance($alias = 'standard', $type = null, $user = null, $pass = null, Array $option = null)
    {
        try {
            try {
                if (!array_key_exists($alias, self::$handle)) {
                    // INI file containing the initial settings of the adapters the database
                    $ini_file = self::getSettings();

                    // Checks if the INI file exists
                    if (file_exists($ini_file)) {
                        $_dir_file = dirname($ini_file);
                        $_ini_file = basename($ini_file);

                        // Checks whether the file is readable
                        if (is_readable($ini_file)) {
                            // Interprets the file containing the initial settings
                            $datafile = self::parse_ini_file_advanced($ini_file);

                            // Initial settings for database adapters
                            if (isset($datafile['PDO4YOU_ADAPTER'])) {
                                // Captures all the names of the keys of an array
                                $Keys = function($array) {
                                            $keys = array();
                                            foreach ($array as $key => $value) {
                                                $slice_item = array_slice($value, 0, 1);
                                                $first_item = array_shift($slice_item);
                                                if (is_array($first_item)) {
                                                    foreach ($value as $key2 => $value2) {
                                                        $keys[] = $key . '.' . $key2;
                                                    }
                                                } else {
                                                    $keys[] = $key;
                                                }
                                            }
                                            return $keys;
                                        };
                                // List with the names of adapters available
                                $adapters = $Keys($datafile['PDO4YOU_ADAPTER']);
                            } else {
                                exit('The settings for existing databases, were not configured in the <strong>' . $_ini_file . '</strong>.');
                            }
                        } else {
                            exit('The <strong>' . $_ini_file . '</strong> file can not be read in the directory:<br /> ' . $_dir_file);
                        }
                    }

                    // Checks the selected adapter
                    if (isset($adapters)) {
                        $adapter = ($alias == 'standard') ? static::PDO4YOU_ADAPTER : $alias;

                        if (empty($adapter))
                            return;

                        if ($adapter == 'vcap') {
                            $json = json_decode(getenv("VCAP_SERVICES"), true);
                            $data = $datafile['PDO4YOU_ADAPTER']['vcap'];
                            $part = preg_split('~[|]~', $data['vcap']);
                            $conf = $json[$part[0]][$part[1]]['credentials'];

                            $type = isset($data['type']) ? $data['type'] : null;
                            $host = isset($conf['hostname']) ? $conf['hostname'] : null;
                            $port = isset($conf['port']) ? $conf['port'] : null;
                            $user = isset($conf['username']) ? $conf['username'] : null;
                            $pass = isset($conf['password']) ? $conf['password'] : null;
                            $base = isset($conf['name']) ? $conf['name'] : null;
                        } else {
                            $part = preg_split('~[.]~', preg_replace('~[\s]{1,}~', null, $adapter));
                            $conf = count($part) == 2 ? @$datafile['PDO4YOU_ADAPTER'][$part[0]][$part[1]] : @$datafile['PDO4YOU_ADAPTER'][$part[0]];

                            // Checks if the selected adapter in the instance exists
                            if (in_array($adapter, $adapters)) {
                                $type = isset($conf['type']) ? $conf['type'] : null;
                                $host = isset($conf['host']) ? $conf['host'] : null;
                                $port = isset($conf['port']) ? $conf['port'] : null;
                                $user = isset($conf['user']) ? $conf['user'] : null;
                                $pass = isset($conf['pass']) ? $conf['pass'] : null;
                                $base = isset($conf['base']) ? $conf['base'] : null;
                            }
                        }
                    }

                    // Checks the type of adapter and mounts the DNS
                    $type = strtolower($type);
                    switch ($type) {
                        case 'maria': $driver = 'mysql:' . (!(empty($base)) ? 'dbname=' . $base . ';' : null) . 'host=' . $host . ';port=' . $port . ';';
                            break;
                        case 'mysql':
                        case 'pgsql':
                        case 'cubrid': $driver = $type . ':' . (!(empty($base)) ? 'dbname=' . $base . ';' : null) . 'host=' . $host . ';port=' . $port . ';';
                            break;
                        case 'mssql':
                        case 'dblib':
                        case 'sybase': $driver = $type . ':' . (!(empty($base)) ? 'dbname=' . $base . ';' : null) . 'host=' . $host . ';';
                            break;
                        case 'sqlsrv': $driver = 'sqlsrv:' . (!(empty($base)) ? 'database=' . $base . ';' : null) . 'server=' . $host . ';';
                            break;
                        case 'oracle': $driver = 'oci:' . (!(empty($base)) ? 'dbname=' . $base : null);
                            break;
                        case 'sqlite': $driver = 'sqlite:' . (!(empty($base)) ? $base : null);
                            break;
                        default: $driver = $type;
                    }

                    // Initializes the Singleton connection
                    self::singleton($alias, $driver, $user, $pass, $option);
                }
            } catch (\PDOException $e) {
                $error = self::getErrorInfo($e);

                if ($error['state'] == '42000') {
                    throw new \PDOException(self::$exception['no-database']);
                } else {
                    throw $e;
                }
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }

        return self::$instance;
    }

    /**
     * Method for assigning a new object instance PDO connection
     * 
     * @access private static
     * @param string $alias Pseudonym to identify the connection instance
     * @param \PDO $instance Object PDO connection
     */
    private static function setHandle($alias, \PDO $instance)
    {
        self::$handle[$alias] = $instance;
    }

    /**
     * Method to return an object PDO connection
     * 
     * @access private static
     * @param string $alias Pseudonym of a connection instance
     * @return object
     */
    private static function getHandle($alias)
    {
        self::setConnection($alias);

        return @ self::$handle[$alias];
    }

    /**
     * Method to set the server name
     * 
     * @access private static
     * @param string $host Server name
     */
    private static function setDatahost($host)
    {
        self::$datahost = $host;
    }

    /**
     * Method to retrieve the server name
     * 
     * @access public static
     * @return string
     */
    public static function getDatahost()
    {
        return self::$datahost;
    }

    /**
     * Method to set the port number of the server
     * 
     * @access private static
     * @param string $port Port number
     */
    private static function setDataport($port)
    {
        self::$dataport = $port;
    }

    /**
     * Method to retrieve the port number of the server
     * 
     * @access public static
     * @return string
     */
    public static function getDataport()
    {
        return self::$dataport;
    }

    /**
     * Method to define which the current instance of connection
     * 
     * @access private static
     * @param string $alias Pseudonym of a connection instance
     */
    private static function setConnection($alias)
    {
        self::$connection = $alias;
    }

    /**
     * Method to retrieve the name of the current instance of connection
     * 
     * @access public static
     * @return string
     */
    public static function getConnection()
    {
        return self::$connection;
    }

    /**
     * Method for defining the type of communication with the database
     * The default connection is not persistent
     * 
     * @access public static
     * @param boolean $persistent Sets a persistent connection
     */
    public static function setPersistent($persistent = false)
    {
        self::$persistent = $persistent;
    }

    /**
     * Method to capture the error information of an Exception
     * 
     * @access private static
     * @param \PDOException $e Gets the message from the exception thrown
     * @param boolean $debug Enables the display of the captured values
     * @return array
     */
    private static function getErrorInfo(\PDOException $e, $debug = false)
    {
        if (defined(static::PDO4YOU_WEBMASTER)) {
            self::fireAlert(self::$exception['critical-error'], $e);
        }

        $info = null;
        $errorInfo = null;
        $message = $e->getMessage();

        preg_match('~SQLSTATE[[]([[:alnum:]]{1,})[]]:?\s[[]?([[:digit:]]{1,})?[]]?\s?(.+)~', $message, $errorInfo);

        $info['state'] = isset($errorInfo[1]) ? $errorInfo[1] : null;
        $info['code'] = isset($errorInfo[2]) ? $errorInfo[2] : null;
        $info['message'] = isset($errorInfo[3]) ? $errorInfo[3] : null;

        if ($debug) {
            echo '<pre>', print_r($info), '</pre>';
        }

        return $info;
    }

    /**
     * Method to retrieve the name of the current driver
     * 
     * @access public static
     * @return string
     */
    public static function getDriver()
    {
        return self::$instance->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Method to display details about the target server's database connected
     * 
     * @access public static
     * @param void
     * @return void
     */
    public static function showServerInfo()
    {
        try {
            if (self::$instance instanceof \PDO) {
                $driver = self::getDriver();

                $info = ($driver == 'sqlite' || $driver == 'mssql') ? 'not available' : self::$instance->getAttribute(\PDO::ATTR_SERVER_INFO);
                echo '<h7>Server Information - ', is_array($info) ? implode(', ', $info) : $info, '</h7>';
            } else {
                throw new \PDOException(self::$exception['no-instance']);
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * Method to display the PDO drivers installed and supported by the server
     * 
     * @access public static 
     * @param void
     * @return void
     */
    public static function showAvailableDrivers()
    {
        try {
            if (self::$instance instanceof \PDO) {
                $info = self::$instance->getAvailableDrivers();
                echo '<h7>Available Drivers: ', implode(', ', $info), '</h7>';
            } else {
                throw new \PDOException(self::$exception['no-instance']);
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * PDO4You Style
     * 
     * @access public static
     * @param void
     * @return void
     */
    public static function css()
    {
        $style = '<style type="text/css">';
        $style.= 'body,.code    { background:#FAFAFA; font:normal 12px/1.7em Bitstream Vera Sans Mono,Courier New,Monospace; margin:0; padding:0; }';
        $style.= '#pdo4you h7   { display:block; color:#FFF; background:#000; font-size:12px; margin:0; padding:2px 5px; }';
        $style.= '.pdo4you      { margin:8px; padding:0; }';
        $style.= '.code         { font:inherit; background:#EFEFEF; border:solid 1px #DDD; border-right-color:#BBB; border-bottom:none; margin:10px 10px 0 10px; overflow:auto; }';
        $style.= '.trace,.debug { background:#FFF; border:solid 1px #BBB; border-left-color:#DDD; border-top:none; margin:0 10px 15px 10px; }';
        $style.= '.trace div    { clear:both; }';
        $style.= '.debug        { padding:5px; }';
        $style.= '.title        { padding-left:6px; font-weight:bold; }';
        $style.= '.title span   { font-weight:normal; }';
        $style.= '.number       { color:#AAA; background:#EFEFEF; min-width:40px; padding:0 5px; margin-right:5px; float:left; text-align:right; cursor:default; }';
        $style.= '.highlight    { background:#FFC; }';
        $style.= '</style>';

        print $style;
    }

    /**
     * Method to display the stack trace of an error Exception
     * 
     * @access private static
     * @param \PDOException $e Gets the error stack generated by the exception
     * @param boolean $show Enables the display of the error stack
     * @return void
     */
    private static function stackTrace(\PDOException $e, $show = true)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $jarr['timer'] = '15000';
            $jarr['status'] = 'no';
            $jarr['info']['stack'][$i = 0] = '<strong>Exception:</strong> ' . $e->getMessage() . '<br />';
            foreach ($e->getTrace() as $t) {
                $jarr['info']['stack'][$i] = '#' . $i++ . ' ' . basename($t['file']) . ':' . $t['line'];
            }

            $json_stack = json_encode($jarr, true);

            exit($json_stack);
        } else {
            if (defined('PHPUnit_MAIN_METHOD')) {
                return;
            }

            if (static::PDO4YOU_FIREDEBUG == FALSE) {
                return;
            }

            self::css();

            if (defined(static::PDO4YOU_WEBMASTER)) {
                self::fireAlert(self::$exception['critical-error'], $e);
            }

            $count = 0;
            $stack = '<div class="pdo4you">';
            $stack.= '<strong>&nbsp;Exception:</strong> ' . $e->getMessage() . '<br />';
            if ($show) {
                foreach ($e->getTrace() as $t) {
                    $stack.= '<div class="code title">#' . $count++ . ' <span>' . $t['file'] . ':' . $t['line'] . '</span></div><div class="code trace">' . self::highlightSource($t['file'], $t['line']) . '</div>';
                }
            }
            $stack.= '</div>';

            exit($stack);
        }
    }

    /**
     * Method to highlight the syntax of a code
     * 
     * @access private static
     * @param string $fileName Filename
     * @param string $lineNumber Sets the highlighted row
     * @param string $showLines Sets the number of rows to display
     * @return string
     * @author Marcus Welz
     */
    private static function highlightSource($fileName, $lineNumber, $showLines = 5)
    {
        $offset = max(0, $lineNumber - ceil($showLines / 2));
        $lines = file_get_contents($fileName);
        $lines = highlight_string($lines, true);
        $lines = array_slice(explode('<br />', $lines), $offset, $showLines);

        $count = count($lines);
        for ($i = $count; $i < $showLines; $i++) {
            array_push($lines, '&nbsp;');
        }

        $trace = null;
        foreach ($lines as $line) {
            $offset++;
            $trace.= '<div' . ($offset == $lineNumber ? ' class="highlight"' : '') . '><span class="number">' . sprintf('%4d', $offset) . '</span>' . $line . '</div>';
        }

        return $trace;
    }

    /**
     * Method to query records in the database
     * 
     * @access private static
     * @param string $query SQL query
     * @param string $type Return type of the query
     * @param string $use Pseudonym of a connection instance
     * @param boolean $count OPTIONAL Counts the number of rows affected
     * @return mixed
     * @throws \PDOException
     */
    private static function selectRecords($query, $type, $use, $count = true)
    {
        try {
            if (is_null($query)) {
                throw new \PDOException(self::$exception['no-argument-sql']);
            }

            if (!is_null($use)) {
                self::setInstance($use);
            }

            $result = null;
            $pdo = self::$instance;
            if (!$pdo instanceof \PDO) {
                throw new \PDOException(self::$exception['no-instance']);
            } else {
                if (Pagination::getPaging() == true) {
                    $pre = $pdo->prepare($query);
                    $pre->execute();
                    $result = $pre->fetchAll(\PDO::FETCH_ASSOC);

                    $query = Pagination::buildQuery($query, $result);
                }

                $pre = $pdo->prepare($query);
                if (!is_object($pre)) {
                    return;
                } else {
                    $pre->execute();
                }

                switch ($type) {
                    case 'num': $result = $pre->fetchAll(\PDO::FETCH_NUM);
                        break;
                    case 'obj': $result = $pre->fetchAll(\PDO::FETCH_OBJ);
                        break;
                    case 'all': $result = $pre->fetchAll(\PDO::FETCH_BOTH);
                        break;
                    default: $result = $pre->fetchAll(\PDO::FETCH_ASSOC);
                }

                if ($count) {
                    self::$rowCount = count($result);
                }
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }

        $pdo = null;

        return $result;
    }

    /**
     * Method referring to the fetchAll(PDO::FETCH_NUM)
     * 
     * @access public static
     * @param string $sql Instruction SQL of query of records
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return array Returns an array indexed by column number
     */
    public static function selectNum($sql, $use = null)
    {
        return self::selectRecords($sql, 'num', $use);
    }

    /**
     * Method referring to the fetchAll(PDO::FETCH_OBJ)
     * 
     * @access public static
     * @param string $sql Instruction SQL of query of records
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return object Returns an object with column names as properties
     */
    public static function selectObj($sql, $use = null)
    {
        return self::selectRecords($sql, 'obj', $use);
    }

    /**
     * Method referring to the fetchAll(PDO::FETCH_BOTH)
     * 
     * @access public static
     * @param string $sql Instruction SQL of query of records
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return array Returns an array indexed both by the name as the column number
     */
    public static function selectAll($sql, $use = null)
    {
        return self::selectRecords($sql, 'all', $use);
    }

    /**
     * Method referring to the fetchAll(PDO::FETCH_ASSOC)
     * 
     * @access public static
     * @param string $sql Instruction SQL of query of records
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return array Returns an array indexed by column name
     */
    public static function select($sql, $use = null)
    {
        return self::selectRecords($sql, null, $use);
    }

    /**
     * Method for manipulation of records in the database
     * 
     * @access private static
     * @param string $jarr SQL statement in JSON/ARRAY format
     * @param string $type Type of operation in the database
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return array Returns an array with the number of rows affected by the operation
     * @throws \PDOException
     */
    private static function executeQuery($jarr, $type, $use)
    {
        $total = null;

        try {
            if (is_null($jarr)) {
                throw new \PDOException(self::$exception['no-instruction']);
            }

            if (!is_null($use)) {
                self::setInstance($use);
            }

            $pdo = self::$instance;
            if (!$pdo instanceof \PDO) {
                throw new \PDOException(self::$exception['no-instance']);
            } else {
                $pdo->beginTransaction();

                try {
                    $jarr = is_array($jarr) ? $jarr : self::parseJSON($jarr);

                    if ($type == 'insert') {
                        foreach ($jarr['query'] as $field) {
                            $sql = 'INSERT INTO ' . $field['table'] . ' (';
                            foreach ($field['values'] as $key => $val) {
                                $sql.= ', ' . $key;
                            }
                            $sql = preg_replace('/, /', '', $sql, 1);
                            $sql.= ') VALUES (';
                            foreach ($field['values'] as $key => $val) {
                                $sql.= ', ?';
                            }
                            $sql.= ')';
                            $sql = preg_replace('/\(, /', '(', $sql, 1);

                            $pre = $pdo->prepare($sql);
                            $k = 1;
                            foreach ($field['values'] as $key => $val) {
                                $pre->bindValue($k++, $val);
                            }

                            $pre->execute();
                            $total[] = $pre->rowCount();
                        }
                    }

                    if ($type == 'update') {
                        foreach ($jarr['query'] as $index => $field) {
                            $sql = 'UPDATE ' . $field['table'] . ' SET ';
                            foreach ($field['values'] as $key => $val) {
                                $sql.= ', ' . $key . ' = ?';
                            }
                            $sql = preg_replace('/, /', '', $sql, 1);
                            $sql.= ' WHERE ';
                            foreach ($field['where'] as $key => $val) {
                                $sql.= ' AND ' . $key . ' = ?';
                            }
                            $sql = preg_replace('/ AND /', '', $sql, 1);

                            $pre = $pdo->prepare($sql);
                            $k = 1;
                            foreach ($field['values'] as $key => $val) {
                                $pre->bindValue($k++, $val);
                            }
                            $j = $k;
                            foreach ($field['where'] as $key => $val) {
                                $pre->bindValue($j++, $val);
                            }

                            $pre->execute();
                            $total[] = $pre->rowCount();
                        }
                    }

                    if ($type == 'delete') {
                        foreach ($jarr['query'] as $index => $field) {
                            $sql = 'DELETE FROM ' . $field['table'] . ' WHERE ';
                            foreach ($field['where'] as $key => $val) {
                                $sql.= ' AND ' . $key . ' = ?';
                            }
                            $sql = preg_replace('/ AND /', '', $sql, 1);

                            $pre = $pdo->prepare($sql);
                            $k = 1;
                            foreach ($field['where'] as $key => $val) {
                                $pre->bindValue($k++, $val);
                            }

                            $pre->execute();
                            $total[] = $pre->rowCount();
                        }
                    }

                    self::$rowCount = $total;

                    $pdo->commit();
                } catch (\PDOException $e) {
                    $pdo->rollback();

                    throw $e;
                }
            }
        } catch (\PDOException $e) {
            self::getErrorInfo($e);
            self::stackTrace($e);
        }

        $pdo = null;

        return $total;
    }

    /**
     * Method to execute a statement in the database
     * 
     * @access public static
     * @param string $json SQL statement in JSON format
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return array Returns an array with the number of rows affected by type of operation
     * @throws \PDOException
     */
    public static function execute($json, $use = null)
    {
        // Finds a word that is at the beginning, in quotation marks or not
        $match = null;
        preg_match('~["]?([[:alnum:]]+)["]?[\s\n\r\t]{0,}?:~', $json, $match);

        try {
            // Checks the word if found, is among the allowed commands for execution
            $command = $match[1];
            if (!in_array($command, array('insert', 'update', 'delete', 'query'))) {
                throw new \PDOException(self::$exception['not-implemented'] . ' PDO4You::' . $command . '()');
            } else {
                return self::executeQuery($json, $command, $use);
            }
        } catch (\PDOException $e) {
            self::stackTrace($e, false);
        }
    }

    /**
     * Method to insert a new record in the database
     * 
     * @access public static
     * @param string $jarr SQL insertion in JSON/ARRAY format
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return array Returns an array with the number of rows affected by insert operation
     */
    public static function insert($jarr, $use = null)
    {
        return self::executeQuery($jarr, 'insert', $use);
    }

    /**
     * Method to update a record in the database
     * 
     * @access public static
     * @param string $jarr SQL update in JSON/ARRAY format
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return array Returns an array with the number of rows affected by update operation
     */
    public static function update($jarr, $use = null)
    {
        return self::executeQuery($jarr, 'update', $use);
    }

    /**
     * Method to delete a record in the database
     * 
     * @access public static
     * @param string $jarr SQL exclusion in JSON/ARRAY format
     * @param string $use OPTIONAL Name of the database defined as a new connection instance
     * @return array Returns an array with the number of rows affected by delete operation
     */
    public static function delete($jarr, $use = null)
    {
        return self::executeQuery($jarr, 'delete', $use);
    }

    /**
     * Method that returns the ID of the last inserted row or sequence value
     * Database such as MS SQL Server, PostgreSQL, among others, they make use variable sequence
     * 
     * @access public static
     * @param string $sequence Name of the variable sequence requested for some database
     * @return array
     * @throws \PDOException
     */
    public static function lastId($sequence = null)
    {
        try {
            $driver = self::getDriver();

            switch ($driver) {
                case 'mysql':
                case 'cubrid': $sql = "SELECT LAST_INSERT_ID() AS lastId;";
                    break;
                case 'sqlite': $sql = "SELECT LAST_INSERT_ROWID() AS lastId;";
                    break;
                case 'pgsql': $sql = "SELECT " . ($sequence ? "CURRVAL('" . $sequence . "')" : "LASTVAL()") . " AS lastId;";
                    break;
                case 'mssql':
                case 'sqlsrv': $sql = "SELECT " . ($sequence ? "IDENT_CURRENT('" . $sequence . "')" : "@@IDENTITY") . " AS lastId;";
                    break;
                #case 'oracle': $sql = "SELECT " . $sequence . ".CURRVAL AS lastId FROM DUAL;";
                case 'oracle': $sql = "SELECT last_number AS lastId FROM user_sequences WHERE sequence_name = '" . $sequence . "';";
                    break;
                default:
                    throw new \PDOException(self::$exception['not-implemented'] . ' PDO4You::lastId()');
            }

            self::$lastId = self::selectRecords($sql, null, null, false);

            return self::$lastId[0]['lastid'];
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * Method that returns the number of rows affected by the last CRUD (INSERT, SELECT, UPDATE, or DELETE)
     * 
     * @access public static
     * @param void
     * @return string
     */
    public static function rowCount()
    {
        $count = is_array(self::$rowCount) ? self::countWhere(self::$rowCount, '>', 0) : self::$rowCount;

        return $count;
    }

    /**
     * Method that converts a string in JSON format for Array
     * 
     * @access private static
     * @param string $json String in JSON notation format
     * @return array
     * @throws \PDOException
     */
    private static function parseJSON($json)
    {
        try {
            // Format JSON
            $json = '{' . $json . '}';

            // Finds a word that is at the beginning, in quotation marks or not and replaces
            $match = null;
            preg_match('~["]?([[:alnum:]]+)["]?[\s\n\r\t]{0,}?:~', $json, $match);
            $command = $match[1];
            if ($command != 'query') {
                $json = preg_replace('~' . $command . '~', 'query', $json, 1);
            }

            // Converts the encoding
            $json = mb_detect_encoding($json, 'UTF-8', true) ? $json : utf8_encode($json);

            // Replaces the excess whitespace and line breaks
            $json = preg_replace(array('~\s+~', '~[\r\n]+~'), array(' ', ''), $json);

            // Fixes whitespace bug
            $json = preg_replace('~(}[\s]?,[\s]?{)~', '},{', $json);

            // Formats the JSON string
            $json = preg_replace('~\s?(,?[{,])\s*([^"]+?)\s*:\s?~', '$1"$2":', $json);

            // Decoded array
            $jarr = json_decode($json, true);

            if (version_compare(PHP_VERSION, '5.3.5') >= 0) {
                switch (json_last_error()) {
                    case JSON_ERROR_DEPTH: $json_error = self::$exception['json-error-depth'];
                        break;
                    case JSON_ERROR_STATE_MISMATCH: $json_error = self::$exception['json-error-state-mismatch'];
                        break;
                    case JSON_ERROR_CTRL_CHAR: $json_error = self::$exception['json-error-ctrl-char'];
                        break;
                    case JSON_ERROR_SYNTAX: $json_error = self::$exception['json-error-syntax'];
                        break;
                }
            } else {
                $json_error = self::$exception['json-error-syntax'];
            }

            if (is_null($jarr)) {
                throw new \PDOException($json_error);
            }

            return $jarr;
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * Triggers a warning via email to the system administrator
     * 
     * @access public static
     * @param string $text Error Message
     * @param object $error Object of diagnostic of the errors
     * @return void
     */
    public static function fireAlert($text, $error)
    {
        $head = 'MIME-Version: 1.1' . PHP_EOL;
        $head.= 'Content-type: text/html; charset=utf-8' . PHP_EOL;
        $head.= 'From: Automatic Alert <firealert@noreply.com>' . PHP_EOL;
        $head.= 'Return-Path: Automatic Alert <firealert@noreply.com>' . PHP_EOL;
        $body = 'Diagnostic alert:<br /><br /><b>' . $error->getMessage() . '</b><br />' . $error->getFile() . ' : ' . $error->getLine();

        if (static::PDO4YOU_FIREALERT) {
            @mail(static::PDO4YOU_WEBMASTER, $text, $body, $head);
        }
    }

    /**
     * Interprets an INI file with heritage section
     * 
     * @access private static
     * @param string $filename Filename
     * @return array
     * @link https://gist.github.com/4217717
     */
    private static function parse_ini_file_advanced($filename)
    {
        $nArr = array();
        $oArr = parse_ini_file($filename, true);

        if (is_array($oArr)) {
            foreach ($oArr as $k => $v) {
                $k = preg_split('~[:]~', preg_replace('~[\s]{1,}~', null, $k));
                $t = &$nArr;
                foreach ($k as $x) {
                    $t = &$t[$x];
                }
                $t = $v;
            }
        }

        return $nArr;
    }

    /**
     * Returns the sum of occurrences, in an array of a given condition satisfied
     * 
     * @access private static
     * @param mixed $value The value or array to be evaluated
     * @param string $operator Operator of evaluation
     * @param string $conditional Conditional assignment
     * @return integer
     * @link https://gist.github.com/3100679
     */
    private static function countWhere($value = 1, $operator = '==', $conditional = 1)
    {
        $array = is_array($value) ? $value : (array) $value;
        $operator = !in_array($operator, array('<', '>', '<=', '>=', '==', '!=')) ? '==' : $operator;

        $i = 0;
        foreach ($array as $current) {
            $match = null;

            eval('$match = (bool)("' . $current . '"' . $operator . '"' . $conditional . '");');

            $i = $match ? ++$i : $i;
        }

        return $i;
    }

    /**
     * Removes the style markup in html tags, derived from a text editor
     * 
     * @access public static
     * @param string $value The input string
     * @return string
     * @link https://gist.github.com/3078188
     */
    public static function clearStyle($value)
    {
        $value = preg_replace("~<(a|ol|ul|li|h[1-6r]|d[dlt]|em|p|i|b|s|strong|span|div|table|t[dhr])\s?(style.*)?/>~i", "<$1>", $value);

        return $value;
    }

    /**
     * As the builder, we make __clone private to prevent cloning instance of the class
     * 
     * @access final private
     */
    final private function __clone()
    {
        
    }

    public static function exec($query)
    {
        try {
            if (!self::$instance instanceof \PDO) {
                throw new \PDOException(self::$exception['no-instance']);
            }

            return self::$instance->exec($query);
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function query($query)
    {
        try {
            if (!self::$instance instanceof \PDO) {
                throw new \PDOException(self::$exception['no-instance']);
            }

            if (!self::$instance->query($query)) {
                throw new \PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function lastInsertId($name)
    {
        try {
            if (!self::$instance instanceof \PDO) {
                throw new \PDOException(self::$exception['no-instance']);
            }

            if (!self::$instance->lastInsertId($name)) {
                throw new \PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function beginTransaction()
    {
        try {
            if (!self::$instance instanceof \PDO) {
                throw new \PDOException(self::$exception['no-instance']);
            }

            if (!self::$instance->beginTransaction()) {
                throw new \PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function commit()
    {
        try {
            if (!self::$instance instanceof \PDO) {
                throw new \PDOException(self::$exception['no-instance']);
            }

            if (!self::$instance->commit()) {
                throw new \PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function rollBack()
    {
        try {
            if (!self::$instance instanceof \PDO) {
                throw new \PDOException(self::$exception['no-instance']);
            }

            if (!self::$instance->rollBack()) {
                throw new \PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
            }
        } catch (\PDOException $e) {
            self::stackTrace($e);
        }
    }

}