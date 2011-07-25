<?php

/**
 * Esta classe implementa o padrão de projeto Singleton para conexão de banco de dados usando a extensão PDO (PHP Data Objects)
 * MySQL é assumido como driver padrão
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @twitter @giovanni_ramos
 * @version 2.0
 * @since 2010-09-07 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 * */


class PDO4You implements PDOConfig 
{
	/**
	 * Variável que define o driver DSN de conexão
	 * 
	 * @access private
	 * @var string
	 * 
	 * */
	private $driver;

	/**
	 * Variável que armazena o usuário do banco de dados
	 * 
	 * @access private
	 * @var string
	 * 
	 * */
	private $user;

	/**
	 * Variável que armazena a senha do banco de dados
	 * 
	 * @access private
	 * @var string
	 * 
	 * */
	private $pass;

	/**
	 * Variável que armazena a configuração do driver PDO
	 * 
	 * @access private
	 * @var array
	 * 
	 * */
	private $option;

	/**
	 * Variável que armazena a instância da classe de conexão
	 * 
	 * @access private static
	 * @var array
	 * 
	 * */
	private static $instance = array();

	/**
	 * Variável booleano que ativa ou não uma conexão persistente
	 * 
	 * @access private static
	 * @var boolean
	 * 
	 * */
	private static $connection = true;

	/**
	 * Variável que armazena uma nova instância de conexão
	 * 
	 * @access private static
	 * @var array
	 * 
	 * */
	private static $handle = array();


	/**
	 * O construtor é definido como privado, impedindo a instância direta da classe
	 * 
	 * @access private
	 * 
	 * */
	private function PDO4You() {}


	/**
	 * Método Singleton de conexão
	 * 
	 * @access private static
	 * @param string $alias Alias identificador da instância
	 * @param string $driver Tipo de driver utilizado na conexão
	 * @param string $user Usuário do banco de dados
	 * @param string $pass Senha do banco de dados
	 * @param string $option Configuração adicional do driver DSN
	 * @return void
	 * @throws Exception Uma Exception será gerada em caso de erros encontrados
	 * 
	 * */
	private static function singleton($alias, $driver, $user, $pass, $option)
	{
		try {
			try {
				self::$instance[$alias] = new PDO($driver, $user, $pass, $option);
				self::$instance[$alias]->setAttribute(
					PDO::ATTR_ERRMODE, ($_SERVER['SERVER_ADDR'] == '127.0.0.1') ? 
					PDO::ERRMODE_EXCEPTION : 
					PDO::ERRMODE_SILENT
				);
				self::$instance[$alias]->exec('SET CHARACTER SET utf8');
				self::$instance[$alias]->exec('SET LC_TIME_NAMES = "pt_BR"');
			} catch (PDOException $e) {
				$error = self::getErrorInfo($e);
				if($error['code'] == '2005')
					throw new PDOFatalError('N&atilde;o houve comunica&ccedil;&atilde;o com o host fornecido. Verifique as suas configura&ccedil;&otilde;es.');
				elseif($error['code'] == '1045')
					throw new PDOFatalError('Houve uma falha de comunica&ccedil;&atilde;o com o banco de dados usando \''.$user.'\'@\''.$pass.'\' ');
				else
					self::stackTrace($e);
			}
		} catch (PDOFatalError $e) {
			self::stackTrace($e);
		}
	}


	/**
	 * Método alternativo para manipular a conexão estabelecida entre banco de dados
	 * 
	 * @access public static
	 * @param string $base Nome do banco de dados usado como ponteiro de conexão
	 * @return void
	 * 
	 * */
	public static function setInstance($base = null)
	{
		self::$handle = self::getInstance($base);
	}


