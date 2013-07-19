*[Read the documentation in English](https://github.com/giovanniramos/PDO4YOU/blob/master/README.md)*

---

PDO4You
==================================================

Esta classe é baseada no PDO, que é uma extensão do PHP que permite aos desenvolvedores criar um código portável, de modo a atender a maioria das bases de dados mais populares.
Sendo o MySQL, PostgreSQL, SQLite, Oracle, Microsoft SQL Server, Sybase.

Alternativamente foi adicionado na versão 3.0 suporte para o banco de dados MariaDB.
MariaDB está sendo considerado o futuro substituto livre do MySQL.
Mais informações em: http://bit.ly/MARIADB

E desde a versão 2.6 também tem prestado suporte para o banco de dados CUBRID.
Um sistema de gerenciamento de banco de dados altamente otimizado para aplicações Web.
Mais informações em: http://bit.ly/CUBRID

O PDO4You provê uma camada abstrata de acesso a dados, que independentemente de qual base de dados você esteja utilizando, sempre poderá usar os mesmos métodos para emitir consultas e buscar dados.

O padrão de projeto Singleton foi adotado para otimizar a conexão, garantindo uma única instância do objeto de conexão.


**Vantagens em sua utilização:**
* Instrução SQL compacta, usando notação JSON
* Abstração de conexão
* Proteção contra SQL Injection
* Múltiplas conexões por base de dados
* Métodos/Comandos CRUD pré-definidos
* Opção de se conectar com VCAP_SERVICES
* Tratamento de erros com Stack Trace



Carregando o arquivo de inicialização
--------------------------------------------------

~~~ php
<?php

// Apenas um arquivo é necessário pelo carregamento dos arquivos responsáveis pelo funcionamento da classe.
require_once('bootstrap.php');

?>
~~~ 

`PDO4You.class.php`: contém a implementação do objeto PDO de conexão.

`PDO4You.config.php`: arquivo de configuração inicial, de acesso ao servidor e base de dados.

`PDO4You.library.php`: arquivo contendo um autoloading de classes, e funções internas da classe.

`PDO4You.settings.ini`: contém as definições para cada adaptador de conexão com uma base de dados.



Drivers suportados pelo servidor
--------------------------------------------------

Execute o método abaixo para verificar se o servidor tem suporte a um driver PDO específico de sua base de dados. 
Os drivers suportados serão exibidos na tela.

~~~ php
<?php

// O método abaixo exibe todos os drivers instalados e que são suportados pelo servidor
PDO4You::getAvailableDrivers();

?>
~~~

Para habilitar algum driver não instalado, localize o arquivo php.ini, abra e procure por "extension=" sem as aspas, depois descomente as linhas a seguir conforme sua base de dados de preferência, removendo no início de cada linha o "ponto-e-vírgula" e após mudanças, reinicie o servidor.

~~~ html
;extension=php_pdo.dll                  ; Esta DLL não será necessária a partir do PHP 5.3
extension=php_pdo_mysql.dll             ; MySQL 3.x/4.x/5.x / MariaDB
extension=php_pdo_pgsql.dll             ; PostgreSQL
;extension=php_pdo_cubrid.dll           ; CUBRID
;extension=php_pdo_oci.dll              ; Oracle Call Interface
;extension=php_pdo_sqlsrv.dll           ; Microsoft SQL Server / SQL Azure
;extension=php_pdo_dblib.dll            ; Microsoft SQL Server / Sybase / FreeTDS
;extension=php_pdo_mssql.dll            ; Microsoft SQL Server "Versão antiga"
;extension=php_pdo_sqlite.dll           ; SQLite 2/3

~~~

Drivers PDO para o servidor Xampp:<br />
CUBRID (PHP 5.4): http://bit.ly/PDO_CUBRID-PHP54<br />
CUBRID (PHP 5.3): http://bit.ly/PDO_CUBRID-PHP53<br />
MS SQL Server 3.0 (PHP 5.4): http://bit.ly/PDO_SQLSRV-PHP54<br />
MS SQL Server 2.0 (PHP 5.2/5.3): http://bit.ly/PDO_SQLSRV-PHP53<br />
MS SQL Server (Versão antiga): http://bit.ly/PDO_MSSQL-PHP53


Estabelecendo uma conexão com a base de dados
--------------------------------------------------

Para abstrair nossos mecanismos de acesso aos dados, usamos um DSN (Data Source Name = Nome de Fonte de Dados) que armazena as informações necessárias para se iniciar uma comunicação com outras fontes de dados, tais como: tipo de tecnologia, nome do servidor ou localização, nome da base de dados, usuário, senha e outras configurações adicionais. Isso facilita a troca de acesso à base de dados que sofrerem migração.

~~~ php
<?php

// Principais meios de se iniciar uma instância de conexão

# PADRÃO 
PDO4You::getInstance(); // Os dados de acesso já foram definidos no arquivo de configuração inicial


// Conectando-se a outras fontes de dados através de um DSN

# MySQL / MariaDB
PDO4You::getInstance('nome_da_instancia', 'mysql:host=localhost;dbname=pdo4you;port=3306', 'usuario', 'senha');

# PostgreSQL
PDO4You::getInstance('nome_da_instancia', 'pgsql:host=localhost;dbname=pdo4you;port=5432', 'usuario', 'senha');

# CUBRID
PDO4You::getInstance('nome_da_instancia', 'cubrid:host=localhost;dbname=pdo4you;port=33000', 'usuario', 'senha');

?>
~~~ 



Realizando operações CRUD em sua base de dados
--------------------------------------------------

O termo CRUD em inglês se refere as 4 operações básicas em uma base de dados e significam: 
Create(INSERT), Retrieve(SELECT), Update(UPDATE) e Destroy(DELETE)

Instruções de consulta:

`PDO4You::select()`: retorna um array indexado pelo nome da coluna.

`PDO4You::selectNum()`: retorna um array indexado pela posição numérica da coluna.

`PDO4You::selectObj()`: retorna um objeto com nomes de coluna como propriedades.

`PDO4You::selectAll()`: retorna um array indexado pelo nome e pela posição numérica da coluna.


Abaixo seguem exemplos de como realizar essas operações.


Selecionando registros na base de dados
--------------------------------------------------

~~~ php
<?php

// Iniciando uma instância de conexão. O padrão de conexão é não-persistente
PDO4You::getInstance();

// Definindo uma comunicação persistente com a base de dados
PDO4You::setPersistent(true);

// Selecionando registros na base de dados
PDO4You::select('SELECT * FROM books LIMIT 2');

// Selecionando registros e definindo qual instância de conexão será utilizada
PDO4You::select('SELECT * FROM books LIMIT 2', 'nome_da_instancia');


// Instrução de consulta
$sql = 'SELECT * FROM books LIMIT 2';

// Selecionando registros com PDO::FETCH_ASSOC
$result = PDO4You::select($sql);

// Selecionando registros com PDO::FETCH_NUM
$result = PDO4You::selectNum($sql);

// Selecionando registros com PDO::FETCH_OBJ
$result = PDO4You::selectObj($sql);

// Selecionando registros com PDO::FETCH_BOTH
$result = PDO4You::selectAll($sql);


// Selecionando todos os registros
$result = PDO4You::select('SELECT * FROM books');

// Obtendo o total de linhas afetadas pela operação
$total = PDO4You::rowCount();

// Exibindo o resultado da consulta
echo '<pre><h3>Resultado da consulta:</h3> ' , print_r($result, true) , '</pre>';

?>
~~~ 



Os métodos insert(), update() e delete() da classe PDO4You estão aninhadas entre transações, sendo elas beginTransaction() e commit(). Isto garante que o sistema consiga reverter uma operação mal sucedida e todas as alterações feitas desde o início de uma transação.

Foi adicionado na versão 3.1 o método execute(), como uma alternativa aos métodos (insert, update and delete).

Um erro grave na execução resulta em invocar o rollBack(), desfazendo toda a operação. Consequentemente será lançada uma Exception, rastreando o caminho de todas as classes e métodos envolvidos na operação, agilizando em ambiente de "produção" o processo de debug e com isso, assegurando a base de dados do risco de se tornar instável.

No MySQL, o suporte a transações está disponível em tabelas do tipo InnoDB.

As instruções SQL da classe PDO4You (insert, update e delete) fazem agora o uso de notação JSON, um novo formato de se escrever querys que por sua vez possui convenções muito semelhante às linguagens como Python, Ruby, C++, Java, JavaScript. A nova sintaxe adotada pela classe é bem mais bonita e concisa, que a usada por Arrays. Além de compacta, as instruções possuem a capacidade de operar ao mesmo tempo, em diferentes tabelas da mesma base de dados. 


Abaixo seguem trechos de exemplo na prática.



Inserindo um simples registro na base de dados
--------------------------------------------------

~~~ php
<?php

// SQL insert no formato JSON
$json = '
	insert : [
		{
			table: "users" ,
			values: { mail: "pdo4you@gmail.com" }
		}
	] 
';

// A variável $result armazena como retorno do método, um array com o número de linhas afetadas por operação de inserção
$result = PDO4You::execute($json);

// Logo após a inserção, utilize o método PDO4You::lastId() para obter o ID da última operação de inserção na base de dados
$lastInsertId = PDO4You::lastId();

// Se necessário, informe o nome da variável de sequência, solicitado em algumas base de dados
$lastInsertId = PDO4You::lastId('table_id_seq');

?>
~~~ 



Inserindo múltiplos registros
--------------------------------------------------

~~~ php
<?php

// SQL insert no formato JSON
$json = '
	insert : [
		{
			table: "users" ,
			values: { mail: "mail_1@domain.com" }
		},{
			table: "users" ,
			values: { mail: "mail_2@domain.com" }
		},{
			table: "books" ,
			values: { title: "title", author: "author" }
		}
	] 
';

// A variável $result armazena um array com o número de linhas afetadas por operação de inserção
$result = PDO4You::execute($json);

?>
~~~ 



Atualizando múltiplos registros
--------------------------------------------------

~~~ php
<?php

// SQL update no formato JSON
$json = '
	update : [
		{
			table: "users" ,
			values: { mail: "mail_1@domain.com" } ,
			where: { id: 2 }
		},{
			table: "users" ,
			values: { mail: "mail_2@domain.com" } ,
			where: { id: 3 }
		},{
			table: "books" ,
			values: { title: "new-title", author: "new-author" } ,
			where: { id: 1 }
		}
	] 
';

// A variável $result armazena um array com o número de linhas afetadas por operação de atualização
$result = PDO4You::execute($json);

?>
~~~ 



Excluindo múltiplos registros
--------------------------------------------------

~~~ php
<?php

// SQL delete no formato JSON
$json = '
	delete : [
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
';

// A variável $result armazena um array com o número de linhas afetadas por operação de exclusão
$result = PDO4You::execute($json);

?>
~~~ 



Requerimentos
--------------------------------------------------

PHP 5.3 ou superior



Colaboradores
--------------------------------------------------

Giovanni Ramos - <giovannilauro@gmail.com> - <http://twitter.com/giovanni_ramos><br />
Veja também a lista de [colaboradores](http://github.com/giovanniramos/PDO4YOU/contributors) que participaram deste projeto.



Licença
--------------------------------------------------

Copyright (c) 2013 [Giovanni Ramos](http://github.com/giovanniramos)

PDO4You é um software open-source licenciado sob a [MIT License](http://www.opensource.org/licenses/MIT) (MIT-LICENSE.txt)