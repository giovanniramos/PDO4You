<?php
// Loading all files required to run the library PDO4You
require_once('../bootstrap.php');

// Creating an instance
$instance = new DemoCRUD;

// Starting the main method
$instance->init();

// Example with text from HTML Editor
echo '<h2><a href=".">DEMOS</a> &rsaquo; HTML EDITOR</h2>';

// Sample text
$sample_text = '<p style="color: #CBF;">Lorem ipsum dolor sit amet</p>';

// Using the method htmlColon() to convert the character colon in a string from a text editor
$sample_text = htmlColon($sample_text);

// UPDATE books SET description = ? WHERE id = 1
$instance->updateWhere($sample_text, 1);