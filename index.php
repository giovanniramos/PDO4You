<?php
// Load all the files needed
require __DIR__.'/src/bootstrap.php';

// Connection instance imported and available for use
use PDO4You\PDO4You;
new PDO4You;

// Applying styling on the page
PDO4You::css();

// Displaying details on the target server's database connected
PDO4You::showServerInfo();
?>
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>PDO4You</title>
        <style>
            body { background:#FAFAFA; font:normal 12px/1.7em Bitstream Vera Sans Mono,Courier New,Monospace; margin:0; padding:0; }
            a {color: #08c; text-decoration: none;} b {color: #ccc;}
            #pdo4you h2 { display:block; color:#000; background:#FFF; font-size:20px; margin:0; padding:10px; border-bottom:solid 1px #999; }
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
                <a href="?p=demos/demo1.php">SELECTS</a> <b>&sect;</b>
                <a href="?p=demos/demo2.php">INSERT/UPDATE/DELETE</a> <b>&sect;</b>
                <a href="?p=demos/demo3.php">MULTIPLE INSTANCES</a> <b>&sect;</b>
                <a href="?p=demos/demo4.php">HTML EDITOR</a> <b>&sect;</b>
                <a href="?p=demos/demo5.php">READING RECORDS</a> <b>&sect;</b>
                <a href="?p=demos/demo6.php">READING WITH PAGING</a>
            </h2>
            <?php
        }
        ?>
    </body>
</html>