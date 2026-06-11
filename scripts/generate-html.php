<?php

/**
 * Script de geração somente HTML, usado pelo observador de desenvolvimento.
 * Ignora a renderização de PDF para manter o ciclo de feedback rápido.
 */

require __DIR__ . '/../vendor/autoload.php';

use DanfseNacional\Config\DanfseConfig;
use DanfseNacional\Config\MunicipalityBranding;
use DanfseNacional\DanfseGenerator;

$examplesDir = __DIR__ . '/../examples';

// danfse_com_config.html — com logo e identificação do município
$xml       = file_get_contents($examplesDir . '/nfse_exemplo.xml');
$config    = new DanfseConfig(
    municipality: new MunicipalityBranding(
        name:       'Prefeitura de Niterói',
        department: 'Secretaria Municipal de Fazenda',
        email:      'iss@fazenda.niteroi.rj.gov.br',
        logoPath:   $examplesDir . '/brasao-niteroi.png',
    ),
);
$generator = new DanfseGenerator($config);
$html      = $generator->generateHtml($generator->parseXml($xml));
file_put_contents($examplesDir . '/danfse_com_config.html', $html);
echo "danfse_com_config.html\n";

// danfse_ibscbs.html — NFS-e com tributação IBS/CBS
$xmlIbsCbs = file_get_contents($examplesDir . '/nfse_exemplo_ibscbs.xml');
$generator = new DanfseGenerator($config);
$html      = $generator->generateHtml($generator->parseXml($xmlIbsCbs));
file_put_contents($examplesDir . '/danfse_ibscbs.html', $html);
echo "danfse_ibscbs.html\n";
