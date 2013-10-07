*[Leia a documenta&ccedil;&atilde;o em Portugu&ecirc;s](https://github.com/giovanniramos/PDO4You/blob/master/README-pt.md)*

---

PDO4You
==================================================

[![Latest Stable Version](https://poser.pugx.org/giovanniramos/pdo4you/v/stable.png)](https://packagist.org/packages/giovanniramos/pdo4you)
[![Build Status](https://travis-ci.org/giovanniramos/PDO4You.png?branch=master)](https://travis-ci.org/giovanniramos/PDO4You)


This class is based on the PDO, which is a PHP extension that allows developers to create portable code, so as to cater for most popular databases.
Being MySQL, PostgreSQL, SQLite, Oracle, Microsoft SQL Server, Sybase.

Alternatively been added in version 3.0 support for the MariaDB database.
MariaDB is being considered as the future replacement free of MySQL.
More information at: http://bit.ly/MARIADB

And since version 2.6 also has provided support for the CUBRID database.
A management system database highly optimized for Web applications.
More information at: http://bit.ly/CUBRID

The PDO4You provides an abstraction layer for data access, that regardless of which database you're using, you can always use the same methods to issue queries and fetch data.

The Singleton design pattern was adopted to optimize the connection, ensuring a single instance of the connection object.


**Advantages in their use:**
* Instruction SQL compact using JSON notation
* Abstraction of connection
* Protection against SQL Injection
* Multiple database connections
* Methods/Commands CRUD predefined
* Option to connect with VCAP_SERVICES
* Error Handling with Stack Trace



Getting Started
--------------------------------------------------

The bootstrap file is responsible for loading the autoloader and all project dependencies.
If not available, you will receive a confirmation message to start installation with Composer.

~~~ php
<?php

// Loads the autoloader and all project dependencies
require __DIR__.'/bootstrap.php';

?>
~~~ 

`PDO4You.php`: class that contains the implementation of the PDO object connection.

`PDO4You.config.php`: initial configuration file, server access and database.

`PDO4You.settings.ini`: contains the settings for each adapter of connection to the database.

`Describe.php`: Describe is a class used to list all the fields in a table and the data format of each field.

`Pagination.php`: Pagination is a class that allows you to list the records so as paged, similar to Google.



Establishing a connection to the database
--------------------------------------------------

To abstract our data access mechanisms, we use a DSN (Data Source Name = Data Source) that stores the information needed to initiate communication with other data sources, such as: type of technology, server name or location, database name, user, password, and other settings. This allows portability of the project, facilitating the exchange of access to the database during a migration.

~~~ php
<?php

// Load all the files needed
require __DIR__.'/bootstrap.php';

// Connection instance imported and available for use
use PDO4You\PDO4You;
new PDO4You;


// Main ways to start a connection instance

# DEFAULT 
PDO4You::getInstance(); // The data access have been defined in the initial configuration


// Connecting to other data sources through a DSN

# MySQL / MariaDB
PDO4You::getInstance('instance_name', 'mysql:host=localhost;dbname=pdo4you;port=3306', 'user', 'pass');

# PostgreSQL
PDO4You::getInstance('instance_name', 'pgsql:host=localhost;dbname=pdo4you;port=5432', 'user', 'pass');

# CUBRID
PDO4You::getInstance('instance_name', 'cubrid:host=localhost;dbname=pdo4you;port=33000', 'user', 'pass');

?>
~~~ 



Performing CRUD operations on your database
--------------------------------------------------

The term CRUD refers to the 4 basic operations in a database and meaning:
Create(INSERT), Retrieve(SELECT), Update(UPDATE) e Destroy(DELETE)

Query statements:

`PDO4You::select()`: returns an array indexed by column name.

`PDO4You::selectNum()`: returns an array indexed by the numerical position of the column.

`PDO4You::selectObj()`: returns an object with column names as properties.

`PDO4You::selectAll()`: returns an array indexed by name and numerical position of the column.


Below are examples of how to perform these operations.



Selecting records in the database
--------------------------------------------------

~~~ php
<?php

// Load all the files needed
require __DIR__.'/bootstrap.php';

// Connection instance imported and available for use
use PDO4You\PDO4You;
new PDO4You;

// Starting a connection instance. The default connection is not persistent
PDO4You::getInstance();

// Defining a persistent communication with the database
PDO4You::setPersistent(true);

// Selecting records in the database
PDO4You::select('SELECT * FROM books LIMIT 2');

// Selecting records and setting that connection instance will be used
PDO4You::select('SELECT * FROM books LIMIT 2', 'instance_name');


// Query statement
$sql = 'SELECT * FROM books LIMIT 2';

// Selecting records with PDO::FETCH_ASSOC
$result = PDO4You::select($sql);

// Selecting records with PDO::FETCH_NUM
$result = PDO4You::selectNum($sql);

// Selecting records with PDO::FETCH_OBJ
$result = PDO4You::selectObj($sql);

// Selecting records with PDO::FETCH_BOTH
$result = PDO4You::selectAll($sql);


// Selecting all records
$result = PDO4You::select('SELECT * FROM books');

// Getting the total number of rows affected by the operation
$total = PDO4You::rowCount();

// Displaying the query results
echo '<pre><h3>Query Result:</h3> ' , print_r($result, true) , '</pre>';

?>
~~~ 



The methods insert(), update() and delete() of the PDO4You class, are nestled between transactions, these being beginTransaction() and commit(). This ensures that the system can roll back an unsuccessful operation and all changes made ​​since the start of a transaction.

Was added in version 3.1 the execute() method, as an alternative to methods (insert, update and delete).

A serious error in the execution results in invoke rollBack(), undoing the whole operation. Consequently one Exception is thrown, tracing the path of all classes and methods involved in the operation, speeding in an environment of "production", the debug process and thus ensuring the database of the risk becoming unstable.

In MySQL, transaction support is available for InnoDB type tables.

The SQL statements of the PDO4You class (insert, update and delete) are now using JSON notation, a new format to write queries which in turn has conventions very similar to languages ​​like Python, Ruby, C++, Java, JavaScript. The new syntax adopted by the class is much more beautiful and concise, than the used by Arrays. Besides compact, instructions are capable of operating simultaneously in different tables in the same database.


Below are excerpts from example in practice.



Inserting a single row in the database
--------------------------------------------------

~~~ php
<?php

// SQL insert in JSON format
$json = '
	insert : [
		{
			table: "users" ,
			values: { mail: "pdo4you@gmail.com" }
		}
	] 
';

// The $result variable stores as return of the method, an array with the number of rows affected by the insert operation
$result = PDO4You::execute($json);

// Just after insertion, use the method PDO4You::lastId() to get the ID of the last insert operation in the database
$lastInsertId = PDO4You::lastId();

// If needed, enter the name of the sequence variable, required in some databases
$lastInsertId = PDO4You::lastId('table_id_seq');

?>
~~~ 



Inserting multiple records
--------------------------------------------------

~~~ php
<?php

// SQL insert in JSON format
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

// The $result variable stores an array with the number of rows affected by the insert operation
$result = PDO4You::execute($json);

?>
~~~ 



Updating multiple records
--------------------------------------------------

~~~ php
<?php

// SQL update in JSON format
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

// The $result variable stores an array with the number of rows affected by the update operation
$result = PDO4You::execute($json);

?>
~~~ 



Deleting multiple records
--------------------------------------------------

~~~ php
<?php

// SQL delete in JSON format
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

// The $result variable stores an array with the number of rows affected by the delete operation
$result = PDO4You::execute($json);

?>
~~~ 



Drivers supported by the server
--------------------------------------------------

Execute the method below to check if the server supports a PDO driver specific to your database.
Supported drivers will be displayed on the screen.

~~~ php
<?php

// The method below shows all the drivers installed and that are supported by the server
PDO4You::showAvailableDrivers();

?>
~~~

To enable any driver not installed, locate the php.ini file, open it and look for "extension=" without quotes, then uncomment the following lines according to your database preferably, removing the beginning of each line the "semicolon" and after changes, restart the server.

~~~ html
;extension=php_pdo.dll                  ; This DLL is not required as of PHP 5.3
extension=php_pdo_mysql.dll             ; MySQL 3.x/4.x/5.x / MariaDB
extension=php_pdo_pgsql.dll             ; PostgreSQL
;extension=php_pdo_cubrid.dll           ; CUBRID
;extension=php_pdo_oci.dll              ; Oracle Call Interface
;extension=php_pdo_sqlsrv.dll           ; Microsoft SQL Server / SQL Azure
;extension=php_pdo_dblib.dll            ; Microsoft SQL Server / Sybase / FreeTDS
;extension=php_pdo_mssql.dll            ; Microsoft SQL Server "Old version"
;extension=php_pdo_sqlite.dll           ; SQLite 2/3

~~~

PDO drivers for the server Xampp:<br />
CUBRID (PHP 5.4): http://bit.ly/PDO_CUBRID-PHP54<br />
CUBRID (PHP 5.3): http://bit.ly/PDO_CUBRID-PHP53<br />
MS SQL Server 3.0 (PHP 5.4): http://bit.ly/PDO_SQLSRV-PHP54<br />
MS SQL Server 2.0 (PHP 5.2/5.3): http://bit.ly/PDO_SQLSRV-PHP53<br />
MS SQL Server (Old version): http://bit.ly/PDO_MSSQL-PHP53



Dependencies
--------------------------------------------------

PHP >= 5.3.2<br />
PHPUnit >= 3.7.0 (needed to run the test suite)



Collaborators
--------------------------------------------------

Giovanni Ramos - <giovannilauro@gmail.com> - <http://twitter.com/giovanni_ramos><br />
See also the list of [colaboradores](http://github.com/giovanniramos/PDO4You/contributors) who participated in this project.



License
--------------------------------------------------

Copyright (c) 2010-2013 [Giovanni Ramos](http://github.com/giovanniramos)

PDO4You is open-sourced software licensed under the [MIT License](http://www.opensource.org/licenses/MIT)