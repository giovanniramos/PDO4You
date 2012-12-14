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

// Adaptador de conexão definido em: \pdo4you\settings.ini
// Ex.: mysql, pgsql, pgsql.dev, pgsql.pro, test
define('PDO4YOU_ADAPTER', 'test');

// Depuração de erros com tratamento de exceção
define('PDO4YOU_FIREDEBUG', true);

// Monitoramento de erros com o envio de notificações via e-mail ao administrador do sistema
define('PDO4YOU_FIREALERT', false);

// E-mail do administrador do sistema para recebimento das notificações
define('PDO4YOU_WEBMASTER', 'pdo4you@gmail.com');

