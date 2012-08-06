<?php

/**
 * Wrapper para carregar os arquivos necessсrios da classe PDO4You
 * 
 * */

// Simples funчуo lambda para exigir arquivos
function requires(array $var)
{
    array_walk($var, create_function('$x', 'require_once("pdo4you/$x");'));
}

requires(array('PDO4You.config.php', 'PDO4You.library.php', 'PDO4You.class.php'));

?>