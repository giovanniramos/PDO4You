<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Creating an instance
$test = new Test_Register;

// Starting the main method
$test->init();

// Test with Read Records
echo '<h2><a href=".">TEST</a> &rsaquo; READ RECORDS</h2>';
?>

<div id="pdo4you">
    <form method="post">
        <h3><?php echo $test::ADD_NEW_USER; ?></h3>

        <div><?php echo $test::FIRST_NAME; ?>: <input type="text" name="firstname" /></div>
        <div><?php echo $test::LAST_NAME; ?>: <input type="text" name="lastname" /></div>
        <div><?php echo $test::MAIL; ?>: <input type="text" name="mail" /></div>
        <div><input type="submit" value="Register" /></div>
    </form>
    
    <?php echo $test->getMessage(); ?>
    
    <br />

    <?php echo $test::TOTAL_USERS; ?>: <?php echo $test->getTotalRecords(); ?>

    <?php echo $test->getRecords(); ?>
</div>