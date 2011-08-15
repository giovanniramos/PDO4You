<?php

// Carregando todas as classes necessárias
require_once("../PDO4You.load.php");


// Criando uma instância da classe de Test
$demo = new Test();


// Inserindo registros
$demo->executeInsert();

// Atualizando registros
$demo->executeUpdate();

// Excluindo registros
$demo->executeDelete();


?>