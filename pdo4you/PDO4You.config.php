<?php

/**
 * Configuração inicial
 * 
 * @category Config
 * @package PDO4You
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2012, Giovanni Ramos
 * @since 2010-09-07
 * @version 2.0
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/giovanniramos/PDO4You
 * 
 * */

// Dados de conexão
define('DATA_TYPE', 'mysql'); # Tipo de conexão: cubrid, mysql, pgsql, oracle, mssql, sybase, dblib, sqlsrv
define('DATA_HOST', 'localhost'); # Nome do servidor: localhost, 127.0.0.1
define('DATA_PORT', '3306'); # Porta do servidor: 3306, 5432, 33000
define('DATA_USER', 'root'); # Nome de usuário da base de dados
define('DATA_PASS', ''); # Senha da base de dados
define('DATA_BASE', 'pdo4you'); # O nome da base de dados

// Fire-Debug: Ativar depuração com tratamento de exceção
define('FIREDEBUG', true);

// Fire-Alert: Permite o monitoramento de erro do sistema envia alertas por e-mail para o administrador
define('FIREALERT', false);

// Define o e-mail do administrador do sistema para receber as notificações do Fire-Alert
define('WEBMASTER', 'pdo4you@gmail.com');

