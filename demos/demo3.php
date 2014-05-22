<?php
// Loading demo class
require 'DemoCRUD.php';

// Example with Multiple Instances
echo '<h2><a href=".">DEMOS</a> &rsaquo; MULTIPLE INSTANCES</h2>';

// Importing classes
use PDO4You\PDO4You;

// Creating an instance
$demo = new DemoCRUD;

// Displaying records of the default instance "standard"
$demo->select();

PDO4You::getInstance('bookstore', 'sqlite:data/database_bookstore.db');
PDO4You::getInstance('pdo4you', 'sqlite:data/database_pdo4you.db');

// Displaying records from the data instance: bookstore
$demo->select('bookstore');

// Displaying records from the data instance: pdo4you
$demo->select('pdo4you');

// Sets another instance
PDO4You::setInstance('standard');
#PDO4You::setInstance('bookstore');

// Displaying records from the last data instance set
$demo->select();