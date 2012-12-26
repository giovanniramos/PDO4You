<?php

/**
 * Esta classe implementa o padrão de projeto Singleton para conexão de base de dados, usando a extensão PDO (PHP Data Objects)
 * 
 * @category PDO
 * @package PDO4You
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2012, Giovanni Ramos
 * @since 2010-09-07
 * @version 2.7
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/giovanniramos/PDO4You
 * 
 * */
class PDO4You
{
    /**
     * Armazena o nome da máquina na qual o servidor da base de dados reside
     * 
     * @access private static
     * @var string
     * 
     * */
    private static $datahost;

    /**
     * Armazena o nome da porta na qual o servidor está executando
     * 
     * @access private static
     * @var string
     * 
     * */
    private static $dataport;

    /**
     * Armazena o nome da instância corrente de conexão
     * 
     * @access private static
     * @var string
     * 
     * */
    private static $connection;

    /**
     * Armazena uma instância do objeto PDO de conexão
     * 
     * @access private static
     * @var object
     * 
     * */
    private static $instance;

    /**
     * Armazena instâncias do objeto PDO de conexão
     * 
     * @access private static
     * @var array
     * 
     * */
    private static $handle = array();

    /**
     * Armazena a definição de conexão persistente
     * 
     * @access private static
     * @var boolean
     * 
     * */
    private static $persistent = false;

    /**
     * Armazena o ID do último registro inserido ou o valor de seqüência
     * 
     * @access private
     * @var string
     * 
     * */
    private static $lastId;

    /**
     * Armazena o total de linhas afetadas na última operação CRUD
     * 
     * @access private
     * @var string
     * 
     * */
    private static $rowCount;

    /**
     * Armazena as mensagens de Exception lançadas
     * 
     * @access private
     * @var array
     * 
     * */
    private static $exception = array(
        'code-1044' => 'Access denied for user: \'%1$s\'',
        'code-1045' => 'Failed communication with the database using: \'%1$s\'@\'%2$s\'',
        'code-2002' => 'No connection could be made because the destination machine actively refused. This host is not known.',
        'code-2005' => 'No communication with the host provided. Check your settings.',
        'unrecognized' => 'The Adapter/DSN Instance was not recognized.',
        'no-database' => 'Database unknown. Check your settings.',
        'no-instance' => 'No instance of object PDO4You available. Unable to access the methods.',
        'no-argument-sql' => 'The SQL argument is missing.',
        'no-instruction-json' => 'The SQL statement is missing in JSON format.',
        'not-implemented' => 'Method not implemented.',
        'critical-error' => 'Critical error detected in the system.',
        'json-error-depth' => 'Maximum stack depth exceeded.',
        'json-error-state-mismatch' => 'Mismatch or arithmetic operation modes impossible to be represented.',
        'json-error-ctrl-char' => 'Attribute control unexpected was found.',
        'json-error-syntax' => 'The query is poorly formatted JSON provided'
    );

    /**
     * O construtor é definido como privado, impedindo a instância direta da classe
     * 
     * @access private
     * 
     * */
    private function PDO4You()
    {
        
    }

