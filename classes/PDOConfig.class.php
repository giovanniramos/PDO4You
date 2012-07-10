<?php

/**
 * Interface de configuração do servidor
 * 
 * PHP version 5
 * 
 * @category PHP
 * @package PDO4You
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright Giovanni Ramos
 * @since 2010-09-07
 * @version 2.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link https://github.com/giovanniramos/PDO4YOU
 * 
 * */
interface PDOConfig
{
    // Configuração de conexão
    const DATA_HOST = 'localhost';
    const DATA_PORT = '3306';
    const DATA_USER = 'root';
    const DATA_PASS = '';
    const DATA_BASE = 'pdo4you';

    // FireDebug: Habilita o debug com tratamento de exceções
    const FIREDEBUG = true;

    // FireAlert: Habilita o monitoramento de erros no sistema, enviando alertas via e-mail para o Administrador
    const FIREALERT = false;

    // E-mail do Administrador do sistema para receber as notificações do FireAlert
    const WEBMASTER = 'username@gmail.com';

}

class PDOFatalError extends Exception { }

class Config implements PDOConfig
{
    public function Config() { }
}
