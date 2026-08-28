<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$currentDir = __DIR__;

// Filtra scripts PHP válidos ignorando index e subpastas
$files = array_values(array_filter(scandir($currentDir) ?: [], function (string $file) use ($currentDir): bool {
    $filePath = $currentDir . DIRECTORY_SEPARATOR . $file;
    return is_file($filePath)
        && pathinfo($file, PATHINFO_EXTENSION) === 'php'
        && $file !== 'index.php';
}));

// Sistema simples de i18n
$availableLangs = ['en', 'pt', 'es'];
$lang = filter_input(INPUT_GET, 'lang', FILTER_DEFAULT) ?? 'en';
if (!in_array($lang, $availableLangs, true)) {
    $lang = 'en';
}

$i18n = [
    'en' => [
        'title' => 'PDO4You — Examples Hub',
        'home' => 'Overview',
        'select_hint' => 'Select a demonstration from the menu above to execute and view the output in real-time.',
        'php_version' => 'PHP Version',
        'pdo_drivers' => 'Available PDO Drivers',
        'available_demos' => 'Available Demos',
        'view_source' => 'View Source Code',
        'run' => 'Run Demo'
    ],
    'pt' => [
        'title' => 'PDO4You — Central de Exemplos',
        'home' => 'Visão Geral',
        'select_hint' => 'Selecione uma demonstração no menu acima para executar e visualizar a saída em tempo real.',
        'php_version' => 'Versão do PHP',
        'pdo_drivers' => 'Drivers PDO Instalados',
        'available_demos' => 'Demonstrações Disponíveis',
        'view_source' => 'Ver Código-Fonte',
        'run' => 'Executar Exemplo'
    ],
    'es' => [
        'title' => 'PDO4You — Centro de Ejemplos',
        'home' => 'Visión General',
        'select_hint' => 'Seleccione una demostración en el menú superior para ejecutar y ver el resultado en tiempo real.',
        'php_version' => 'Versión de PHP',
        'pdo_drivers' => 'Controladores PDO Disponibles',
        'available_demos' => 'Demostraciones Disponibles',
        'view_source' => 'Ver Código Fuente',
        'run' => 'Ejecutar Ejemplo'
    ]
];

$t = $i18n[$lang];

// Sanitização e validação de rota
$selected = filter_input(INPUT_GET, 'demo', FILTER_DEFAULT);
$scriptPath = ($selected && in_array($selected, $files, true))
    ? $currentDir . DIRECTORY_SEPARATOR . $selected
    : null;

// Função auxiliar para formatar nomes de arquivos em rótulos amigáveis
function formatTitle(string $filename): string {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    return ucwords(str_replace(['-', '_'], ' ', $name));
}

