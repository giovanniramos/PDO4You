<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Creating an instance
$test = new Test_CRUD;

// Starting the main method
$test->init();

// Test with all Selects
echo '<h2><a href=".">TEST</a> &rsaquo; ALL SELECTS</h2>';

// Displaying records between different instances of base
$test->allSelects();

?>