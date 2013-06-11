<?php
// Loading all files required to run the library PDO4You
require_once('../bootstrap.php');

// Creating an instance
$instance = new DemoCRUD;

// Starting the main method
$instance->init();

// Example with multiple instances
echo '<h2><a href=".">DEMOS</a> &rsaquo; MULTIPLE INSTANCES</h2>';

// Displaying records of the default instance "standard"
$instance->select();

PDO4You::getInstance('bookstore', 'sqlite:../data/database_bookstore.db');
PDO4You::getInstance('pdo4you', 'sqlite:../data/database_pdo4you.db');

// Displaying records from the data instance: bookstore
$instance->select('bookstore');

// Displaying records from the data instance: pdo4you
$instance->select('pdo4you');

// Sets another instance
PDO4You::setInstance('standard');
#PDO4You::setInstance('bookstore');

// Displaying records from the last data instance set
$instance->select();