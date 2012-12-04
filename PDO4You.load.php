<?php

/**
 * Simples função lambda para exigir os arquivos necessários da classe PDO4You
 * 
 * */
function requires(array $var)
{
    array_walk($var, create_function('$x', 'require_once("pdo4you/$x");'));
}

requires(array('PDO4You.config.php', 'PDO4You.library.php', 'PDO4You.class.php'));

?>