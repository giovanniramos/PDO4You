<?php

/**
 * Interface de configuração do servidor
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright Giovanni Ramos
 * @twitter giovanni_ramos
 * @since 2010-09-07
 * @version 2.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package PDO4You
 * 
 * */


interface PDOConfig 
{
	// Configuração de conexão
	const DATA_HOST = 'localhost';
	const DATA_PORT = '3306';
	const DATA_USER = 'admin';
	const DATA_PASS = '123123';
	const DATA_BASE = 'pdo4you';
	
	// FireDebug: Habilita o debug com tratamento de exceções
	const FIREDEBUG = true;
	
	// FireAlert: Habilita o monitoramento de erros no sistema, enviando alertas via e-mail para o Administrador
	const FIREALERT = false;
	
	// E-mail do administrador do sistema para receber as notificações do FireAlert
	const WEBMASTER = 'giovannilauro@gmail.com';
}

class PDOFatalError extends Exception { }

class Config implements PDOConfig
{
	public function Config() { }
}

?>