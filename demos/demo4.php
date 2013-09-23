<?php
// Loading demo class
require 'DemoCRUD.php';

// Example with text from HTML Editor
echo '<h2><a href=".">DEMOS</a> &rsaquo; HTML EDITOR</h2>';

// Creating an instance
$demo = new DemoCRUD;

// Sample text
$sample_text = '<p style="color: #CBF;">Lorem ipsum "dolor" sit amet</p>';

// UPDATE books SET description = ? WHERE id = 1
$demo->updateWhere($sample_text, 1);