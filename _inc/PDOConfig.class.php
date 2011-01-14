<?php

/**
 * Interface de configuração do servidor
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @twitter @giovanni_ramos
 * @version 1.0
 * @since 2010-09-07 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package PDO4You 
 * 
 * */
 

interface PDOConfig {

	// Habilita o envio de alertas para o Webmaster
	const FIREALERT = false;

	// Email do Webmaster que irá receber as notificações
	const WEBMASTER = 'giovannilauro@gmail.com';

	// Configuração do driver dsn
	const DATA_HOST = 'localhost'; 
	const DATA_PORT = '3306';
	const DATA_USER = 'root';
	const DATA_PASS = 'vertrigo';
	const DATA_BASE = 'pdo4you';

}


/**
 * Biblioteca de funções PDO
 * 
 * */
class PDOLibrary implements PDOConfig {

	/**
	 * Dispara um alerta em caso de falha de comunicação, para o e-mail do Webmaster
	 * 
	 * @access public
	 * @param string $message Mensagem de erro
	 * @param object $errorInfo Objeto contendo o diagnóstico do erro
	 * @return void
	 * 
	 * */
	public static function pdo_fire_alert($message, $errorInfo){
		$head = 'MIME-Version: 1.1'.PHP_EOL;
		$head.= 'Content-type: text/html; charset=iso-8859-1'.PHP_EOL;
		$head.= 'From: Alerta automático <noreply@noreply.com>'.PHP_EOL;
		$head.= 'Return-Path: Alerta automático <noreply@noreply.com>'.PHP_EOL;
		$body = 'Diagnóstico do alerta:<br /><br /><b>'.$errorInfo->getMessage().'</b><br />'.$errorInfo->getFile().' : '.$errorInfo->getLine();

		if(self::FIREALERT) @mail(self::WEBMASTER, $message, $body, $head);
	}


	/**
	 * Método para unir os elementos de uma matriz para inserção de dados
	 * 
	 * @access public
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string
	 * 
	 * */
	public static function pdo_implode_insert($table, $array) {
		if(empty($array)) return null;
		$str = 'INSERT INTO '.$table.' ('; 
		foreach($array as $k => $v)
	 	$str.= ', '.$k;
	 	$str = preg_replace('/, /', '', $str, 1);
	 	$str.= ') VALUES (';
		foreach($array as $k => $v)
	 	$str.= ', :'.$k;
	 	$str.= ')';
	 	$str = preg_replace('/, :/', ':', $str, 1);
	 	return $str;
	}


	/**
	 * Método para unir os elementos de uma matriz para atualização de dados
	 * 
	 * @access public
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string
	 * 
	 * */
	public static function pdo_implode_update($table, $array) {
		if(empty($array)) return null;
		$str = 'UPDATE '.$table.' SET ';
		foreach($array[1] as $k => $v)
	 	$str.= ', '.$k.'=:'.$k;
		$str = preg_replace( '/, /', '', $str, 1);
		$str.= ' WHERE ';
		foreach($array[0] as $k => $v)
	 	$str.= ' AND '.$k.'=:'.$k;
	 	$str = preg_replace('/ AND /', '', $str, 1);
		return $str;
	}


	/**
	 * Método para unir os elementos de uma matriz para exclusão do registro
	 * 
	 * @access public
	 * @param string $table Nome da tabela
	 * @param array $array Matriz de valores
	 * @return string
	 * 
	 * */
	public static function pdo_implode_delete($table, $array) {
		if(empty($array)) return null;
		$str = 'DELETE FROM '.$table.' WHERE ';
		foreach($array as $k => $v)
	 	$str.= ' AND '.$k.'=:'.$k;
		$str = preg_replace('/ AND /', '', $str, 1);
		return $str;
	}

}

?>