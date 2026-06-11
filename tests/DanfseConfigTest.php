<?php

namespace DanfseNacional\Tests;

use DanfseNacional\Config\DanfseConfig;
use DanfseNacional\Config\MunicipalityBranding;
use PHPUnit\Framework\TestCase;

class DanfseConfigTest extends TestCase
{
    public function test_sem_logo_especificado_usa_padrao_dos_assets(): void
    {
        $config = new DanfseConfig();

        $this->assertNotNull($config->logoDataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $config->logoDataUri);
    }

    public function test_logo_false_desabilita_logo(): void
    {
        $config = new DanfseConfig(logoPath: false);

        $this->assertNull($config->logoDataUri);
    }

    public function test_caminho_logo_convertido_para_data_uri(): void
    {
        $path = __DIR__ . '/fixtures/logo.png';
        $config = new DanfseConfig(logoPath: $path);

        $this->assertNotNull($config->logoDataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $config->logoDataUri);

        $expected = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        $this->assertSame($expected, $config->logoDataUri);
    }

    public function test_logo_data_uri_usado_diretamente(): void
    {
        $config = new DanfseConfig(logoDataUri: 'data:image/png;base64,abc123');

        $this->assertSame('data:image/png;base64,abc123', $config->logoDataUri);
    }

    public function test_logo_data_uri_tem_precedencia_sobre_caminho(): void
    {
        $config = new DanfseConfig(
            logoDataUri: 'data:image/png;base64,explicit',
            logoPath: __DIR__ . '/fixtures/logo.png',
        );

        $this->assertSame('data:image/png;base64,explicit', $config->logoDataUri);
    }

    public function test_logo_false_tem_precedencia_sobre_data_uri(): void
    {
        $config = new DanfseConfig(
            logoDataUri: 'data:image/png;base64,explicit',
            logoPath: false,
        );

        $this->assertNull($config->logoDataUri);
    }

    public function test_caminho_logo_invalido_lanca_excecao(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/não encontrado/');

        new DanfseConfig(logoPath: '/caminho/inexistente/logo.png');
    }

    public function test_municipality_caminho_logo_e_convertido(): void
    {
        $path = __DIR__ . '/fixtures/logo.png';
        $branding = new MunicipalityBranding(
            name: 'Prefeitura de Niterói',
            logoPath: $path,
        );

        $this->assertNotNull($branding->logoDataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $branding->logoDataUri);
    }

    public function test_municipality_sem_logo_e_nulo(): void
    {
        $branding = new MunicipalityBranding(name: 'Prefeitura de Niterói');

        $this->assertNull($branding->logoDataUri);
    }

    public function test_municipality_caminho_logo_invalido_lanca_excecao(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MunicipalityBranding(
            name: 'Prefeitura de Niterói',
            logoPath: '/caminho/inexistente/logo.png',
        );
    }
}
