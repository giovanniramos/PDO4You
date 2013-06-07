<?php
// Loading all files required to run the library PDO4You
require_once('../bootstrap.php');

// Creating an instance
$instance = new DemoRegister;

// Starting the main method
$instance->init();

// Example with Read Records
echo '<h2><a href=".">DEMOS</a> &rsaquo; READ RECORDS</h2>';
?>

<div class="pdo4you">
    <form method="post">
        <h3><?php echo $instance::ADD_NEW_USER; ?></h3>

        <div><?php echo $instance::FIRST_NAME; ?>: <input type="text" name="firstname" /></div>
        <div><?php echo $instance::LAST_NAME; ?>: <input type="text" name="lastname" /></div>
        <div><?php echo $instance::MAIL; ?>: <input type="text" name="mail" /></div>
        <div><input type="submit" value="Register" /></div>
    </form>

    <?php echo $instance->getMessage(); ?>

    <br />

    <?php echo $instance::TOTAL_USERS; ?>: <?php echo $instance->getTotalRecords(); ?>

    <?php echo $instance->getRecords(); ?>
</div>