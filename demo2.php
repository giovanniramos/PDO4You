<?php

/**
 * Configuração de testes
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright Giovanni Ramos
 * @since 2011-07-27
 * @version 1.0
 * 
 * */


// Invocamos o arquivo de configuração 
require_once("PDOConfig.class.php");

// Invocamos a classe PDO4You
require_once("PDO4You.class.php");

// Invocamos a biblioteca de funções
require_once("PDOLibrary.class.php");



// Iniciando a instância de uma conexão
PDO4You::getInstance('bookstore');


// Exibe todos os drivers disponíveis
PDO4You::getAvailableDrivers();



$sql = "SELECT * FROM books ORDER BY title";

//PDO4You::showQuery($sql);
PDO4You::select($sql);
PDO4You::showTables();

?>