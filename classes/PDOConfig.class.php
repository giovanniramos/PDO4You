<?php

/**
 * Interface de configuração do servidor
 * 
 * @category Config
 * @package PDO4You
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2012, Giovanni Ramos
 * @since 2010-09-07
 * @version 2.1
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/giovanniramos/PDO4You
 * 
 * */
interface PDOConfig
{
    // Configuração de conexão
    const DATA_TYPE = 'mysql';
    const DATA_HOST = 'localhost';
    const DATA_PORT = '3306';
    const DATA_USER = 'root';
    const DATA_PASS = '';
    const DATA_BASE = 'pdo4you';

    // FireDebug: Habilita o debug com tratamento de exceções
    const FIREDEBUG = true;

    // FireAlert: Habilita o monitoramento de erros no sistema, enviando alertas via e-mail para o Administrador
    const FIREALERT = false;

    // E-mail do administrador do sistema para receber as notificações do FireAlert
    const WEBMASTER = 'pdo4you@gmail.com';

}

class PDOFatalError extends Exception { }

class Config implements PDOConfig
{
    public function Config() { }
}