	/**
	 * Método que obtém uma única instância da classe por conexão
	 * A instância se utiliza de um ponteiro para troca de conexão
	 * 
	 * @access public static
	 * @param string $base O nome do banco de dados, que será usado como ponteiro de conexão
	 * @param string $driver Driver DSN utilizado na conexão
	 * @param string $user Usuário do banco de dados
	 * @param string $pass Senha do banco de dados
	 * @param string $option Configuração adicional do driver DSN
	 * @return object O objeto retornado é uma instância da conexão estabelecida
	 * 
	 * */
	public static function getInstance($base = self::DATA_BASE, $driver = NULL, $user = self::DATA_USER, $pass = self::DATA_PASS, Array $option = NULL)
	{
		try {
			try {
				$base = strtolower($base);
				
				if (!array_key_exists($base, self::$instance)) {
					$driver = !is_null($driver) ? $driver : 'mysql:host='.self::DATA_HOST.';port='.self::DATA_PORT;
					$option = !is_null($option) ? $option : array( 
						PDO::ATTR_PERSISTENT => self::$connection, 
						PDO::ATTR_CASE => PDO::CASE_LOWER 
					);
					self::singleton($base, $driver, $user, $pass, $option);
				}
				self::$handle = self::$instance[$base];
				self::$handle->exec("USE ".$base);
			} catch (PDOException $e) {
				$error = self::getErrorInfo($e);
				if($error['state'] == '42000')
					throw new PDOFatalError('Banco de dados desconhecido. Verifique as suas configura&ccedil;&otilde;es.');
				else
					self::stackTrace($e);
			}
		} catch (PDOFatalError $e) {
			self::stackTrace($e);
		}
				
		return self::$handle;
 	}


	/**
	 * Método para definir o tipo de comunicação com o banco de dados
	 * O padrão de conexão é persistente 
	 * 
	 * @access public static
	 * @param boolean $connection Define uma conexão persistente
	 * @return void
	 * 
	 * */
	public static function setPersistent(Boolean $connection)
	{
		self::$connection = $connection;
	}


