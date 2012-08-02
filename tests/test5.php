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
$sample_text = '<p style="color: #CBF;">Lorem ipsum dolor site amet</p><p><strong>Lorem ipsum dolor site amet</strong></p>';

// Using the method clearStyle() to remove the tag style html tags derived from a text editor
$sample_text = clearStyle($sample_text);

// UPDATE books SET text = ? WHERE id = 1
$test->updateWhere($sample_text, 1);
