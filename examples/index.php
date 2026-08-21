<?php
$dir = ".";
$files = scandir($dir);

echo "<h2>Lista de Arquivos:</h2><ul>";
foreach ($files as $file) {
    if ($file !== "." && $file !== ".." && $file !== "index.php") {
        echo "<li><a href='$file'>$file</a></li>";
    }
}
echo "</ul>";