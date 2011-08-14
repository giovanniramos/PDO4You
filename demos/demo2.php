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

//Load of all necessary classes
require_once('../classes/PDO4You.load.php');

// Iniciando a instância de uma conexão
PDO4You::getInstance('bookstore');


// Exibe todos os drivers disponíveis
PDO4You::getAvailableDrivers();



$sql = "SELECT * FROM books ORDER BY title";

//PDO4You::showQuery($sql);
PDO4You::select($sql);
PDO4You::showTables();

?>