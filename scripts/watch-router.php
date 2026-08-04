<?php

/**
 * Roteador para o servidor de desenvolvimento embutido do PHP.
 * Serve arquivos a partir da raiz do projeto, injeta um snippet de live-reload nas respostas HTML
 * e expõe /reload-token para o browser saber quando recarregar.
 */

$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rootDir  = dirname(__DIR__);
$filePath = $rootDir . $uri;

// Endpoint de polling para live-reload
if ($uri === '/reload-token') {
    header('Content-Type: text/plain');
    header('Cache-Control: no-store, no-cache');
    $tokenFile = $rootDir . '/.watch-token';
    echo file_exists($tokenFile) ? trim(file_get_contents($tokenFile)) : '0';
    exit;
}

// Injeta o script de reload e o wrapper de preview A4 nos arquivos HTML
if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'html') {
    header('Content-Type: text/html; charset=utf-8');
    $html = file_get_contents($filePath);

    // Tela A4: fundo cinza, body vira a folha centralizada.
    $a4Style = <<<'CSS'
<style>
@media screen {
    html {
        background: #b0b0b0 !important;
        padding: 24px !important;
        min-height: 100%;
    }
    body {
        width: 210mm !important;
        min-height: 297mm !important;
        margin: 0 auto !important;
        background: #fff !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .45) !important;
    }
    .watermark {
        z-index: 0 !important;
        opacity: .5;
    }
}
</style>
CSS;

    $reloadScript = <<<'JS'
<script>
(function () {
    var token = null;
    function poll() {
        fetch('/reload-token?' + Date.now())
            .then(function (r) { return r.text(); })
            .then(function (t) {
                if (token === null) { token = t; }
                else if (t !== token) { location.reload(); }
            })
            .catch(function () {})
            .finally(function () { setTimeout(poll, 300); });
    }
    poll();
})();
</script>
JS;

    $html = strpos($html, '</head>') !== false
        ? str_replace('</head>', $a4Style . '</head>', $html)
        : $a4Style . $html;

    $html = strpos($html, '</body>') !== false
        ? str_replace('</body>', $reloadScript . '</body>', $html)
        : $html . $reloadScript;

    echo $html;
    exit;
}

// Deixa o servidor embutido tratar todo o resto (arquivos estáticos, 404s)
return false;
