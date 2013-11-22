<?php
// Loading demo class
require 'DemoRegister.php';

// Example with Pagination
echo '<h2><a href=".">DEMOS</a> &rsaquo; READING WITH PAGING</h2>';

// Importing classes
use PDO4You\Pagination as pager;

// Enable pagination: If the passed value is TRUE enables paging for friendly URLs
pager::setPagination(false);

// Sets the number of records per page
//pager::setLimitPerPage(2);

// Sets the page navigation
pager::setPaginator('n');

// Creating an instance
$demo = new DemoRegister;
?>

<div class="pdo4you">
    <form method="post">
        <h3><?php echo $demo::ADD_NEW_USER; ?></h3>

        <div><?php echo $demo::FIRST_NAME; ?>: <input type="text" name="firstname" /></div>
        <div><?php echo $demo::LAST_NAME; ?>: <input type="text" name="lastname" /></div>
        <div><?php echo $demo::MAIL; ?>: <input type="text" name="mail" /></div>
        <div><input type="submit" value="Register" /></div>
    </form>

    <?php echo $demo->getMessage(); ?>

    <?php echo $demo::TOTAL_USERS; ?>: <?php echo $demo->getTotalOfRecords(); ?>

    <?php echo $demo->showRecords(); ?>

    <?php echo pager::getPagination(); ?>
</div>