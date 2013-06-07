<?php
// Loading all files required to run the library PDO4You
require_once('../bootstrap.php');

// Creating an instance
$instance = new DemoCRUD;

// Starting the main method
$instance->init();

// Instructions for use with Selects
echo '<h2><a href=".">DEMOS</a> &rsaquo; ALL SELECTS</h2>';

// Displaying records between different instances of base
$instance->allSelects();