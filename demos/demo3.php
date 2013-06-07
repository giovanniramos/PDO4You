<?php
// Loading all files required to run the library PDO4You
require_once('../bootstrap.php');

// Creating an instance
$instance = new DemoCRUD;

// Starting the main method
$instance->init();

// Example with multiple instances
echo '<h2><a href=".">DEMOS</a> &rsaquo; MULTIPLE INSTANCES</h2>';

// Displaying records from the last data instance set
$instance->select();

PDO4You::getInstance('bookstore', 'mysql:host=127.0.0.2;dbname=bookstore', 'root', '');
PDO4You::getInstance('pdo4you', 'mysql:host=127.0.0.2;dbname=pdo4you', 'root', '');

// Displaying records from the data instance: bookstore
$instance->select('bookstore');

// Displaying records from the data instance: pdo4you
$instance->select('pdo4you');

// Sets another instance
#PDO4You::setInstance('bookstore');
PDO4You::setInstance('default');

// Displaying records from the last data instance set
$instance->select();