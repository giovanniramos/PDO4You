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
        <style>
            a {color: #08c; text-decoration: none;} b {color: #ccc;}
            .pagination {margin-top: 25px;}
            .pagination a {color: #08c; background: #fff; border: 1px solid #bbb; font-size: 12px; font-weight: bold; display: inline-block; min-width: 13px; min-height: 15px; margin: 0 2px; padding: 1px 6px; -webkit-border-radius:2px; -moz-border-radius:2px; border-radius:2px; text-align: center; cursor: pointer;}
            .pagination a.selected, .pagination a.selected:hover, .pagination a:hover {color: #fff; background: #09e; border: 1px solid #08c;}
            .pagination a.nolink, .pagination a.nolink:hover {color: #aaa; background: #fff; border: 1px solid #ccc; cursor: default;}
        </style>
    </head>
    <body id="pdo4you">
        <?php
        if ($page = filter_input(INPUT_GET, 'p')) {
            include $page;
        } else {
            ?>
            <h2>
                DEMOS &rsaquo; 
                <a href="?p=demo1.php">ALL SELECTS</a> <b>&sect;</b>
                <a href="?p=demo2.php">INSERT/UPDATE/DELETE</a> <b>&sect;</b>
                <a href="?p=demo3.php">MULTIPLE INSTANCES</a> <b>&sect;</b>
                <a href="?p=demo5.php">HTML EDITOR</a> <b>&sect;</b>
                <a href="?p=demo4.php">READ RECORDS</a> <b>&sect;</b>
                <a href="?p=demo6.php">PAGINATION</a>
            </h2>
            <?php
        }
        ?>
    </body>
</html>