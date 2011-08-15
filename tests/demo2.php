<?php

// Carregando todas as classes necessárias
require_once("../PDO4You.load.php");


// Iniciando a instância de uma conexão
PDO4You::getInstance('pdo4you');

// Exibe todos os drivers disponíveis
PDO4You::getAvailableDrivers();


$sql = "SELECT * FROM books ORDER BY title";
//PDO4You::showQuery($sql);
PDO4You::select($sql);
PDO4You::showTables();

?>