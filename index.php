<?php
// Loading all the necessary files
require __DIR__.'/src/bootstrap.php';

// Connection class imported
use PDO4You\PDO4You;

// Connection instance started and available
PDO4You::getInstance();

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
            a    { color:#08C; text-decoration:none; } b { color:#CCC; }
            #pdo4you h2 { color:#000; background:#FFF; font-size:20px; display:block; margin:0; padding:10px; border-bottom:solid 1px #999; }
            .pagination { margin-top:25px;}
            .pagination a { color:#08C; background:#FFF; border:1px solid #BBB; font-size:12px; font-weight:bold; display:inline-block; min-width:13px; min-height:15px; margin:0 2px; padding:1px 6px; -webkit-border-radius:2px; -moz-border-radius:2px; border-radius:2px; text-align:center; cursor:pointer; }
            .pagination a.selected, .pagination a.selected:hover, .pagination a:hover { color:#FFF; background:#09E; border:1px solid #08C; }
            .pagination a.nolink, .pagination a.nolink:hover { color:#AAA; background:#FFF; border:1px solid #CCC; cursor:default; }
        </style>
    </head>
    <body id="pdo4you">
        <?php
        if ((string) $page = filter_input(INPUT_GET, 'p')) {
            include '/demos/' . $page . '.php';
        } else {
            ?>
            <h2>
                DEMOS &rsaquo; 
                <a href="?p=demo1">SELECTS</a> <b>&sect;</b>
                <a href="?p=demo2">INSERT/UPDATE/DELETE</a> <b>&sect;</b>
                <a href="?p=demo3">MULTIPLE INSTANCES</a> <b>&sect;</b>
                <a href="?p=demo4">HTML EDITOR</a> <b>&sect;</b>
                <a href="?p=demo5">READING RECORDS</a> <b>&sect;</b>
                <a href="?p=demo6">READING WITH PAGING</a>
            </h2>
            <?php
        }
        ?>
    </body>
</html>