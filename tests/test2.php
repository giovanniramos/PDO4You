<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Creating an instance
$test = new Test_CRUD;

// Starting the main method
$test->init();

// Test with Insert, Update and Delete
echo '<h2><a href=".">TEST</a> &rsaquo; INSERT/UPDATE/DELETE</h2>';

// Test with inserting multiple records
$test->multipleInsert();

// Test to update multiple records
$test->multipleUpdate();

// Test with multiple exclusion of records
$test->multipleDelete();

?>