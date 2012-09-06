<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Creating an instance
$test = new Test_CRUD;

// Starting the main method
$test->init();

// PostgreSQL test
//PDO4You::getInstance('pg_test', 'pgsql:host=localhost;port=5432;dbname=bookstore', 'postgres', '123456');

// Test with multiple instances
echo '<h2><a href=".">TEST</a> &rsaquo; MULTIPLE INSTANCES</h2>';

// Displaying records from the last data instance set
$test->select();

// Displaying records from the database: bookstore
$test->select('bookstore');

// Displaying records from the database: pdo4you
$test->select('pdo4you');

?>