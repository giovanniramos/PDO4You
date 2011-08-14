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
require_once('../PDOYou.load.php');


// Iniciando a instância de uma conexão
PDO4You::getInstance();


// Exibe informações sobre o banco de dados
PDO4You::getServerInfo();


// Teste de instrução: INSERT
$data['insert'] = Teste::executeInsert();

// Teste de instrução: UPDATE
$data['update'] = Teste::executeUpdate();

// Teste de instrução: DELETE
$data['delete'] = Teste::executeDelete();

// Imprimindo o resultado 
echo "<pre><h3>Resultado dos testes:</h3> ",print_r($data, true),"</pre>";

?>