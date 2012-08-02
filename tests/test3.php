<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Creating an instance
$test = new Test_CRUD;

// Starting the main method
$test->init();

// Test with multiple instances
echo '<h2><a href=".">TEST</a> &rsaquo; MULTIPLE INSTANCES</h2>';

// Displaying records from the database: bookstore
$test->select('bookstore');

// Displaying records from the database: pdo4you
$test->select('pdo4you');

// Displaying records from the last data instance set
$test->select();

// Displaying records from the database: bookstore
$test->select('bookstore');

?>