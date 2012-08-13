<?php
// Load of all necessary classes
require_once '../PDO4You.load.php';

// Connection instance started and available
PDO4You::getInstance();
#PDO4You::showMySqlTables();
// Applying styling on the page
PDO4You::setStyle();

// Displaying details on the target server's database connected
PDO4You::getServerInfo();

// Displays all available drivers
#PDO4You::getAvailableDrivers();

?>
<!DOCTYPE html>
<meta charset="utf-8" />
<title>PDO4You</title>
<style>a {color: #069; text-decoration: none;} b {color: #ccc;}</style>
<html>
    <body id="pdo4you">
        <?php
        $pag = isset($_GET['p']) ? $_GET['p'] : null;

        if (!is_null($pag)):
            include $pag;
        else:
            ?>
            <h2>
                TEST &rsaquo; 
                <a href="?p=test1.php">ALL SELECTS</a> <b>&sect;</b>
                <a href="?p=test2.php">INSERT/UPDATE/DELETE</a> <b>&sect;</b>
                <a href="?p=test3.php">MULTIPLE INSTANCES</a> <b>&sect;</b>
                <a href="?p=test4.php">READ RECORDS</a> <b>&sect;</b>
                <a href="?p=test5.php">HTML EDITOR</a>
            </h2>
        <?php
        endif;
        ?>
    </body>
</html>