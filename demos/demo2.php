<?php
// Loading all files required to run the library PDO4You
require_once('../bootstrap.php');

// Example with Insert, Update and Delete
echo '<h2><a href=".">DEMOS</a> &rsaquo; INSERT/UPDATE/DELETE</h2>';

// Creating an instance
$instance = new DemoCRUD;

// Starting the main method
$instance->init();

// Example with inserting multiple records
$instance->multipleInsert();

// Example to update multiple records
$instance->multipleUpdate();

// Example with multiple exclusion of records
$instance->multipleDelete();