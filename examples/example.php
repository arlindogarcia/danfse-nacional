<?php

require __DIR__ . '/../vendor/autoload.php';

use DanfseNacional\Config\DanfseConfig;
use DanfseNacional\DanfseGenerator;

$xmlPath = __DIR__ . '/nfse_exemplo.xml';
$xml     = file_get_contents($xmlPath);

// --- Exemplo 1: geração simples, sem configuração ---

$generator = new DanfseGenerator();
$pdf = $generator->generateFromXml($xml);

$output = __DIR__ . '/danfse_simples.pdf';
file_put_contents($output, $pdf);
echo "PDF simples gerado: {$output} (" . number_format(strlen($pdf) / 1024, 1) . " KB)\n";

// --- Exemplo 2: inspecionar os dados antes de gerar ---

$nfse = $generator->parseXml($xml);
$inf  = $nfse->infNFSe;

echo "\nDados extraídos do XML:\n";
echo "  NFS-e número : " . $inf->nNFSe . "\n";
echo "  Emitente     : " . $inf->emit->xNome . "\n";
echo "  CNPJ         : " . $inf->emit->CNPJ . "\n";
echo "  Tomador      : " . $inf->DPS->infDPS->toma->xNome . "\n";
echo "  Valor líquido: R$ " . $inf->valores->vLiq . "\n";
echo "  Competência  : " . $inf->DPS->infDPS->dCompet . "\n";
echo "  Ambiente     : " . ($inf->DPS->infDPS->tpAmb === '1' ? 'Produção' : 'Homologação') . "\n";

// --- Exemplo 3: com marca d'água de nota cancelada ---

$config = new DanfseConfig(canceled: true);

$generator = new DanfseGenerator($config);
$pdf = $generator->generateFromXml($xml);

$output = __DIR__ . '/danfse_com_config.pdf';
file_put_contents($output, $pdf);

$generator = new DanfseGenerator($config);
$nfse = $generator->parseXml($xml);
$html = $generator->generateHtml($nfse);
file_put_contents(__DIR__ . '/danfse_com_config.html', $html);

echo "\nPDF: {$output} (" . number_format(strlen($pdf) / 1024, 1) . " KB)\n";

// --- Exemplo 4: NFS-e com tributação IBS/CBS (Reforma Tributária) ---
// Inclui os blocos "Destinatário da Operação" e "Tributação IBS/CBS",
// além de "Total do IBS/CBS" e "Valor Líquido da NFS-e + IBS/CBS".

$xmlIbsCbs = file_get_contents(__DIR__ . '/nfse_exemplo_ibscbs.xml');

$generator = new DanfseGenerator();
$pdf = $generator->generateFromXml($xmlIbsCbs);

$output = __DIR__ . '/danfse_ibscbs.pdf';
file_put_contents($output, $pdf);

$nfse = $generator->parseXml($xmlIbsCbs);
$html = $generator->generateHtml($nfse);
file_put_contents(__DIR__ . '/danfse_ibscbs.html', $html);

echo "\nPDF IBS/CBS: {$output} (" . number_format(strlen($pdf) / 1024, 1) . " KB)\n";

echo "\nConcluído. Os PDFs foram salvos em " . __DIR__ . "\n";