    /**
     * Método Singleton de conexão
     * 
     * @access private static
     * @param string $alias Nome da instância de conexão
     * @param string $driver Driver DSN de conexão
     * @param string $user Usuário da base de dados
     * @param string $pass Senha da base de dados
     * @param string $option Configuração do driver de conexão
     * @return void
     * @throws PDOException Dispara uma exceção em caso de falhas na conexão
     * 
     * */
    private static function singleton($alias, $driver, $user, $pass, $option)
    {
        try {
            try {
                $instance = @ new PDO($driver, $user, $pass, $option);
                $instance->setAttribute(PDO::ATTR_ERRMODE, ($_SERVER['SERVER_ADDR'] == '127.0.0.1') ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT);

                self::setHandle($alias, $instance);
                self::setInstance($alias);
            } catch (PDOException $e) {
                $error = self::getErrorInfo($e);

                if ($e->getMessage() == 'could not find driver' || $e->getMessage() == 'invalid data source name')
                    throw new PDOException(self::$exception['unrecognized']);
                elseif ($error['code'] == '2005')
                    throw new PDOException(self::$exception['code-2005']);
                elseif ($error['code'] == '2002')
                    throw new PDOException(self::$exception['code-2002']);
                elseif ($error['code'] == '1044')
                    throw new PDOException(sprintf(self::$exception['code-1044'], $user));
                elseif ($error['code'] == '1045')
                    throw new PDOException(sprintf(self::$exception['code-1045'], $user, $pass));
                else
                    throw $e;
            }
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * Método para definir uma instância de conexão
     * 
     * @access public static
     * @param string $alias Nome de uma instância de conexão
     * @return void
     * 
     * */
    public static function setInstance($alias)
    {
        self::$instance = self::getHandle($alias == null ? 'default' : $alias);
    }

    /**
     * Método que obtém uma única instância da base de dados por conexão
     * 
     * @access public static
     * @param string $alias Pseudônimo que será usado como ponteiro de uma instância de conexão pré-estabelecida
     * @param string $type Tipo de conexão se estiver usando a "Configuração Inicial", ou um "DSN completo"
     * @param string $user Usuário da base de dados
     * @param string $pass Senha da base de dados
     * @param string $option Configuração adicional do driver
     * @return object
     * @throws Exception Dispara uma exceção em caso de falhas na conexão
     * 
     * */
    public static function getInstance($alias = 'default', $type = null, $user = null, $pass = null, Array $option = null)
    {
        try {
            try {
                if (!array_key_exists($alias, self::$handle)):
                    if ($alias == 'default'):
                        $dir = dirname(__FILE__);
                        $file = $dir . '\settings.ini';

                        if (file_exists($file)):
                            if (is_readable($file)):
                                $datafile = parse_ini_file_advanced($file);

                                if (isset($datafile['adapter'])):
                                    $part = preg_split('~[.]~', preg_replace('~[\s]{1,}~', null, ADAPTER));
                                    $data = count($part) == 2 ? @$datafile['adapter'][$part[0]][$part[1]] : @$datafile['adapter'][$part[0]];

                                    $type = isset($data['DATA_TYPE']) ? $data['DATA_TYPE'] : null;
                                    $host = isset($data['DATA_HOST']) ? $data['DATA_HOST'] : null;
                                    $port = isset($data['DATA_PORT']) ? $data['DATA_PORT'] : null;
                                    $user = isset($data['DATA_USER']) ? $data['DATA_USER'] : null;
                                    $pass = isset($data['DATA_PASS']) ? $data['DATA_PASS'] : null;
                                    $base = isset($data['DATA_BASE']) ? $data['DATA_BASE'] : null;
                                else:
                                    exit('The settings for existing databases, were not configured in the <strong>settings.ini</strong>.');
                                endif;
                            else:
                                exit('The <strong>settings.ini</strong> file cannot be read.');
                            endif;
                        else:
                            exit('The <strong>settings.ini</strong> file could not be found in directory:<br /> ' . $dir . '\\');
                        endif;
                    endif;

                    $type = strtolower($type);
                    switch ($type):
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
                        case 'oracle': $driver = 'oci:' . $base;
                            break;
                        default: $driver = $type;
                    endswitch;

                    $option = !is_null($option) ? $option : array(PDO::ATTR_PERSISTENT => self::$persistent, PDO::ATTR_CASE => PDO::CASE_LOWER);

                    self::singleton($alias, $driver, $user, $pass, $option);
                endif;
            } catch (PDOException $e) {
                $error = self::getErrorInfo($e);

                if ($error['state'] == '42000')
                    throw new PDOException(self::$exception['no-database']);
                else
                    throw $e;
            }
        } catch (PDOException $e) {
            self::stackTrace($e);
        }

        return self::$instance;
    }

    /**
     * Método para atribuir uma nova instância do objeto PDO de conexão
     *
     * @param string $alias Pseudônimo para identificar a instância de conexão
     * @param PDO $instance Objeto PDO de conexão
     * @return void
     * 
     */
    private static function setHandle($alias, PDO $instance)
    {
        self::$handle[$alias] = $instance;
    }

    /**
     * Método para retornar um objeto PDO de conexão
     *
     * @param string $alias Pseudônimo de uma instância de conexão
     * @return object
     * 
     */
    private static function getHandle($alias)
    {
        self::setConnection($alias);

        return self::$handle[$alias];
    }

    /**
     * Método para definir o nome do servidor
     * 
     * @access private static
     * @param string $host Nome do servidor
     * @return void
     * 
     * */
    private static function setDatahost($host)
    {
        self::$datahost = $host;
    }

    /**
     * Método para recuperar o nome do servidor
     * 
     * @access public static
     * @param void
     * @return string
     * 
     * */
    public static function getDatahost()
    {
        return self::$datahost;
    }

    /**
     * Método para definir o número da porta do servidor
     * 
     * @access private static
     * @param string $port Número da porta
     * @return void
     * 
     * */
    private static function setDataport($port)
    {
        self::$dataport = $port;
    }

    /**
     * Método para recuperar o número da porta do servidor
     * 
     * @access public static
     * @param void
     * @return string
     * 
     * */
    public static function getDataport()
    {
        return self::$dataport;
    }

    /**
     * Método para definir qual a instância corrente de conexão
     * 
     * @access private static
     * @param string $alias Pseudônimo da instância de conexão
     * @return void
     * 
     * */
    private static function setConnection($alias)
    {
        self::$connection = $alias;
    }

    /**
     * Método para recuperar o nome da instância corrente de conexão
     * 
     * @access public static
     * @param void
     * @return string
     * 
     * */
    public static function getConnection()
    {
        return self::$connection;
    }

    /**
     * Método para definir o tipo de comunicação com a base de dados
     * O padrão de conexão é não-persistente
     * 
     * @access public static
     * @param boolean $persistent Define uma conexão persistente
     * @return void
     * 
     * */
    public static function setPersistent($persistent)
    {
        self::$persistent = $persistent;
    }

    /**
     * Método para capturar as informações de erro de uma Exception
     * 
     * @access public static
     * @param Exception $e Obtém a mensagem da exceção lançada
     * @param boolean $debug Habilita a exibição dos valores capturados
     * @return array
     * 
     * */
    public static function getErrorInfo(Exception $e, $debug = false)
    {
        if (defined(WEBMASTER))
            self::fireAlert(self::$exception['critical-error'], $e);

        $info = null;
        $errorInfo = null;
        $message = $e->getMessage();

        preg_match('~SQLSTATE[[]([[:alnum:]]{1,})[]]:?\s[[]?([[:digit:]]{1,})?[]]?\s?(.+)~', $message, $errorInfo);
        $info['state'] = isset($errorInfo[1]) ? $errorInfo[1] : null;
        $info['code'] = isset($errorInfo[2]) ? $errorInfo[2] : null;
        $info['message'] = isset($errorInfo[3]) ? $errorInfo[3] : null;

        if ($debug)
            echo '<pre>', print_r($info), '</pre>';

        return $info;
    }

    /**
     * Método para recuperar o nome do driver corrente
     * 
     * @access public static
     * @param void
     * @return string
     * 
     * */
    public static function getDriver()
    {
        return self::$instance->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Método para exibir detalhes sobre a meta do servidor da base de dados conectada
     * 
     * @access public static
     * @param void
     * @return void
     * 
     * */
    public static function getServerInfo()
    {
        try {
            if (self::$instance instanceof PDO):
                self::setStyle();

                $driver = self::getDriver();

                $info = ($driver == 'mssql') ? 'not available' : self::$instance->getAttribute(PDO::ATTR_SERVER_INFO);
                echo '<h7>Server Information - ', is_array($info) ? implode(', ', $info) : $info, '</h7>';
            else:
                throw new PDOException(self::$exception['no-instance']);
            endif;
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * Método para exibir os drivers PDO instalados e suportados pelo servidor
     * 
     * @access public static 
     * @param void
     * @return void
     * 
     * */
    public static function getAvailableDrivers()
    {
        try {
            if (self::$instance instanceof PDO):
                self::setStyle();

                $info = self::$instance->getAvailableDrivers();
                echo '<h7>Available Drivers: ', implode(', ', $info), '</h7>';
            else:
                throw new PDOException(self::$exception['no-instance']);
            endif;
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * PDO4You Style
     * 
     * @access public static
     * @param void
     * @return void
     * 
     * */
    public static function setStyle()
    {
        $css = '<style type="text/css">';
        $css.= 'body, code    { background:#FAFAFA; font:normal 12px/1.7em Bitstream Vera Sans Mono,Courier New,Monospace; margin:0; padding:0; }';
        $css.= '#pdo4you h2   { display:block; color:#000; background:#FFF; font-size:20px; margin:0; padding:10px; border-bottom:solid 1px #999; }';
        $css.= '#pdo4you h7   { display:block; color:#FFF; background:#000; font-size:12px; margin:0; padding:2px 5px; }';
        $css.= '.pdo4you      { margin:8px; padding:0; }';
        $css.= 'code          { display:block; font:inherit; background:#EFEFEF; border:solid 1px #DDD; border-right-color:#BBB; border-bottom:none; margin:10px 10px 0 10px; overflow:auto; }';
        $css.= '.trace,.debug { background:#FFF; border:solid 1px #BBB; border-left-color:#DDD; border-top:none; margin:0 10px 15px 10px; }';
        $css.= '.debug        { padding:5px; }';
        $css.= '.number       { color:#AAA; background:#EFEFEF; min-width:40px; padding:0 5px; margin-right:5px; float:left; text-align:right; cursor:default; }';
        $css.= '.highlight    { background:#FFC; }';
        $css.= '</style>';

        print $css;
    }

    /**
     * Método para exibir o rastreamento da pilha de erros de uma Exception 
     * 
     * @access public static
     * @param Exception $e Obtém a pilha de erros gerada pela exceção
     * @param boolean $show Habilita a exibição da pilha de erros
     * @return void
     * 
     * */
    public static function stackTrace(Exception $e, $show = true)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $jarr['timer'] = '15000';
            $jarr['status'] = 'no';
            $jarr['info']['stack'][$i = 0] = '<strong>Exception:</strong> ' . $e->getMessage() . '<br />';
            foreach ($e->getTrace() as $t)
                $jarr['info']['stack'][$i] = '#' . $i++ . ' ' . basename($t['file']) . ':' . $t['line'];
            $json = json_encode($jarr, true);

            exit($json);
        } else {
            self::setStyle();

            if (!FIREDEBUG)
                return;

            if (defined(WEBMASTER))
                self::fireAlert(self::$exception['critical-error'], $e);

            $count = 0;
            $stack = '<div class="pdo4you">';
            $stack.= '<strong>Exception:</strong> ' . $e->getMessage() . '<br />';
            if ($show)
                foreach ($e->getTrace() as $t)
                    $stack.= '<code>&nbsp;<strong>#' . $count++ . '</strong> ' . $t['file'] . ':' . $t['line'] . '</code><code class="trace">' . self::highlightSource($t['file'], $t['line']) . '</code>';
            $stack.= '</div>';

            exit($stack);
        }
    }

    /**
     * Método para destacar a sintaxe de um código
     * 
     * @access public static
     * @param string $fileName Nome do arquivo
     * @param string $lineNumber Define a linha de destaque
     * @param string $showLines Define o número de linhas a serem exibidas
     * @return string
     * @author Marcus Welz
     * 
     * */
    public static function highlightSource($fileName, $lineNumber, $showLines = 5)
    {
        $offset = max(0, $lineNumber - ceil($showLines / 2));
        $lines = file_get_contents($fileName);
        $lines = highlight_string($lines, true);
        $lines = array_slice(explode('<br />', $lines), $offset, $showLines);
        $trace = null;

        foreach ($lines as $l):
            $offset++;
            $line = '<div class="number">' . sprintf('%4d', $offset) . '</div>' . $l . '<br />';
            $trace.= ($offset == $lineNumber) ? '<div class="highlight">' . $line . '</div>' : $line;
        endforeach;

        return $trace;
    }

    /**
     * Método para consulta de registros na base de dados
     * 
     * @access private static
     * @param string $query Instrução SQL de consulta
     * @param string $type Tipo de retorno da consulta
     * @param string $use Nome da base de dados instanciada
     * @param boolean $count Conta o número de linhas afetadas (opcional)
     * @return mixed
     * 
     * */
    private static function selectRecords($query, $type, $use = null, $count = true)
    {
        $total = null;

        try {
            if (is_null($query))
                throw new PDOException(self::$exception['no-argument-sql']);

            if (!is_null($use))
                self::setInstance($use);

            $pdo = self::$instance;
            if (!$pdo instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            else:
                $pre = $pdo->prepare($query);
                $pre->execute();

                switch ($type):
                    case 'num' : $result = $pre->fetchAll(PDO::FETCH_NUM);
                        break;
                    case 'obj' : $result = $pre->fetchAll(PDO::FETCH_OBJ);
                        break;
                    case 'all' : $result = $pre->fetchAll(PDO::FETCH_BOTH);
                        break;
                    default : $result = $pre->fetchAll(PDO::FETCH_ASSOC);
                endswitch;

                $total = $pre->rowCount();
                if ($count)
                    self::$rowCount = $total;
            endif;
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
        $pdo = null;

        return $result;
    }

    /**
     * Método referente ao fetchAll(PDO::FETCH_NUM)
     * 
     * @access public static
     * @param string $sql Instrução SQL de consulta de registros
     * @param object $use Nome da base de dados definida como nova instância de conexão (OPCIONAL)
     * @return array Retorna um array indexado pelo número da coluna
     * 
     * */
    public static function selectNum($sql, $use = null)
    {
        return self::selectRecords($sql, 'num', $use);
    }

    /**
     * Método referente ao fetchAll(PDO::FETCH_OBJ)
     * 
     * @access public static
     * @param string $sql Instrução SQL de consulta de registros
     * @param object $use Nome da base de dados definida como nova instância de conexão (OPCIONAL)
     * @return object Retorna um objeto com nomes de coluna como propriedades
     * 
     * */
    public static function selectObj($sql, $use = null)
    {
        return self::selectRecords($sql, 'obj', $use);
    }

    /**
     * Método referente ao fetchAll(PDO::FETCH_BOTH)
     * 
     * @access public static
     * @param string $sql Instrução SQL de consulta de registros
     * @param object $use Nome da base de dados definida como nova instância de conexão (OPCIONAL)
     * @return array Retorna um array indexado tanto pelo nome como pelo número da coluna
     * 
     * */
    public static function selectAll($sql, $use = null)
    {
        return self::selectRecords($sql, 'all', $use);
    }

    /**
     * Método referente ao fetch(PDO::FETCH_ASSOC)
     * 
     * @access public static
     * @param string $sql Instrução SQL de consulta de registros
     * @param object $use Nome da base de dados definida como nova instância de conexão (OPCIONAL)
     * @return array Retorna um array indexado pelo nome da coluna
     * 
     * */
    public static function select($sql, $use = null)
    {
        return self::selectRecords($sql, null, $use);
    }

    /**
     * Método para manipulação de registros na base de dados
     * 
     * @access private static
     * @param string $json Instrução SQL no formato JSON
     * @param string $type Tipo de operação na base de dados
     * @param string $use Nome da base de dados instanciada
     * @return array Retorna um array com o número de linhas afetadas por operação
     * 
     * */
    private static function executeQuery($json, $type, $use = null)
    {
        $total = null;

        try {
            if (is_null($json))
                throw new PDOException(self::$exception['no-instruction-json']);

            if (!is_null($use))
                self::setInstance($use);

            $pdo = self::$instance;
            if (!$pdo instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            else:
                $pdo->beginTransaction();

                try {
                    $jarr = self::parseJSON($json);

                    if ($type == 'insert'):
                        foreach ($jarr['query'] as $field):
                            $sql = 'INSERT INTO ' . $field['table'] . ' (';
                            foreach ($field['values'] as $key => $val)
                                $sql.= ', ' . $key;
                            $sql = preg_replace('/, /', '', $sql, 1);
                            $sql.= ') VALUES (';
                            foreach ($field['values'] as $key => $val)
                                $sql.= ', ?';
                            $sql.= ')';
                            $sql = preg_replace('/\(, /', '(', $sql, 1);

                            $pre = $pdo->prepare($sql);
                            $k = 1;
                            foreach ($field['values'] as $key => $val)
                                $pre->bindValue($k++, $val);

                            $pre->execute();
                            $total[] = $pre->rowCount();
                        endforeach;
                    endif;

                    if ($type == 'update'):
                        foreach ($jarr['query'] as $index => $field):
                            $sql = 'UPDATE ' . $field['table'] . ' SET ';
                            foreach ($field['values'] as $key => $val)
                                $sql.= ', ' . $key . ' = ?';
                            $sql = preg_replace('/, /', '', $sql, 1);
                            $sql.= ' WHERE ';
                            foreach ($field['where'] as $key => $val)
                                $sql.= ' AND ' . $key . ' = ?';
                            $sql = preg_replace('/ AND /', '', $sql, 1);

                            $pre = $pdo->prepare($sql);
                            $k = 1;
                            foreach ($field['values'] as $key => $val)
                                $pre->bindValue($k++, $val);
                            $j = $k;
                            foreach ($field['where'] as $key => $val)
                                $pre->bindValue($j++, $val);

                            $pre->execute();
                            $total[] = $pre->rowCount();
                        endforeach;
                    endif;

                    if ($type == 'delete'):
                        foreach ($jarr['query'] as $index => $field):
                            $sql = 'DELETE FROM ' . $field['table'] . ' WHERE ';
                            foreach ($field['where'] as $key => $val)
                                $sql.= ' AND ' . $key . ' = ?';
                            $sql = preg_replace('/ AND /', '', $sql, 1);

                            $pre = $pdo->prepare($sql);
                            $k = 1;
                            foreach ($field['where'] as $key => $val)
                                $pre->bindValue($k++, $val);

                            $pre->execute();
                            $total[] = $pre->rowCount();
                        endforeach;
                    endif;

                    self::$rowCount = $total;

                    $pdo->commit();
                } catch (PDOException $e) {
                    $pdo->rollback();

                    throw $e;
                }
            endif;
        } catch (PDOException $e) {
            self::getErrorInfo($e);
            self::stackTrace($e);
        }
        $pdo = null;

        return $total;
    }

    /**
     * Método para inserir um novo registro na base de dados
     * 
     * @access public static
     * @param string $json Instrução SQL de inserção, no formato JSON
     * @param string $use Nome da base de dados definida como nova instância de conexão (OPCIONAL)
     * @return array Retorna um array com o número de linhas afetadas por operação de inserção
     * 
     * */
    public static function insert($json, $use = null)
    {
        return self::executeQuery($json, 'insert', $use);
    }

    /**
     * Método para atualizar os dados de um registro
     * 
     * @access public static
     * @param string $json Instrução SQL de atualização, no formato JSON
     * @param string $use Nome da base de dados definida como nova instância de conexão (OPCIONAL)
     * @return array Retorna um array com o número de linhas afetadas por operação de atualização
     * 
     * */
    public static function update($json, $use = null)
    {
        return self::executeQuery($json, 'update', $use);
    }

    /**
     * Método para excluir um registro
     * 
     * @access public static
     * @param string $json Instrução SQL de exclusão, no formato JSON
     * @param string $use Nome da base de dados definida como nova instância de conexão (OPCIONAL)
     * @return array Retorna um array com o número de linhas afetadas por operação de exclusão
     * 
     * */
    public static function delete($json, $use = null)
    {
        return self::executeQuery($json, 'delete', $use);
    }

    /**
     * Método que retorna o ID do último registro inserido ou o valor de sequência
     * Base de dados como: MS SQL Server, PostgreSQL, entre outros, fazem uso da variável de sequência
     * Exemplos: users, users_id_seq, table_seq, (...)
     * 
     * @access public static
     * @param string $sequence Nome da variável de sequência solicitado em algumas base de dados
     * @return array
     * 
     * */
    public static function lastId($sequence = null)
    {
        try {
            $driver = self::getDriver();

            switch ($driver):
                case 'cubrid':
                case 'mysql': $sql = "SELECT LAST_INSERT_ID() AS lastId;";
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
                    throw new PDOException(self::$exception['not-implemented'] . ' PDO4You::lastId()');
            endswitch;

            self::$lastId = self::selectRecords($sql, null, null, false);

            return self::$lastId[0]['lastid'];
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * Método que retorna o número de linhas afetadas pelo último CRUD (INSERT, SELECT, UPDATE ou DELETE)
     * 
     * @access public static
     * @param void
     * @return string
     * 
     * */
    public static function rowCount()
    {
        $count = (is_array(self::$rowCount)) ? countWhere(self::$rowCount, '>', 0) : self::$rowCount;

        return $count;
    }

    /**
     * Método que converte uma string no formato JSON para Array 
     * 
     * @access private static
     * @param string $json String no formato de notação JSON
     * @return array
     * 
     * */
    private static function parseJSON($json)
    {
        try {
            $json = mb_detect_encoding($json, 'UTF-8', true) ? $json : utf8_encode($json);
            $json = preg_replace('~[\n\r\t]~', '', $json);
            $json = preg_replace('~(,?[{,])[[:space:]]*([^"]+?)[[:space:]]*:~', '$1"$2":', $json);
            $jarr = json_decode($json, true);

            if (version_compare(PHP_VERSION, '5.3.5') >= 0):
                switch (json_last_error()):
                    case JSON_ERROR_DEPTH: $json_error = self::$exception['json-error-depth'];
                        break;
                    case JSON_ERROR_STATE_MISMATCH: $json_error = self::$exception['json-error-state-mismatch'];
                        break;
                    case JSON_ERROR_CTRL_CHAR: $json_error = self::$exception['json-error-ctrl-char'];
                        break;
                    case JSON_ERROR_SYNTAX: $json_error = self::$exception['json-error-syntax'];
                        break;
                endswitch;
            else:
                $json_error = self::$exception['json-error-syntax'];
            endif;

            if (is_null($jarr))
                throw new PDOException($json_error);

            return $jarr;
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    /**
     * Método do MySQL para exibir as tabelas da base de dados
     * 
     * @access private static
     * @param void 
     * @return void
     * 
     * */
    private static function showMySqlTables()
    {
        self::setStyle();

        $tables = self::select("SHOW TABLES;");
        $index = array_keys($tables[0]);
        $database = preg_replace('~tables_in_~i', '', $index[0]);

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $database . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br />';
        foreach ($tables as $k1 => $v1):
            foreach ($v1 as $k2 => $v2):
                $desc = self::select("DESCRIBE " . $database . "." . $v2);

                $html.= '<code>&nbsp;<strong>Table</strong>: ' . $v2 . '</code>';
                $html.= '<code class="trace">';
                foreach ($desc as $k3 => $v3)
                    $html.= '<div class="number">&nbsp;</div> <span><i style="color:#00B;">' . $v3['field'] . "</i> - " . strtoupper($v3['type']) . '</span><br />';
                $html.= '</code>';
            endforeach;
        endforeach;
        $html.= '</div>';

        echo $html;
    }

    /**
     * Método do PostgreSQL para exibir as tabelas da base de dados
     * 
     * @access private static
     * @param string $schema Nome do esquema
     * @return void
     * 
     * */
    private static function showPgSqlTables($schema)
    {
        self::setStyle();

        $table_schema = !is_null($schema) ? "table_schema = '" . $schema . "'" : "table_schema NOT SIMILAR TO '(information_schema|pg_%)'";
        $tables = self::select("SELECT table_catalog, table_schema, table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND " . $table_schema . ";");
        $database = $tables[0]['table_catalog'];

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $database . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br />';
        foreach ($tables as $k1 => $v1):
            $desc = self::select("SELECT d.datname, n.nspname, a.attname AS field, t.typname AS type FROM pg_database d, pg_namespace n, pg_class c, pg_attribute a, pg_type t WHERE d.datname = '" . $v1['table_catalog'] . "' AND n.nspname = '" . $v1['table_schema'] . "' AND c.relname = '" . $v1['table_name'] . "' AND c.relnamespace = n.oid AND a.attnum > 0 AND not a.attisdropped AND a.attrelid = c.oid AND a.atttypid = t.oid ORDER BY a.attnum");

            $html.= '<code>&nbsp;<strong>Table</strong>: ' . $v1['table_schema'] . '.' . $v1['table_name'] . '</code>';
            $html.= '<code class="trace">';
            foreach ($desc as $k2 => $v2)
                $html.= '<div class="number">&nbsp;</div> <span><i style="color:#00B;">' . $v2['field'] . "</i> - " . strtoupper($v2['type']) . '</span><br />';
            $html.= '</code>';
        endforeach;
        $html.= '</div>';

        echo $html;
    }

    /**
     * Método do CUBRID para exibir as tabelas da base de dados
     * 
     * @access private static
     * @param void 
     * @return void
     * 
     * */
    private static function showCubridTables()
    {
        self::setStyle();

        $tables = self::select("SHOW TABLES;");
        $index = array_keys($tables[0]);
        $database = preg_replace('~tables_in_~i', '', $index[0]);

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $database . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br />';
        foreach ($tables as $k1 => $v1):
            foreach ($v1 as $k2 => $v2):
                $desc = self::select("SHOW COLUMNS IN " . $v2);

                $html.= '<code>&nbsp;<strong>Table</strong>: ' . $v2 . '</code>';
                $html.= '<code class="trace">';
                foreach ($desc as $k3 => $v3)
                    $html.= '<div class="number">&nbsp;</div> <span><i style="color:#00B;">' . $v3['Field'] . "</i> - " . strtoupper($v3['Type']) . '</span><br />';
                $html.= '</code>';
            endforeach;
        endforeach;
        $html.= '</div>';

        echo $html;
    }

    /**
     * Método do Microsoft SQL Server para as tabelas da base de dados
     * 
     * @access private static
     * @param string $schema Nome do esquema
     * @return void
     * 
     * */
    private static function showMsSqlTables($schema)
    {
        self::setStyle();

        $table_schema = !is_null($schema) ? "table_schema = '" . $schema . "'" : "table_schema IS NOT NULL";
        $tables = self::select("SELECT table_catalog, table_schema, table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND " . $table_schema . ";");
        $database = $tables[0]['table_catalog'];

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $database . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br />';
        foreach ($tables as $k1 => $v1):
            $desc = self::select("SELECT table_catalog, table_schema, table_name, column_name AS field, data_type AS type FROM information_schema.columns WHERE table_catalog = '" . $v1['table_catalog'] . "' AND table_name = '" . $v1['table_name'] . "';");

            $html.= '<code>&nbsp;<strong>Table</strong>: ' . $v1['table_schema'] . '.' . $v1['table_name'] . '</code>';
            $html.= '<code class="trace">';
            foreach ($desc as $k2 => $v2)
                $html.= '<div class="number">&nbsp;</div> <span><i style="color:#00B;">' . $v2['field'] . "</i> - " . strtoupper($v2['type']) . '</span><br />';
            $html.= '</code>';
        endforeach;
        $html.= '</div>';

        echo $html;
    }

    /**
     * Método que exibe e descreve as tabelas da base de dados
     * 
     * @access public static
     * @param string $schema Nome do esquema utilizado
     * @return void
     * 
     * */
    public static function showTables($schema = null)
    {
        try {
            $driver = self::getDriver();

            switch ($driver):
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
                    throw new PDOException(self::$exception['not-implemented'] . ' PDO4You::showTables()');
            endswitch;
        } catch (PDOException $e) {
            self::stackTrace($e, false);
        }
    }

    /**
     * Dispara um aviso via e-mail para o administrador do sistema
     * 
     * @access public static
     * @param string $text Mensagem de erro
     * @param object $error Objeto do diagnóstico de erros
     * @return void
     * 
     * */
    public static function fireAlert($text, $error)
    {
        $head = 'MIME-Version: 1.1' . PHP_EOL;
        $head.= 'Content-type: text/html; charset=utf-8' . PHP_EOL;
        $head.= 'From: Alerta automático <firealert@noreply.com>' . PHP_EOL;
        $head.= 'Return-Path: Alerta automático <firealert@noreply.com>' . PHP_EOL;
        $body = 'Diagnóstico do alerta:<br /><br /><b>' . $error->getMessage() . '</b><br />' . $error->getFile() . ' : ' . $error->getLine();

        if (FIREALERT)
            @mail(WEBMASTER, $text, $body, $head);
    }

    /**
     * Assim como o construtor, tornamos __clone privado para impedir a clonagem da instância da classe
     * 
     * @access private
     * @param void
     * @return void
     * 	 
     * */
    final private function __clone()
    {
        
    }

    public static function beginTransaction()
    {
        try {
            if (!self::$instance instanceof PDO)
                throw new PDOException(self::$exception['no-instance']);

            if (!self::$instance->beginTransaction())
                throw new PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function commit()
    {
        try {
            if (!self::$instance instanceof PDO)
                throw new PDOException(self::$exception['no-instance']);

            if (!self::$instance->commit())
                throw new PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function exec($query)
    {
        try {
            if (!self::$instance instanceof PDO)
                throw new PDOException(self::$exception['no-instance']);

            if (!self::$instance->exec($query))
                throw new PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
            else
                return self::$instance->exec($query);
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function query($query)
    {
        try {
            if (!self::$instance instanceof PDO)
                throw new PDOException(self::$exception['no-instance']);

            if (!self::$instance->query($query))
                throw new PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function rollBack()
    {
        try {
            if (!self::$instance instanceof PDO)
                throw new PDOException(self::$exception['no-instance']);

            if (!self::$instance->rollBack())
                throw new PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public static function lastInsertId($name)
    {
        try {
            if (!self::$instance instanceof PDO)
                throw new PDOException(self::$exception['no-instance']);

            if (!self::$instance->lastInsertId($name))
                throw new PDOException(current(self::$instance->errorInfo()) . ' ' . end(self::$instance->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

}

?>