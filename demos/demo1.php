<?php
// Loading demo class
require 'DemoCRUD.php';

// Example with All Selects
echo '<h2><a href=".">DEMOS</a> &rsaquo; SELECTS</h2>';

// Creating an instance
$demo = new DemoCRUD;

// Displaying records between different instances of base
$demo->allSelects();