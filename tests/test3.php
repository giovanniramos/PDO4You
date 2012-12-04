<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Creating an instance
$test = new Test_CRUD;

// Starting the main method
$test->init();

// Test with multiple instances
echo '<h2><a href=".">TEST</a> &rsaquo; MULTIPLE INSTANCES</h2>';

// Displaying records from the last data instance set
$test->select();

PDO4You::getInstance('bookstore', 'mysql:host=127.0.0.2;dbname=bookstore', 'root', '');
PDO4You::getInstance('pdo4you', 'mysql:host=127.0.0.2;dbname=pdo4you', 'root', '');

// Displaying records from the data instance: bookstore
$test->select('bookstore');

// Displaying records from the data instance: pdo4you
$test->select('pdo4you');

// Sets another instance
#PDO4You::setInstance('bookstore');
PDO4You::setInstance('default');

// Displaying records from the last data instance set
$test->select();

?>