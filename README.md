# PDO4You

Esta classe é baseada no PDO, que é uma extensão do PHP que permite aos desenvolvedores criar um código portável, de modo a atender a maioria das bases de dados mais populares. 
Sendo o MySQL, PostgreSQL, MS SQL Server, Sybase, Oracle.

O PDO4You provê uma camada abstrata de acesso a dados, que independentemente de qual base de dados você esteja utilizando, sempre poderá usar os mesmos métodos para emitir consultas e buscar dados.

O padrão de projeto Singleton foi adotado para otimizar a conexão, garantindo uma única instância do objeto de conexão por base de dados.


Vantagens no uso da classe:
==========
* Abstração de conexão
* Proteção contra SQL Injection
* Métodos CRUD pré-definidos
* Múltiplas instâncias de conexão distinta
* Instrução SQL compacta, usando notação JSON
* Tratamento de erros com Stack Trace



Verificando os drivers suportados pelo servidor
-------------------------------------
Para verificar se o seu servidor tem suporte a um driver PDO de sua base de dados, execute o seguinte método.

~~~ php
<?php

// O método getAvailableDrivers(), exibe todos os drivers instalados e que são suportados pelo servidor
PDO4You::getAvailableDrivers();

?>
~~~

Para habilitar os drivers basta editar o arquivo php.ini e descomentar as seguintes linhas:

~~~ html
extension=php_pdo.dll
extension=php_pdo_mysql.dll
extension=php_pdo_pgsql.dll
extension=php_pdo_mssql.dll
extension=php_pdo_oci.dll
extension=php_pdo_oci8.dll
extension=php_pdo_sqlite.dll
~~~


Carregando a interface, a classe PDO4You de conexão e o autoloader
----------------------
~~~ php
<?php

// Os principais arquivos nessa ordem são carregados
require_once("PDOConfig.class.php");
require_once("PDO4You.class.php");
require_once("PDOLibrary.class.php");

?>
~~~ 

`PDOConfig.class.php`: contém a interface de configuração do servidor.

`PDO4You.class.php`: possui a implementação do objeto PDO4You de conexão Singleton, estendendo a extensão PDO.

`PDOLibrary.class.php`: possui um autoloading de classes e pode ser usado como biblioteca de funções do sistema.


Um DSN ou Data Source Name (Nome de Fonte de Dados), armazena as informações necessárias para se iniciar uma comunicação com outras fontes de dados, tais como: tipo de tecnologia, nome do servidor ou localização, nome da base de dados, usuário, senha  e outras configurações adicionais. Isso facilita a troca de acesso a base de dados que sofrerem migração.



Conectando-se a uma base de dados
------------------------------------------------
~~~ php
<?php

// Principais formas de se iniciar uma instância de conexão. O uso de um DSN é opcional.

# MySQL 
PDO4You::getInstance(); // Default
PDO4You::getInstance('database');
PDO4You::getInstance('database', 'mysql:host=localhost;');

# MySQL
PDO4You::getInstance('database', 'mysql:host=localhost;port=3306;', 'root', 'pass');

# PgSQL
PDO4You::getInstance('database', 'pgsql:host=localhost;', 'root', 'pass');

# MS SQL Server
PDO4You::getInstance('database', 'mssql:host=localhost;', 'root', 'pass');

# Sybase  
PDO4You::getInstance('database', 'sybase:host=localhost;', 'root', 'pass');

# Oracle
PDO4You::getInstance('database', 'OCI:dbname=database;charset=UTF-8', 'root', 'pass');

?>
~~~ 



Realizando consultas na base de dados
---------------------

O termo CRUD em inglês se refere as 4 operações básicas em uma base de dados e significam: 
Create(INSERT), Retrieve(SELECT), Update(UPDATE) e Destroy(DELETE)

Instruções SQL de consulta:

`PDO4You::select()`: Obtém registros como um array indexado pelo nome da coluna. Equivale a PDO::FETCH_ASSOC

`PDO4You::selectNum()`: Obtém registros como um array indexado pelo número da coluna. Equivale a PDO::FETCH_NUM

`PDO4You::selectObj()`: Obtém registros como um objeto com nomes de coluna como propriedades. Equivale a PDO::FETCH_OBJ

`PDO4You::selectAll()`: Obtém registros como um array indexado tanto pelo nome como pelo número da coluna. Equivale a PDO::FETCH_BOTH

`PDO4You::rowCount()`: Obtém o total de registros afetados em uma operação de SELECT.

Nota: Em determinadas base de dados, o rowCount() com SELECT pode retornar o número de linhas afetadas pela instrução. No entanto, este comportamento não é garantido.


Abaixo segue exemplos de como realizar estas operações do PDO4You em sua base de dados.

~~~ php
<?php

// Iniciando uma instância de conexão. O padrão de conexão é persistente.
PDO4You::getInstance();

// Para definir um tipo de comunicação persistente ou não-persistente, utilize o método abaixo passando um valor booleano.
PDO4You::setPersistent(false);

// Selecionando registros na base de dados
PDO4You::select('SELECT * FROM books LIMIT 2');

// Selecionando registros e definindo qual instância de base de dados será utilizada
PDO4You::select('SELECT * FROM books LIMIT 2', 'bookstore');


// Query de consulta
$sql = 'SELECT * FROM books LIMIT 2';

// Selecionando registros com FETCH_ASSOC
$result = PDO4You::select($sql);

// Selecionando registros com FETCH_NUM
$result = PDO4You::selectNum($sql);

// Selecionando registros com FETCH_OBJ
$result = PDO4You::selectObj($sql);

// Selecionando registros com FETCH_BOTH
$result = PDO4You::selectAll($sql);

// Selecionando o total de registros
$result = PDO4You::rowCount($sql);


// Imprimindo o resultado 
echo "<pre><h3>Resultado:</h3> ",print_r($result, true),"</pre>";

?>
~~~ 


Os métodos insert(), update() e delete() da classe PDO4You estão aninhadas entre transações, sendo elas beginTransaction() e commit().
Isto garante que o sistema consiga reverter uma operação mal sucedida e todas as alterações feitas desde o início da transação, 
assegurando a base de dados do risco de instabilidade, e quando isso ocorrer, um rollBack() será invocado e uma exceção será lançada rastreando o caminho de todas as classes e métodos envolvidas na operação, agilizando o debug em produção.
No MySQL o suporte a transações está disponível em tabelas do tipo InnoDB.

As instruções de SQL do PDO4You (insert, update e delete), possuem a capacidade de operar ao mesmo tempo, em diferentes tabelas da mesma base de dados. Veja abaixo alguns exemplos.



Inserindo múltiplos registros na base de dados
---------------------
~~~ php
<?php

// SQL query
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

// A variável $result armazena como retorno do método, um array com o ID de cada operação de inserção
$result = PDO4You::insert($sql);

?>
~~~ 



Atualizando múltiplos registros
---------------------
~~~ php
<?php

// SQL query
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

// A variável $result armazena um array com o número de linhas afetadas por operação de atualização
$result = PDO4You::update($sql);

?>
~~~ 



Excluindo múltiplos registros
---------------------
~~~ php
<?php

// SQL query
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

// A variável $result armazena um array com o número de linhas afetadas por operação de exclusão
$result = PDO4You::delete($sql);

?>
~~~ 
