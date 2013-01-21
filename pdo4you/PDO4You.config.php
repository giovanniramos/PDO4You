<?php

/**
 * Configuração inicial
 * 
 * @category Config
 * @package PDO4You
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2012, Giovanni Ramos
 * @since 2010-09-07
 * @version 2.7+
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/giovanniramos/PDO4You
 * 
 * */

// Adaptador de conexão definido em settings.ini (Ex.: test, mysql, pgsql, pgsql.dev, pgsql.pro, vcap)
define('ADAPTER', getenv('SERVER_NAME') == 'localhost' ? 'test' : 'vcap');

// Depuração de erros com tratamento de exceção
define('FIREDEBUG', true);

// Monitoramento de erros com o envio de notificações via e-mail ao administrador do sistema
define('FIREALERT', false);

// E-mail do administrador do sistema para recebimento das notificações
define('WEBMASTER', 'pdo4you@gmail.com');

