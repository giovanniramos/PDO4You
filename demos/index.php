<?php
// Loading all files required to run the library PDO4You
require_once('../bootstrap.php');

// Connection instance started and available
PDO4You::getInstance();

// Applying styling on the page
PDO4You::setStyle();

// Displaying details on the target server's database connected
PDO4You::getServerInfo();

// Displays all available drivers
#PDO4You::getAvailableDrivers();
?>
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>PDO4You</title>
        <style>a {color: #069; text-decoration: none;} b {color: #ccc;}</style>
    </head>
    <body id="pdo4you">
        <?php
        $pag = isset($_GET['p']) ? $_GET['p'] : null;

        if (!is_null($pag)):
            include $pag;
        else:
            ?>
            <h2>
                DEMOS &rsaquo; 
                <a href="?p=demo1.php">ALL SELECTS</a> <b>&sect;</b>
                <a href="?p=demo2.php">INSERT/UPDATE/DELETE</a> <b>&sect;</b>
                <a href="?p=demo3.php">MULTIPLE INSTANCES</a> <b>&sect;</b>
                <a href="?p=demo4.php">READ RECORDS</a> <b>&sect;</b>
                <a href="?p=demo5.php">HTML EDITOR</a>
            </h2>
        <?php
        endif;
        ?>
    </body>
</html>