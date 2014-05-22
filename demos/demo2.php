<?php
// Loading demo class
require 'DemoCRUD.php';

// Example with Insert, Update and Delete
echo '<h2><a href=".">DEMOS</a> &rsaquo; INSERT/UPDATE/DELETE</h2>';

// Creating an instance
$demo = new DemoCRUD;

// Example with inserting multiple records
$demo->multipleInsert();

// Example to update multiple records
$demo->multipleUpdate();

// Example with multiple exclusion of records
$demo->multipleDelete();