	/**
	 * Método para capturar as informações de erro de uma Exception
	 * 
	 * @access public static
	 * @param Exception $e Obtém a mensagem da exceção lançada
	 * @param boolean $show Exibe na tela a mensagem capturada
	 * @return array Retorna como um Array a mensagem da exceção capturada
	 * 
	 * */
	public static function getErrorInfo(Exception $e, $show = false)
	{
		if(defined(self::WEBMASTER)) self::pdo_fire_alert('Foi detectado um erro crítico no sistema!', $e);
		
		$errorInfo = null;
		$message = $e->getMessage();
		
		preg_match('~SQLSTATE[[]([[:alnum:]]{1,})[]]:?\s[[]?([[:digit:]]{1,})?[]]?\s?(.+)~', $message, $errorInfo);
		$info['state'] = isset($errorInfo[1]) ? $errorInfo[1] : null;
		$info['code'] = isset($errorInfo[2]) ? $errorInfo[2] : null; 
		$info['message'] = isset($errorInfo[3]) ? $errorInfo[3] : null;
		
		if($show)
			echo "<pre>",print_r($info),"</pre>";
		
		return $info;
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
			if(self::$handle instanceof PDO):
				self::setStyle();
																
		  		$driver = self::$handle->getAvailableDrivers();
		  		$driver = '<b>Drivers suportados:</b> '. implode(", ", $driver);
		  		echo '<div class="inline">'.$driver.'</div>';
			else:
				throw new PDOFatalError('Não existe uma instância inicializada de conexão.');
			endif;
		} catch (PDOFatalError $e) {
			self::stackTrace($e);
		}
    }


	/**
	 * Método para exibir informações sobre a meta do servidor de banco de dados conectado
	 * 
	 * @access public static 
	 * @param void
	 * @return void 
	 * 
	 * */
    public static function getServerInfo()
	{
		try {
			if(self::$handle instanceof PDO):
				self::setStyle();
								
		  		$server = self::$handle->getAttribute(constant("PDO::ATTR_SERVER_INFO")) . '</ br>';
		  		echo '<div class="inline">'.$server.'</div>';  	
			else:
				throw new PDOFatalError('Não existe uma instância inicializada de conexão.');
			endif;
		} catch (PDOFatalError $e) {
			self::stackTrace($e);
		}
    }


	/**
	 * Método para aplicar CSS à página
	 * 
	 * @access public static 
	 * @param void
	 * @return void
	 * 
	 * */
    public static function setStyle()
	{
		$style = '<style type="text/css">';
		$style.= 'body       { background:#FAFAFA; font:normal 12px/1.7em Bitstream Vera Sans Mono,Courier New,Monospace; margin:0; }';
		$style.= '#pdo4you   { margin:8px; padding:0; }';
		$style.= '.inline    { background:#000000; color:#FFF; padding:2px 5px; }';
		$style.= '.source    { background:#EFEFEF; border:solid 1px #DDD; border-bottom:none; border-right:solid 1px #BBB; overflow:auto; }';
		$style.= '.bottom    { background:#FFFFFF; border-top:none; border-bottom:solid 1px #BBB; border-right:solid 1px #BBB; margin-bottom:15px; }';
		$style.= '.number    { background:#EFEFEF; color:#AAA; min-width:40px; padding:0 5px; margin-right:5px; float:left; text-align:right; cursor:default; }';
		$style.= '.highlight { background:#FFFFCC; }';
		$style.= '</style>';
		
		print $style;
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
  		self::setStyle();
  		
		if(!self::FIREDEBUG) return;
  		if(defined(self::WEBMASTER)) self::pdo_fire_alert('Erro crítico detectado no sistema!', $e);
  		
		$count = 0;
		$stack = '<div id="pdo4you">';
		$stack.= '</strong>Exception:</strong> '.$e->getMessage().'<br /><br />';
		foreach ($e->getTrace() as $t):
			$stack.= '<div class="source">&nbsp;<strong>#'.$count++.'</strong> '.$t['file'].':'.$t['line'].'</div>';
			$stack.= '<div class="source bottom">'.self::highlightSource($t['file'], $t['line']).'</div>';
		endforeach;
		$stack.= '</div>';
		
		exit($stack);
    }


	/**
	 * Método para destacar a sintaxe de um código
	 * 
	 * @access public static
	 * @param string $fileName Nome do arquivo
	 * @param string $lineNumber Define a linha de destaque
	 * @param string $showLines Define o número de linhas a serem exibidas
	 * @return string Retorna o trecho de código destacado
	 * @credits Marcus Welz
	 * 
	 * */
	public static function highlightSource($fileName, $lineNumber, $showLines = 5)
    {
		$trace = null;
		$lines = file_get_contents($fileName);
		$lines = highlight_string($lines, true);
		$offset = max(0, $lineNumber - ceil($showLines / 2));
		$lines = array_slice(explode("<br />", $lines), $offset, $showLines);
		foreach ($lines as $l):
			$offset++;
			$line = '<div class="number">'.sprintf('%4d', $offset).'</div>'.$l.' <br />';
			$trace.= ($offset == $lineNumber) ? '<div class="highlight">'.$line.'</div>' : $line;
		endforeach;
		
		return $trace;
    }


	/**
	 * Método para exibir e descrever as tabelas do banco de dados
	 * 
	 * @access public static
	 * @param void 
	 * @return void
	 * 
	 * */
	public static function showTables()
    {
		self::setStyle();
		
		$tables = self::select("SHOW TABLES", 'ass');
		$index = array_keys($tables[0]);
		$baseName = preg_replace('~tables_in_~', '', $index[0]);
		
		$html = '<div id="pdo4you">';
		$html.= '<strong>Banco de dados:</strong> '.$baseName.' &nbsp; <strong>Número de tabelas:</strong> '.count($tables).'<br /><br />';
		foreach($tables as $k1 => $v1):
			foreach($v1 as $k2 => $v2):
				$desc = self::select("DESCRIBE ".$baseName.".".$v2, 'ass');
				
				$html.= '<div class="source">&nbsp;<strong>Tabela</strong>: '.$v2.'</div>';
				$html.= '<div class="source bottom">';								
				foreach($desc as $k3 => $v3):
					$html.= '<div class="number">&nbsp;</div> ';
					$html.= '<span><i style="color:#0000BB;">'.$v3['field']."</i> - ".$v3['type'].'</span><br />';
				endforeach;
				$html.= '</div>';
			endforeach;
		endforeach;
		$html.= '</div>';
		
		exit($html);
	}


	/**
	 * Método para exibir na tela uma query de consulta
	 * 
	 * @access public static
	 * @param string $query Instrução SQL
	 * @return void
	 * 
	 * */
	public static function showQuery($query = null)
	{
		self::setStyle();
		
		$html = '<div id="pdo4you">';
		$html.= '<strong>Instrução SQL de consulta</strong> <br /><br />';
		$html.= '<div class="source">&nbsp;<strong>Debug</strong></div>';
		$html.= '<div class="source bottom">';								
		$html.= '<span><pre><i style="color:#0000BB;">'.print_r($query, true).'</pre></span><br />';
		$html.= '</div>';
		$html.= '</div>';
		
		exit($html);
	}


	/**
	 * Método para consulta de registros no banco de dados
	 * 
	 * @access private static
	 * @param string $query Instrução SQL de consulta
	 * @param string $type Tipo de retorno da consulta
	 * @param string $use Ponteiro de uma conexão estabelecidade
	 * @return mixed Retorna todos os registros afetados
	 * 
	 * */
	private static function selectRecords($query, $type = null, $use = null)
	{
		if(!is_null($use))
			self::setInstance($use);
	    $pdo = self::$handle;
	    
		try {
			$pre = $pdo->prepare($query);
			$pre->execute();
			
			switch($type):
				case 'sum' : $result = $pre->rowCount(); break;
				case 'num' : $result = $pre->fetchAll(PDO::FETCH_NUM); break;
				case 'obj' : $result = $pre->fetchAll(PDO::FETCH_OBJ); break;
				case 'all' : $result = $pre->fetchAll(PDO::FETCH_BOTH); break;
				default    : $result = $pre->fetchAll(PDO::FETCH_ASSOC); 
			endswitch;
		} catch (PDOException $e) {
			echo self::stackTrace($e);
		}
		$pdo = null;
		
		return $result;	
	}


	/**
	 * Método referente ao rowCount()
	 * 
	 * @access public static
	 * @param string $sql Instrução SQL de consulta
	 * @param object $use Instância de uma nova conexão (OPCIONAL)
	 * @return integer Retorna o total de registros afetados
	 * 
	 * */
	public static function rowCount($sql, $use = null)
	{
		return self::selectRecords($sql, 'sum', $use);
	}


	/**
	 * Método referente ao fetchAll(PDO::FETCH_NUM)
	 * 
	 * @access public static
	 * @param string $sql Instrução SQL de consulta
	 * @param object $use Instância de uma nova conexão (OPCIONAL)
	 * @return array Retorna um array indexado pelo número da coluna
	 * 
	 * */
	public static function selectNum($sql, $use = null)
	{
		return self::selectRecords($sql, 'num', $use);
	}


	/**
	 * Método referente ao fetch(PDO::FETCH_OBJ)
	 * 
	 * @access public static
	 * @param string $sql Instrução SQL de consulta
	 * @param object $use Instância de uma nova conexão (OPCIONAL)
	 * @return array Retorna um objeto com nomes de coluna como propriedades
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
	 * @param string $sql Instrução SQL de consulta
	 * @param object $use Instância de uma nova conexão (OPCIONAL)
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
	 * @param string $sql Instrução SQL de consulta
	 * @param object $use Instância de uma nova conexão (OPCIONAL)
	 * @return array Retorna um array indexado pelo nome da coluna
	 * 
	 * */
	public static function select($sql, $use = null)
	{
		return self::selectRecords($sql, null, $use);
	}


	/**
	 * Método para inserir um novo registro no banco de dados
	 * 
	 * @access public static
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string Retorna a Id da última inserção
	 * 
	 * */
	public static function insert($table, $array)
	{
		$pdo = self::$handle;
		$pdo->beginTransaction();
		try {
			foreach($array as $k1 => $v1):
				$sql = self::pdo_implode_insert($table, $v1);

				$pre = $pdo->prepare($sql);
				foreach($v1 as $k2 => $v2)
				$pre->bindValue(':'.$k2, $v2);

				$pre->execute();
				$lastInsertId = $pdo->lastInsertId();
			endforeach;
		} catch (PDOException $e) {
			echo self::stackTrace($e);
			$pdo->rollback();
		}
		$pdo->commit();
		$pdo = null;
		
		return $lastInsertId;
	}


	/**
	 * Método para atualizar os dados de um registro
	 * 
	 * @access public static
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string Retorna o número de linhas afetadas
	 * 
	 * */
	public static function update($table, $array)
	{
		$pdo = self::$handle;
		$pdo->beginTransaction();
		try {
			foreach($array as $k1 => $v1):
				$sql = self::pdo_implode_update($table, $v1);

				$pre = $pdo->prepare($sql);
				foreach($v1[1] as $k2 => $v2)
				$pre->bindValue(':'.$k2, $v2);

				foreach($v1[0] as $k2 => $v2)
				$pre->bindValue(':'.$k2, $v2);

				$pre->execute();
				$rowCount = $pre->rowCount();
			endforeach;
		} catch (PDOException $e) {
			echo self::stackTrace($e);
			$pdo->rollback();
		}
		$pdo->commit();
		$pdo = null;
		
		return $rowCount;
	}


	/**
	 * Método para excluir um registro
	 * 
	 * @access public static
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string Retorna o número de linhas afetadas
	 * 
	 * */
	public static function delete($table, $array)
	{
		$pdo = self::$handle;
		$pdo->beginTransaction();
		try {
			foreach($array as $k1 => $v1):
				$sql = self::pdo_implode_delete($table, $v1);

				$pre = $pdo->prepare($sql);
				foreach($v1 as $k2 => $v2) 
				$pre->bindValue(':'.$k2, $v2);

				$pre->execute();
				$rowCount = $pre->rowCount();
			endforeach;
		} catch (PDOException $e) {
			echo self::stackTrace($e);
			$pdo->rollback();
		}
		$pdo->commit();
		$pdo = null;
		
		return $rowCount;
	}


	/**
	 * Unifica os elementos de um array para gerar a instrução INSERT do banco de dados
	 * 
	 * @access public static
	 * @param string $table Nome da tabela
	 * @param array $values Matriz de valores
	 * @return string
	 * 
	 * */
	public static function pdo_implode_insert($table, $values)
	{
		if(empty($values)) return null;
		$str = 'INSERT INTO '.$table.' ('; 
		foreach($values as $k => $v)
	 	$str.= ', '.$k;
	 	$str = preg_replace('/, /', '', $str, 1);
	 	$str.= ') VALUES (';
		foreach($values as $k => $v)
	 	$str.= ', :'.$k;
	 	$str.= ')';
	 	$str = preg_replace('/, :/', ':', $str, 1);
	 	
	 	return $str;
	}


	/**
	 * Unifica os elementos de um array para gerar a instrução UPDATE do banco de dados
	 * 
	 * @access public static
	 * @param string $table Nome da tabela
	 * @param array $values Matriz de valores
	 * @return string
	 * 
	 * */
	public static function pdo_implode_update($table, $values)
	{
		if(empty($values)) return null;
		$str = 'UPDATE '.$table.' SET ';
		foreach($values[1] as $k => $v)
	 	$str.= ', '.$k.'=:'.$k;
		$str = preg_replace( '/, /', '', $str, 1);
		$str.= ' WHERE ';
		foreach($values[0] as $k => $v)
	 	$str.= ' AND '.$k.'=:'.$k;
	 	$str = preg_replace('/ AND /', '', $str, 1);
	 	
		return $str;
	}


	/**
	 * Unifica os elementos de um array para gerar a instrução DELETE do banco de dados
	 * 
	 * @access public static
	 * @param string $table Nome da tabela
	 * @param array $values Matriz de valores
	 * @return string
	 * 
	 * */
	public static function pdo_implode_delete($table, $values)
	{
		if(empty($values)) return null;
		$str = 'DELETE FROM '.$table.' WHERE ';
		foreach($values as $k => $v)
	 	$str.= ' AND '.$k.'=:'.$k;
		$str = preg_replace('/ AND /', '', $str, 1);
		
		return $str;
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
	public static function pdo_fire_alert($text, $error)
	{
		$head = 'MIME-Version: 1.1'.PHP_EOL;
		$head.= 'Content-type: text/html; charset=iso-8859-1'.PHP_EOL;
		$head.= 'From: Alerta automático <fatalerror@noreply.br>'.PHP_EOL;
		$head.= 'Return-Path: Alerta automático <fatalerror@noreply.br>'.PHP_EOL;
		$body = 'Diagnóstico do alerta:<br /><br /><b>'.$error->getMessage().'</b><br />'.$error->getFile().' : '.$error->getLine();
		
		if(self::FIREALERT) @mail(self::WEBMASTER, $text, $body, $head);
	}



	/**
	 * Assim como o construtor, tornamos __clone privado para impedir a clonagem da instância da classe
	 * 
	 * @access private
	 * @param void
	 * @return void
	 * 	 
	 * */
	final private function __clone() {}


	public function beginTransaction() 
	{
		if ( !self::$handle->beginTransaction() )
			throw new Error( self::$handle->errorInfo() );
	}

	public function commit() 
	{
		if ( !self::$handle->commit() )
			throw new Error( self::$handle->errorInfo() );
	}

	public function exec($query) 
	{
		if ( !self::$handle->exec($query) )
			throw new Error( self::$handle->errorInfo() );
	}

	public function query($query) 
	{
		if ( !self::$handle->query($query) ) 
			throw new Error( self::$handle->errorInfo() );
	}

	public function rollBack() 
	{
		if ( !self::$handle->rollBack() )
			throw new Error( self::$handle->errorInfo() );
	}

	public function lastInsertId($name) 
	{
		if ( !self::$handle->lastInsertId($name) )
			throw new Error( self::$handle->errorInfo() );
	}


}


?>