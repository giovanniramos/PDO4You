<?php

/**
 * Esta classe implementa o padrão de projeto Singleton para conexão de base de dados, usando a extensão PDO (PHP Data Objects)
 * 
 * @category PDO
 * @package PDO4You
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2012, Giovanni Ramos
 * @since 2010-09-07
 * @version 2.3
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
     * Armazena o nome da base de dados
     * 
     * @access private static
     * @var string
     * 
     * */
    private static $database;

    /**
     * Armazena a instância da classe PDO4You de conexão
     * 
     * @access private static
     * @var array
     * 
     * */
    private static $instance = array();

    /**
     * Armazena uma nova instância de conexão
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
    private static $connection = false;

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
        'code-1045' => 'Houve uma falha de comunica&ccedil;&atilde;o com a base de dados usando: \'%1$s\'@\'%2$s\'',
        'code-2002' => 'Nenhuma conex&atilde;o p&ocirc;de ser feita porque a m&aacute;quina de destino as recusou ativamente. Este host n&atilde;o &eacute; conhecido.',
        'code-2005' => 'N&atilde;o houve comunica&ccedil;&atilde;o com o host fornecido. Verifique as suas configura&ccedil;&otilde;es.',
        'no-database' => 'Base de dados desconhecida. Verifique as suas configura&ccedil;&otilde;es.',
        'no-instance' => 'N&atilde;o existe uma inst&acirc;ncia do objeto PDO4You dispon&iacute;vel. Imposs&iacute;vel acessar os m&eacute;todos.',
        'no-argument-sql' => 'O argumento SQL de consulta est&aacute; ausente.',
        'no-instruction-json' => 'A instru&ccedil;&atilde;o SQL no formato JSON est&aacute; ausente.',
        'not-implemented' => 'N&atilde;o implementado.',
        'duplicate-key' => 'N&atilde;o foi poss&iacute;vel gravar o registro. Existe uma chave duplicada na tabela.<br />\'%1$s',
        'critical-error' => 'Erro cr&iacute;tico detectado no sistema.',
        'json-error-depth' => 'Profundidade m&aacute;xima da pilha excedida.',
        'json-error-state-mismatch' => 'Incompatibilidade de modos ou opera&ccedil;&atilde;o aritm&eacute;tica imposs&iacute;vel de ser representado.',
        'json-error-ctrl-char' => 'Atributo de controle inesperado foi encontrado.',
        'json-error-syntax' => 'A query JSON fornecida est&aacute; mal formatada.'
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
     * @param string $alias Ponteiro identificador da instância
     * @param string $driver Driver DSN de conexão
     * @param string $user Usuário da base de dados
     * @param string $pass Senha da base de dados
     * @param string $option Configuração do driver de conexão
     * @return void
     * @throws Exception Dispara uma exceção em caso de falhas na conexão
     * 
     * */
    private static function singleton($alias, $driver, $user, $pass, $option)
    {
        try {
            try {
                self::$instance[$alias] = @ new PDO($driver, $user, $pass, $option);
                self::$instance[$alias]->setAttribute(PDO::ATTR_ERRMODE, ($_SERVER['SERVER_ADDR'] == '127.0.0.1') ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT);
            } catch (PDOException $e) {
                $error = self::getErrorInfo($e);

                if ($error['code'] == '2005')
                    throw new PDOException(self::$exception['code-2005']);
                elseif ($error['code'] == '2002')
                    throw new PDOException(self::$exception['code-2002']);
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
     * Método alternativo para manipular a conexão estabelecida entre base de dados
     * 
     * @access public static
     * @param string $base Nome da base de dados usada como instância de conexão
     * @return void
     * 
     * */
    public static function setInstance($base)
    {
        self::$handle = self::getInstance($base);
    }

    /**
     * Método que obtém uma única instância da base de dados por conexão
     * 
     * @access public static
     * @param string $base O nome da base de dados que será usada como instância de conexão
     * @param string $type Tipo do driver DSN utilizado na conexão
     * @param string $user Usuário da base de dados
     * @param string $pass Senha da base de dados
     * @param string $option Configuração adicional do driver
     * @return object O objeto retornado é uma instância da conexão estabelecida
     * @throws Exception Dispara uma exceção em caso de falhas na conexão
     * 
     * */
    public static function getInstance($base = DATA_BASE, $type = DATA_TYPE, $user = DATA_USER, $pass = DATA_PASS, Array $option = null)
    {
        try {
            try {
                self::$datahost = DATA_HOST;
                self::$dataport = DATA_PORT;
                self::$database = $base;

                if (!array_key_exists(self::$database, self::$instance)):
                    $type = !(empty($type)) ? strtolower($type) : 'mysql';

                    switch ($type):
                        case 'mysql':
                        case 'pgsql': $driver = $type . ':dbname=' . self::$database . ';host=' . self::$datahost . ';port=' . self::$dataport . ';';
                            break;
                        case 'mssql':
                        case 'sybase':
                        case 'dblib': $driver = $type . ':dbname=' . self::$database . ';host=' . self::$datahost . ';';
                            break;
                        case 'oracle':
                        case 'oci': $driver = 'oci:dbname=' . self::$database . ';';
                            break;
                        case 'sqlsrv': $driver = 'sqlsrv:Database=' . self::$database . ';Server=' . self::$datahost . ';';
                            break;
                        default: $driver = $type;
                    endswitch;

                    $option = !is_null($option) ? $option : array(PDO::ATTR_PERSISTENT => self::$connection, PDO::ATTR_CASE => PDO::CASE_LOWER);

                    self::singleton(self::$database, $driver, $user, $pass, $option);
                endif;

                self::$handle = self::$instance[self::$database];

                #self::setDatabase(self::$database);
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

        return self::$handle;
    }

    /**
     * Método para mudar o Schema padrão da base de dados
     * 
     * @access private static
     * @param string $base Nome da base de dados
     * @return void
     * 
     * */
    private static function setDatabase($base)
    {
        $driver = self::getDriver();

        switch ($driver):
            case 'mysql': self::$handle->exec('USE ' . $base);
                break;
            case 'pgsql': self::$handle->exec('SET search_path TO ' . $base);
                break;
            default:
                throw new PDOException(self::$exception['not-implemented']);
        endswitch;
    }

    /**
     * Método para recuperar o nome da base de dados apontada como instância corrente de conexão
     * 
     * @access public static
     * @param void
     * @return string Retorna o nome da base de dados instanciada
     * 
     * */
    public static function getDatabase()
    {
        return self::$database;
    }

    /**
     * Método para recuperar o nome do driver corrente
     * 
     * @access public static
     * @param void
     * @return string Retorna o nome do driver
     * 
     * */
    public static function getDriver()
    {
        return self::$handle->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Método para definir o tipo de comunicação com a base de dados
     * O padrão de conexão é persistente
     * 
     * @access public static
     * @param boolean $connection Define uma conexão persistente
     * @return void
     * 
     * */
    public static function setPersistent($connection)
    {
        self::$connection = $connection;
    }

    /**
     * Método para capturar as informações de erro de uma Exception
     * 
     * @access public static
     * @param Exception $e Obtém a mensagem da exceção lançada
     * @param boolean $show Exibe na tela os valores capturados na mensagem
     * @return array Retorna um vetor da mensagem capturada
     * 
     * */
    public static function getErrorInfo(Exception $e, $show = false)
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

        if ($show)
            echo '<pre>', print_r($info), '</pre>';

        try {
            if ($info['state'] == '23000')
                throw new PDOException(sprintf(self::$exception['duplicate-key'], $info['message']));
            return $info;
        } catch (PDOException $e) {
            self::stackTrace($e);
        }

        return $info;
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
            if (self::$handle instanceof PDO):
                self::setStyle();

                $info = self::$handle->getAttribute(constant("PDO::ATTR_SERVER_INFO"));
                echo '<h7>' . $info . '</h7>';
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
            if (self::$handle instanceof PDO):
                self::setStyle();

                $info = self::$handle->getAvailableDrivers();
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
     * Método para exibir a stack trace de uma Exception lançada 
     * 
     * @access public static
     * @param array $e Contém a pilha de erros gerada pela exceção 
     * @return void
     * 
     * */
    public static function stackTrace(Exception $e)
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
     * @return string Retorna o trecho de código destacado
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
     * @param boolean $count Conta o número de linhas afetadas
     * @return mixed Retorna todos os registros afetados
     * 
     * */
    private static function selectRecords($query, $type, $use = null, $count = true)
    {
        $total = null;

        $pdo = self::$handle;
        try {
            if (is_null($query))
                throw new PDOException(self::$exception['no-argument-sql']);

            if (!is_null($use))
                self::setInstance($use);

            if (!self::$handle instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            else:
                $pre = $pdo->prepare($query);
                $pre->execute();
                $total = $pre->rowCount();

                if ($count)
                    self::$rowCount = $total;

                switch ($type):
                    case 'num' : $result = $pre->fetchAll(PDO::FETCH_NUM);
                        break;
                    case 'obj' : $result = $pre->fetchAll(PDO::FETCH_OBJ);
                        break;
                    case 'all' : $result = $pre->fetchAll(PDO::FETCH_BOTH);
                        break;
                    default : $result = $pre->fetchAll(PDO::FETCH_ASSOC);
                endswitch;
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

        $pdo = self::$handle;
        try {
            if (is_null($json))
                throw new PDOException(self::$exception['no-instruction-json']);

            if (!is_null($use))
                self::setInstance($use);

            $pdo->beginTransaction();

            try {
                $jarr = self::parseJSON($json);

                if ($type == 'insert') {
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
                }

                if ($type == 'update') {
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
                }

                if ($type == 'delete') {
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
                }

                self::$rowCount = $total;
            } catch (PDOException $e) {
                $pdo->rollback();

                throw $e;
            }

            $pdo->commit();
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
     * Método que retorna o ID do último registro inserido ou o valor de seqüência
     * 
     * @access public static
     * @param string $sequence Nome da variável de sequência solicitado em algumas base de dados
     * @return array Retorna o ID do último registro
     * 
     * */
    public static function lastId($sequence = 'table_id_seq')
    {
        $driver = self::getDriver();

        switch ($driver):
            case 'mysql': $sql = "SELECT LAST_INSERT_ID() AS lastId;";
                break;
            #case 'pgsql': $sql = "SELECT CURRVAL('" . $sequence . "') AS lastId;";
            case 'pgsql': $sql = "SELECT LASTVAL() AS lastId;";
                break;
            case 'mssql': $sql = "SELECT @@IDENTITY AS lastId;";
                break;
            #case 'oracle': $sql = "SELECT " . $sequence . ".CURRVAL AS lastId FROM DUAL;";
            case 'oracle': $sql = "SELECT last_number AS lastId FROM user_sequences WHERE sequence_name = '" . $sequence . "';";
                break;
            #case 'sqlsrv': $sql = "SELECT current_value AS lastId FROM sys.sequences WHERE name = '" . $sequence . "';";
            case 'sqlsrv': $sql = "SELECT SCOPE_IDENTITY() AS lastId;";
                break;
            default:
                throw new PDOException(self::$exception['not-implemented']);
        endswitch;

        self::$lastId = self::selectRecords($sql, null, null, false);

        return self::$lastId[0]['lastid'];
    }

    /**
     * Método que retorna o número de linhas afetadas pelo último CRUD (INSERT, SELECT, UPDATE ou DELETE)
     * 
     * @access public static
     * @param void
     * @return string Retorna o total de linhas afetadas
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
     * @param string $json String no formato de notação json
     * @return array Retorna o array convertido
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
        } catch (PDOException $e) {
            self::stackTrace($e);
        }

        return $jarr;
    }

    /**
     * Método do MySQL, para exibir e descrever as tabelas da base de dados
     * 
     * @access public static
     * @param void 
     * @return void
     * 
     * */
    public static function showMySqlTables()
    {
        self::setStyle();

        $tables = self::select("SHOW TABLES;");
        $index = array_keys($tables[0]);
        $baseName = preg_replace('~tables_in_~', '', $index[0]);

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $baseName . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br /><br />';
        foreach ($tables as $k1 => $v1):
            foreach ($v1 as $k2 => $v2):
                $desc = self::select("DESCRIBE " . $baseName . "." . $v2);

                $html.= '<code>&nbsp;<strong>Table</strong>: ' . $v2 . '</code>';
                $html.= '<code class="trace">';
                foreach ($desc as $k3 => $v3):
                    $html.= '<div class="number">&nbsp;</div> ';
                    $html.= '<span><i style="color:#00B;">' . $v3['field'] . "</i> - " . strtoupper($v3['type']) . '</span><br />';
                endforeach;
                $html.= '</code>';
            endforeach;
        endforeach;
        $html.= '</div>';

        exit($html);
    }

    /**
     * Método do PostgreSQL, para exibir e descrever as tabelas da base de dados
     * 
     * @access public static
     * @Param void 
     * @return void
     * 
     * */
    public static function showPgSqlTables()
    {
        self::setStyle();

        $tables = self::select("SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema');");
        $baseName = DATA_BASE;

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $baseName . ' &nbsp;<strong>Total of tables:</strong> ' . count($tables) . '<br /><br />';
        foreach ($tables as $k1 => $v1):
            foreach ($v1 as $k2 => $v2):
                $desc = self::select("SELECT a.attname AS field, t.typname AS type FROM pg_class c, pg_attribute a, pg_type t WHERE c.relname = '" . $v2 . "' AND a.attnum > 0 AND a.attrelid = c.oid AND a.atttypid = t.oid ORDER BY a.attnum");

                $html.= '<code>&nbsp;<strong>Table</strong>: ' . $v2 . '</code>';
                $html.= '<code class="trace">';
                foreach ($desc as $k3 => $v3):
                    $html.= '<div class="number">&nbsp;</div> ';
                    $html.= '<span><i style="color:#00B;">' . $v3['field'] . "</i> - " . strtoupper($v3['type']) . '</span><br />';
                endforeach;
                $html.= '</code>';
            endforeach;
        endforeach;
        $html.= '</div>';

        exit($html);
    }

    /**
     * Método do PostgreSQL, para exibir e descrever as tabelas da base de dados
     * 
     * @access public static
     * @Param void 
     * @return void
     * 
     * */
    public static function showPgSqlViews()
    {
        self::setStyle();

        $tables = self::select("SELECT table_name, view_definition FROM information_schema.views WHERE view_definition IS NOT NULL AND table_schema NOT IN ('pg_catalog', 'information_schema');");
        $baseName = DATA_BASE;

        $html = '<div class="pdo4you">';
        $html.= '<strong>Database:</strong> ' . $baseName . ' &nbsp;<strong>Total of views:</strong> ' . count($tables) . '<br /><br />';
        foreach ($tables as $k1 => $v1):
            $html.= '<code>&nbsp;<strong>View</strong>: <i style="color:#00B;">' . $v1['table_name'] . '</i></code>';
            $html.= '<code class="trace">';
            $html.= '<div class="number">&nbsp;</div> ';
            $html.= '<span>' . $v1['view_definition'] . '</span><br />';
            $html.= '</code>';
        endforeach;
        $html.= '</div>';

        exit($html);
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
        $head.= 'Content-type: text/html; charset=iso-8859-1' . PHP_EOL;
        $head.= 'From: Alerta automático <fatalerror@noreply.br>' . PHP_EOL;
        $head.= 'Return-Path: Alerta automático <fatalerror@noreply.br>' . PHP_EOL;
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

    public function beginTransaction()
    {
        try {
            if (!self::$handle instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            endif;

            if (!self::$handle->beginTransaction())
                throw new PDOException(current(self::$handle->errorInfo()) . ' ' . end(self::$handle->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public function commit()
    {
        try {
            if (!self::$handle instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            endif;

            if (!self::$handle->commit())
                throw new PDOException(current(self::$handle->errorInfo()) . ' ' . end(self::$handle->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public function exec($query)
    {
        try {
            if (!self::$handle instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            endif;

            if (!self::$handle->exec($query))
                throw new PDOException(current(self::$handle->errorInfo()) . ' ' . end(self::$handle->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public function query($query)
    {
        try {
            if (!self::$handle instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            endif;

            if (!self::$handle->query($query))
                throw new PDOException(current(self::$handle->errorInfo()) . ' ' . end(self::$handle->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public function rollBack()
    {
        try {
            if (!self::$handle instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            endif;

            if (!self::$handle->rollBack())
                throw new PDOException(current(self::$handle->errorInfo()) . ' ' . end(self::$handle->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

    public function lastInsertId($name)
    {
        try {
            if (!self::$handle instanceof PDO):
                throw new PDOException(self::$exception['no-instance']);
            endif;

            if (!self::$handle->lastInsertId($name))
                throw new PDOException(current(self::$handle->errorInfo()) . ' ' . end(self::$handle->errorInfo()));
        } catch (PDOException $e) {
            self::stackTrace($e);
        }
    }

}

?>