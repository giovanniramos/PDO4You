# PDO4You

PDO é uma extensão do PHP, que permite aos desenvolvedores criar um código portável, de modo a atender a maioria dos bancos de dados mais populares. 
Sendo o MySQL, PostgreSQL, Oracle, SQLite.


Vantagens no uso da classe:
==========
* Abstração de conexão
* Proteção contra SQL Injection
* Instrução SQL compacta, usando notação JSON
* Múltiplas instâncias de conexão com banco de dados
* Controle e tratamento de exceções com stack trace


Verificando os drivers suportados pelo servidor
-------------------------------------
Para verificar se o seu servidor tem suporte a um driver PDO de seu banco de dados, execute o seguinte método.

~~~ php
<?php

// O método getAvailableDrivers(), exibe todos os drivers instalados e que são suportados pelo servidor
PDO4You::getAvailableDrivers();

?>
~~~

O PDO provê uma camada abstrata de acesso a dados, que independentemente de qual banco de dados você esteja usando, você poderá usar as mesmas funções para emitir consultas e buscar dados.



O padrão de projeto Singleton otimiza a conexão, garantido uma única instância do objeto de conexão.

Carregando a interface, a classe PDO4You de conexão e o autoloader
----------------------
~~~ php
<?php

// Nessa ordem são carregados
require_once("PDOConfig.class.php");
require_once("PDO4You.class.php");
require_once("PDOLibrary.class.php");

?>
~~~ 

`PDOConfig.class.php`: contém a interface de configuração do servidor.

`PDO4You.class.php`: possue a implementação da classe PDO4You de conexão singleton, baseada na extensão PDO.

`PDOLibrary.class.php`: possue um autoloading de classes e será uma biblioteca de funções


DSN ou Data Source Name, contém as informações necessárias para se iniciar a comunicação com um banco de dados.



Conectando ao banco de dados. DSN (Opcional)
------------------------------------------------
~~~ php
<?php

// Formas de se iniciar uma instância de conexão 
PDO4You::getInstance();
PDO4You::getInstance('database');
PDO4You::getInstance('database', 'mysql:host=localhost;port=3306;');
PDO4You::getInstance('database', 'mysql:host=localhost;port=3306;', 'root', 'pass');

?>
~~~ 




O termo CRUD em inglês se refere as 4 operações básicas do banco de dados e significam: 
Create(INSERT), Retrieve(SELECT), Update(UPDATE) e Destroy(DELETE)

Abaixo segue um exemplo de como realizar operações CRUD no banco de dados.

~~~ php
<?php

// Iniciando uma instância de conexão. Por default, a conexão iniciada será persistente.
PDO4You::getInstance();

// Para definir o tipo de comunicação com o banco de dados, utilize o método abaixo passando um valor booleano.
PDO4You::setPersistent(false);

// Selecionando registros no banco de dados
PDO4You::select('SELECT * FROM books LIMIT 2');

// Selecionando registros e definindo a instância de banco que será utilizada
PDO4You::select('SELECT * FROM books LIMIT 2', 'bookstore');


// Query de consulta
$sql = 'SELECT * FROM books LIMIT 2';

// Obtendo registros como um array indexado pelo nome da coluna. Equivale a FETCH_ASSOC
$result = PDO4You::select($sql);

// Obtendo registros como um array indexado pelo número da coluna. Equivale a FETCH_NUM
$result = PDO4You::selectNum($sql);

// Obtendo registros como um objeto com nomes de coluna como propriedades. Equivale a FETCH_OBJ
$result = PDO4You::selectObj($sql);

// Obtendo registros como um array indexado tanto pelo nome como pelo número da coluna. Equivale a FETCH_BOTH
$result = PDO4You::selectAll($sql);

// Obtendo o total de registros afetados.
$result = PDO4You::rowCount($sql);


// Imprimindo o resultado 
echo "<pre><h3>Resultado:</h3> ",print_r($result, true),"</pre>";

?>
~~~ 


Os métodos insert, update e delete da classe PDO4You estão aninhadas entre transações, sendo elas beginTransaction() e commit(). 
Isto garante que o sistema consiga reverter uma operação mal sucedida e todas as alterações feitas desde o início da transação, 
assegurando o banco dados do risco de instabilidade, e dessa forma lançando uma exceção para análise.

As instruções de SQL ( insert, update e delete ), possuem a capacidade de operar ao mesmo tempo, em tabelas distintas do mesmo banco de dados


Inserindo múltiplos registros no banco de dados
---------------------
~~~ php
<?php

$sql = '
{
	query : [
		{
			table: "users" ,
			values: { mail: "email1@gmail.com" }
		},{
			table: "users" ,
			values: { mail: "email2@gmail.com" }
		},{
			table: "books" ,
			values: { title: "titulo", author: "autor" }
		}
	] 
}
';
$result = PDO4You::insert($sql);

?>
~~~ 


Atualizando múltiplos registros
---------------------
~~~ php
<?php

$sql = '
{
	query : [
		{
			table: "users" ,
			values: { mail: "novo-email1@gmail.com" } ,
			where: { id: 2 }
		},{
			table: "users" ,
			values: { mail: "novo-email2@gmail.com" } ,
			where: { id: 3 }
		},{
			table: "books" ,
			values: { title: "novo-titulo", author: "novo-autor" } ,
			where: { id: 1 }
		}
	] 
}
';
$result = PDO4You::update($sql);

?>
~~~ 


Excluindo múltiplos registros
---------------------
~~~ php
<?php

$sql = '
{
	query : [
		{
			table: "users" ,
			where: { id: 2 }
		},{
			table: "users" ,
			where: { id: 5 }
		},{
			table: "users" ,
			where: { id: 10 }
		},{
			table: "books" ,
			where: { id: 10 }
		}
	] 
}
';
$result = PDO4You::delete($sql);

?>
~~~ 
