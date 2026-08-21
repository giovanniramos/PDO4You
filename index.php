<?php
$examples = [
    'Basic Usage' => 'examples/basic-usage.php',
    'Transaction Usage' => 'examples/transaction-usage.php',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PDO4You - Examples Viewer</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; padding: 24px; max-width: 760px; margin: auto; background-color: #f9fafb; color: #1f2937; }
        .box { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        pre { background: #1e293b; color: #f8fafc; border: 1px solid #334155; padding: 14px; border-radius: 6px; overflow-x: auto; margin:0; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>PDO4You Examples</h1>

    <div class="box">
        <?php foreach ($examples as $title => $path):
            $data = include $path;
        ?>
        <h2><?= htmlspecialchars($title) ?></h2>
        <pre><?= htmlspecialchars(print_r($data, true)) ?></pre>
        <?php endforeach; ?>
    </div>
</body>
</html>