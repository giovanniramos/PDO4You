<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Creating an instance
$test = new Test_CRUD;

// Starting the main method
$test->init();

// Test with text from HTML Editor
echo '<h2><a href=".">TEST</a> &rsaquo; HTML EDITOR</h2>';

// Sample text
$sample_text = '<p style="color: #CBF;">Lorem ipsum dolor sit amet</p>';

// Using the method htmlColon() to convert the character colon in a string from a text editor
$sample_text = htmlColon($sample_text);

// UPDATE books SET description = ? WHERE id = 1
$test->updateWhere($sample_text, 1);

?>