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
PDO4You::getInstance();


// Exibe informações sobre o banco de dados
PDO4You::getServerInfo();


// Teste de consulta padrão
$data['select1'] = Teste::executeSelect1();

// Teste de consulta aplicando outra instância de conexão
$data['select2'] = Teste::executeSelect2();

//Teste de consulta, alternando entre instâncias de conexão
$data['select3'] = Teste::executeSelect3();

// Imprimindo o resultado 
echo "<pre><h3>Resultado dos testes:</h3> ",print_r($data, true),"</pre>";


?>