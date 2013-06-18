<?php
// Loading all files required to run the library PDO4You
require_once('../bootstrap.php');

// Example with Pagination
echo '<h2><a href=".">DEMOS</a> &rsaquo; PAGINATION</h2>';

// Enable pagination
PDO4You::setPagination();

// Sets the page link
PDO4You::setLink('?p=demo6.php&n=');

// Sets the current page navigation
PDO4You::setPage(isset($_GET['n']) ? (int) $_GET['n'] : 1);

// Creating an instance
$instance = new DemoRegister;

// Starting the main method
$instance->init();
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

    <?php echo PDO4You::getPagination(); ?>
</div>