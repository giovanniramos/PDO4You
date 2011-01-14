<?php

/**
 * Esta classe implementa o padrão de projeto Singleton para conexão de banco de dados usando a extensão PDO (PHP Data Objects)
 * MySQL é o driver padrão
 * 
 * Para mais informações, acesse a documentação do PDO em:
 * http://ca.php.net/manual/en/book.pdo.php
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @twitter @giovanni_ramos
 * @version 1.0
 * @since 2010-09-07 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 * */


class PDO4You extends PDOLibrary implements PDOConfig  {

	/**
	 * Esta variável armazena o driver dsn de conexão
	 * Ex.: pgsql:dbname=database;host=localhost
	 * 
	 * @access private
	 * @var string
	 * 
	 * */
	private $driver;

	/**
	 * Esta variável armazena o usuário do banco de dados
	 * 
	 * @access private
	 * @var string
	 * 
	 * */
	private $user;

	/**
	 * Esta variável armazena a senha do banco de dados
	 * 
	 * @access private
	 * @var string
	 * 
	 * */
	private $pass;

	/**
	 * Este vetor armazena as opções do driver PDO
	 * 
	 * @access private
	 * @var array
	 * 
	 * */
	private $option;

	/**
	 * Esta variável armazena a instância da conexão
	 * 
	 * @access private
	 * @var string
	 * 
	 * */
	private static $instance = NULL;


	/**
	 * O construtor é definido como privado, impedindo a instância direta da classe
	 * @access private
	 * 
	 * */
	private function PDO4You() {}


	/**
	 * Método singleton de conexão
	 * 
	 * @access public static
	 * @param string $driver Driver dsn de conexão
	 * @param string $user Usuário do banco
	 * @param string $pass Senha do banco
	 * @param string $option Opções do driver PDO
	 * @return void
	 * @throws Exception Gerando excessão em caso de erro
	 *
	 * */
	public static function singleton($driver, $user, $pass, array $option = NULL) {
		try {
			$option = ( !is_null($option) ) ? $option : array(
				PDO::ATTR_PERSISTENT => true, 
				PDO::ATTR_CASE => PDO::CASE_LOWER 
			);
			self::$instance = new PDO($driver, $user, $pass, $option);
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, ($_SERVER['SERVER_ADDR'] == '127.0.0.1') ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT);
		} catch(PDOException $e) {
			if(defined('self::WEBMASTER')) self::pdo_fire_alert('Foi detectado um erro fatal no sistema!', $e);
			exit ( 'Houve falhas na conexão com o banco de dados.<br /><b>Acesse novamente em outro momento!</b>' );
		}
	}


	/**
	 * Método para retornar uma única instância da conexão
	 * 
	 * @access public
	 * @return object Retorna o objeto PDO
	 *
	 * */
	public static function getInstance() {
		if (!self::$instance) {
			self::singleton('mysql:host='.self::DATA_HOST.';port='.self::DATA_PORT.';dbname='.self::DATA_BASE, self::DATA_USER, self::DATA_PASS);
		}
		return self::$instance;
 	}


	/**
	 * Método para inserir um novo registro no banco de dados
	 * 
	 * @access public
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string Retorna a Id da última inserção
	 *
	 * */
	public static function insert($table, $array) {
		$pdo = self::getInstance();
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
			if(defined('self::WEBMASTER')) self::pdo_fire_alert('Foi detectado um erro crítico no sistema!', $e);
			echo 'Erro: '.$e->getMessage().'<br /><br />';
			$pdo->rollback();
		}
		$pdo->commit();
		$pdo = null;
		return $lastInsertId;
	}


	/**
	 * Método para atualizar os dados do registro
	 * 
	 * @access public
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string Retorna o número de linhas afetadas
	 *
	 * */
	public static function update($table, $array) {
		$pdo = self::getInstance();
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
			if(defined('self::WEBMASTER')) self::pdo_fire_alert('Foi detectado um erro crítico no sistema!', $e);
			echo 'Erro: '.$e->getMessage().'<br /><br />';
			$pdo->rollback();
		}
		$pdo->commit();
		$pdo = null;
		return $rowCount;
	}


	/**
	 * Método para excluir o registro
	 * 
	 * @access public
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string Retorna o número de linhas afetadas
	 *
	 * */
	public static function delete($table, $array) {
		$pdo = self::getInstance();
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
			if(defined('self::WEBMASTER')) self::pdo_fire_alert('Foi detectado um erro crítico no sistema!', $e);
			echo 'Erro: '.$e->getMessage().'<br /><br />';
			$pdo->rollback();
		}
		$pdo->commit();
		$pdo = null;
		return $rowCount;
	}


	/**
	 * Assim como o construtor, tornamos __clone privado para que ninguém possa clonar a instância da classe 
	 * @access private
	 *
	 * */
	private function __clone() {}


	public function beginTransaction() {
		if ( !self::getInstance()->beginTransaction() )
			throw new Error( self::getInstance()->errorInfo() );
	}

	public function commit() {
		if ( !self::getInstance()->commit() )
			throw new Error( self::getInstance()->errorInfo() );
	}

	public function exec($query) {
		if ( !self::getInstance()->exec($query) )
			throw new Error( self::getInstance()->errorInfo() );
	}

	public function query($query) {
		if ( !self::getInstance()->query($query) ) 
			throw new Error( self::getInstance()->errorInfo() );
	}

	public function rollBack() {
		if ( !self::getInstance()->rollBack() )
			throw new Error( self::getInstance()->errorInfo() );
	}

	public function lastInsertId($name) {
		if ( !self::getInstance()->lastInsertId($name) )
			throw new Error( self::getInstance()->errorInfo() );
	}


}


?>