#!/usr/bin/env php
<?php

/**
 * Script de observação para desenvolvimento.
 *
 * Uso:
 *   php scripts/dev-watch.php [--file=danfse_com_config.html] [--port=8090]
 *
 * Observa mudanças em src/, regenera e faz live-reload do HTML no browser
 * via o servidor watch-router.php.
 */

$projectRoot   = dirname(__DIR__);
$port          = 8090;
$defaultFile   = 'danfse_ibscbs.html';

foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--file='))  { $defaultFile = substr($arg, 7); }
    if (str_starts_with($arg, '--port='))  { $port = (int) substr($arg, 7); }
}

$tokenFile      = $projectRoot . '/.watch-token';
$generateScript = __DIR__ . '/generate-html.php';
$srcDir         = $projectRoot . '/src';
$routerScript  = __DIR__ . '/watch-router.php';

function regenerate(string $script): bool
{
    $output = [];
    exec('php ' . escapeshellarg($script) . ' 2>&1', $output, $code);
    if ($code !== 0) {
        echo '[watch] ERRO ao gerar:' . "\n" . implode("\n", $output) . "\n\n";
        return false;
    }
    return true;
}

function scanMtimes(string $dir): array
{
    $map = [];
    $it  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $dir,
        FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
    ));
    foreach ($it as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $map[$f->getPathname()] = $f->getMTime();
        }
    }
    return $map;
}

function openBrowser(string $url): void
{
    match (PHP_OS_FAMILY) {
        'Windows' => exec('start "" ' . escapeshellarg($url)),
        'Darwin'  => exec('open '      . escapeshellarg($url)),
        default   => exec('xdg-open '  . escapeshellarg($url)),
    };
}

echo "[watch] Gerando HTML inicial...\n";
if (!regenerate($generateScript)) {
    exit(1);
}
file_put_contents($tokenFile, microtime(true));

$cmd = sprintf('php -S localhost:%d %s', $port, escapeshellarg($routerScript));

chdir($projectRoot);
$null = PHP_OS_FAMILY === 'Windows' ? 'nul' : '/dev/null';
$serverProc = proc_open(
    $cmd,
    [0 => ['pipe', 'r'], 1 => ['file', $null, 'w'], 2 => ['file', $null, 'w']],
    $pipes
);

if (!is_resource($serverProc)) {
    echo "[watch] ERRO: não foi possível iniciar o servidor PHP.\n";
    exit(1);
}

register_shutdown_function(function () use ($serverProc, $tokenFile): void {
    proc_terminate($serverProc);
    if (file_exists($tokenFile)) {
        unlink($tokenFile);
    }
    echo "\n[watch] Servidor encerrado.\n";
});

$url = "http://localhost:{$port}/examples/{$defaultFile}";
echo "[watch] Servidor: {$url}\n";
echo "[watch] Observando mudanças em src/...\n";
echo "[watch] Ctrl+C para parar.\n\n";

sleep(1); // aguarda o servidor inicializar
openBrowser($url);

$lastMtimes = scanMtimes($srcDir);

while (true) {
    usleep(150_000); // 150 ms

    $currentMtimes = scanMtimes($srcDir);
    $diff          = array_diff_assoc($currentMtimes, $lastMtimes);

    if ($diff !== []) {
        foreach (array_keys($diff) as $path) {
            echo '[watch] Alterado: ' . basename($path) . "\n";
        }
        echo "[watch] Gerando HTML...\n";
        regenerate($generateScript);
        file_put_contents($tokenFile, microtime(true));
        echo "[watch] Pronto — HTML atualizado.\n\n";
        $lastMtimes = $currentMtimes;
    }
}
