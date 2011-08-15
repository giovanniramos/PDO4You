<?php

// Carregando todas as classes necessárias
require_once("../PDO4You.load.php");


// Criando uma instância da classe de Test
$demo = new Test();


// Exibindo registros da base de dados: bookstore
$demo->showMethodSelect("bookstore");

// Exibindo registros da base de dados: pdo4you
$demo->showMethodSelect("pdo4you");

// Exibindo registros da última instância de dados definida
$demo->showMethodSelect();

// Exibindo registros da base de dados: bookstore
$demo->showMethodSelect("bookstore");


// Exibindo registros entre instâncias de base distintas 
$demo->showAllMethodsOfSelect();

?>