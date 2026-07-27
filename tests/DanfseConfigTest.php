<?php

namespace DanfseNacional\Tests;

use DanfseNacional\Config\DanfseConfig;
use PHPUnit\Framework\TestCase;

class DanfseConfigTest extends TestCase
{
    public function test_logo_padrao_dos_assets_e_usado(): void
    {
        $config = new DanfseConfig();

        $this->assertNotNull($config->logo);
        $this->assertStringStartsWith('data:image/png;base64,', $config->logo);
    }
}
