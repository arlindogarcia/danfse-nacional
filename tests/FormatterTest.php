<?php

namespace DanfseNacional\Tests;

use DanfseNacional\Formatter;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    private Formatter $fmt;

    protected function setUp(): void
    {
        $this->fmt = new Formatter();
    }

    public function test_formatacao_cnpj(): void
    {
        $this->assertSame('18.587.777/0001-60', $this->fmt->cnpjCpf('18587777000160'));
    }

    public function test_formatacao_cpf(): void
    {
        $this->assertSame('123.456.789-09', $this->fmt->cnpjCpf('12345678909'));
    }

    public function test_cnpj_ja_formatado_e_limpo_e_reformatado(): void
    {
        $this->assertSame('18.587.777/0001-60', $this->fmt->cnpjCpf('18.587.777/0001-60'));
    }

    public function test_cnpj_alfanumerico_sem_mascara(): void
    {
        $this->assertSame('AB.123.CDE/4567-89', $this->fmt->cnpjCpf('AB123CDE456789'));
    }

    public function test_cnpj_alfanumerico_ja_formatado(): void
    {
        $this->assertSame('AB.123.CDE/4567-89', $this->fmt->cnpjCpf('AB.123.CDE/4567-89'));
    }

    public function test_cnpj_alfanumerico_lowercase_e_normalizado_para_maiusculo(): void
    {
        $this->assertSame('AB.123.CDE/4567-89', $this->fmt->cnpjCpf('ab.123.cde/4567-89'));
    }

    public function test_identificador_desconhecido_retorna_como_informado(): void
    {
        $this->assertSame('NIF-12345', $this->fmt->cnpjCpf('NIF-12345'));
    }

    public function test_telefone_10_digitos(): void
    {
        $this->assertSame('(21) 3619-9708', $this->fmt->phone('2136199708'));
    }

    public function test_telefone_11_digitos(): void
    {
        $this->assertSame('(21) 99999-9999', $this->fmt->phone('21999999999'));
    }

    public function test_formatacao_cep(): void
    {
        $this->assertSame('24.020-085', $this->fmt->cep('24020085'));
    }

    public function test_formatacao_data(): void
    {
        $this->assertSame('19/02/2026', $this->fmt->date('2026-02-19'));
    }

    public function test_formatacao_data_hora(): void
    {
        $result = $this->fmt->dateTime('2026-02-19T09:59:11-03:00');
        // Hora pode variar por fuso, verificamos o padrão de formato
        $this->assertMatchesRegularExpression('/\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}/', $result);
    }

    public function test_formatacao_moeda(): void
    {
        $this->assertSame('R$ 400,00', $this->fmt->currency('400.00'));
        $this->assertSame('R$ 1.234,56', $this->fmt->currency('1234.56'));
    }

    public function test_formatacao_cod_trib_nacional(): void
    {
        $this->assertSame('01.03.01', $this->fmt->codTribNacional('010301'));
    }

    public function test_limite(): void
    {
        $this->assertSame('Processamento de ...', $this->fmt->limit('Processamento de dados', 20));
        $this->assertSame('curto', $this->fmt->limit('curto', 20));
    }

    public function test_valores_vazios_retornam_traco(): void
    {
        $this->assertSame('-', $this->fmt->cnpjCpf(''));
        $this->assertSame('-', $this->fmt->phone(''));
        $this->assertSame('-', $this->fmt->cep(''));
        $this->assertSame('-', $this->fmt->date(''));
        $this->assertSame('-', $this->fmt->dateTime(''));
        $this->assertSame('-', $this->fmt->currency(''));
        $this->assertSame('-', $this->fmt->codTribNacional(''));
    }
}
