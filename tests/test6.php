<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Connection instance started and available
PDO4You::getInstance('pdo4you');

// Displays all available drivers
PDO4You::getAvailableDrivers();

// Displays a list of names of tables in the database.
PDO4You::showTables();
?>