// Define o título dinâmico (se houver demo selecionada ou home)
$pageTitle = $scriptPath ? 'PDO4You — ' . formatTitle((string)$selected) : $t['title'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; padding: 24px; max-width: 850px; margin: auto; background-color: var(--bg); color: var(--text-main); }
        pre { background: #0f172a; color: #f8fafc; padding: 14px; border-radius: 6px; overflow-x: auto; font-size: 0.88em; margin: 8px 0; }
        .card { background: var(--card-bg); padding: 28px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07); margin-bottom: 20px; border: 1px solid var(--border); }

        /* Header & Lang Bar */
        .top-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .top-bar h1 { margin: 0; font-size: 1.6rem; }
        .lang-switch { display: flex; gap: 4px; background: #f1f5f9; padding: 3px; border-radius: 6px; }
        .lang-switch a { padding: 3px 8px; text-decoration: none; color: var(--text-muted); font-size: 0.78rem; font-weight: 700; border-radius: 4px; text-transform: uppercase; }
        .lang-switch a.active { background: #fff; color: var(--primary); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }

        /* Nav Pills */
        .nav-links { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
        .nav-links a { padding: 6px 14px; background: #f1f5f9; color: var(--text-main); border: 1px solid var(--border); border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: all 0.2s ease; }
        .nav-links a.active, .nav-links a:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* Status & Steps Elements */
        .step { color: var(--primary); font-weight: 600; margin-top: 18px; }
        .success { color: #16a34a; font-weight: 500; }
        .error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .divider { border: 0; border-top: 1px solid var(--border); margin: 24px 0; }

        /* Initial Dashboard Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin: 20px 0; }
        .stat-box { background: #f8fafc; border: 1px solid var(--border); padding: 14px; border-radius: 8px; }
        .stat-box small { color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; font-weight: 600; display: block; }
        .stat-box strong { font-size: 1.1rem; color: var(--text-main); }
        .badge { display: inline-block; padding: 2px 8px; background: #e0e7ff; color: #3730a3; border-radius: 4px; font-size: 0.75rem; font-weight: 600; margin-right: 4px; margin-top: 4px; }

        /* Source Code Collapsible */
        details.source-viewer { margin-top: 24px; border: 1px solid var(--border); border-radius: 6px; padding: 10px 14px; background: #fafafa; }
        details.source-viewer summary { cursor: pointer; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); outline: none; }
        details.source-viewer summary:hover { color: var(--primary); }
    </style>
</head>
<body>
    <div class="card">
        <div class="top-bar">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <div class="lang-switch">
                <?php foreach ($availableLangs as $l): ?>
                    <a href="?<?= http_build_query(array_filter(['demo' => $selected, 'lang' => $l])) ?>" class="<?= $lang === $l ? 'active' : '' ?>">
                        <?= $l ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <nav class="nav-links">
            <a href="?lang=<?= $lang ?>" class="<?= $selected === null ? 'active' : '' ?>">
                🏠 <?= htmlspecialchars($t['home']) ?>
            </a>
            <?php foreach ($files as $file): ?>
                <a href="?demo=<?= urlencode($file) ?>&lang=<?= $lang ?>" class="<?= $selected === $file ? 'active' : '' ?>">
                    <?= htmlspecialchars(formatTitle($file)) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <hr class="divider">

        <?php if ($scriptPath && file_exists($scriptPath)): ?>
            <!-- Execução do Script -->
            <div class="execution-output">
                <?php require $scriptPath; ?>
            </div>

            <!-- Inspecionar Código-Fonte -->
            <details class="source-viewer">
                <summary>🔍 <?= htmlspecialchars($t['view_source']) ?> (<?= htmlspecialchars($selected) ?>)</summary>
                <pre><?= htmlspecialchars((string)file_get_contents($scriptPath)) ?></pre>
            </details>
        <?php else: ?>
            <!-- Estado Inicial / Dashboard Informativo -->
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0;"><?= htmlspecialchars($t['select_hint']) ?></p>

            <div class="grid">
                <div class="stat-box">
                    <small><?= htmlspecialchars($t['php_version']) ?></small>
                    <strong><?= PHP_VERSION ?></strong>
                </div>
                <div class="stat-box">
                    <small><?= htmlspecialchars($t['pdo_drivers']) ?></small>
                    <div>
                        <?php foreach (\PDO::getAvailableDrivers() as $driver): ?>
                            <span class="badge"><?= htmlspecialchars($driver) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <h3 style="margin-top: 24px; font-size: 1.05rem;"><?= htmlspecialchars($t['available_demos']) ?>:</h3>
            <div class="grid">
                <?php foreach ($files as $file): ?>
                    <div class="stat-box" style="display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <strong><?= htmlspecialchars(formatTitle($file)) ?></strong>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 4px 0 10px 0;"><?= htmlspecialchars($file) ?></p>
                        </div>
                        <a href="?demo=<?= urlencode($file) ?>&lang=<?= $lang ?>" style="color: var(--primary); text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                            <?= htmlspecialchars($t['run']) ?> &rarr;
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>