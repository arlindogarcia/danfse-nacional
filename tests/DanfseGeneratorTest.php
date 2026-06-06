<?php

namespace DanfseNacional\Tests;

use DanfseNacional\Config\DanfseConfig;
use DanfseNacional\Config\MunicipalityBranding;
use DanfseNacional\DanfseGenerator;
use DanfseNacional\Dto\NFSe;
use PHPUnit\Framework\TestCase;

class DanfseGeneratorTest extends TestCase
{
    private string $realXml;

    protected function setUp(): void
    {
        $path = __DIR__ . '/../examples/nfse_exemplo.xml';
        $this->realXml = file_get_contents($path);
        $this->assertNotFalse($this->realXml, "real_nfse.xml não encontrado em $path");
    }

    public function test_parse_xml_returns_nfse_dto(): void
    {
        $generator = new DanfseGenerator();
        $nfse = $generator->parseXml($this->realXml);

        $this->assertInstanceOf(NFSe::class, $nfse);
        $this->assertNotNull($nfse->infNFSe);
    }

    public function test_parsed_dto_fields_match_xml(): void
    {
        $generator = new DanfseGenerator();
        $nfse = $generator->parseXml($this->realXml);

        $inf = $nfse->infNFSe;
        $this->assertNotNull($inf);
        $this->assertSame('10', $inf->nNFSe);
        $this->assertSame('Niterói', $inf->xLocEmi);

        $emit = $inf->emit;
        $this->assertNotNull($emit);
        $this->assertSame('11222333000181', $emit->CNPJ);
        $this->assertSame('EMPRESA EXEMPLO DESENVOLVIMENTO LTDA', $emit->xNome);

        $dps = $inf->DPS;
        $this->assertNotNull($dps);

        $infDps = $dps->infDPS;
        $this->assertNotNull($infDps);
        $this->assertSame('1', $infDps->tpAmb);
        $this->assertSame('5', $infDps->nDPS);
        $this->assertSame('2026-01-15', $infDps->dCompet);

        $toma = $infDps->toma;
        $this->assertNotNull($toma);
        $this->assertSame('91712343000134', $toma->CNPJ);
        $this->assertSame('CLIENTE FICTICIO COMERCIO S.A.', $toma->xNome);
    }

    public function test_generate_from_xml_returns_pdf_binary(): void
    {
        $generator = new DanfseGenerator();
        $pdf = $generator->generateFromXml($this->realXml);

        // Verifica assinatura do PDF (%PDF-)
        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    public function test_generate_with_config(): void
    {
        $config = new DanfseConfig(
            municipality: new MunicipalityBranding(
                name: 'Prefeitura de Niterói',
                department: 'Secretaria Municipal de Fazenda',
                email: 'iss@fazenda.niteroi.rj.gov.br',
            ),
        );
        $generator = new DanfseGenerator($config);
        $pdf = $generator->generateFromXml($this->realXml);

        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    public function test_two_step_generation(): void
    {
        $generator = new DanfseGenerator();
        $nfse = $generator->parseXml($this->realXml);
        $pdf = $generator->generatePdf($nfse);

        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    public function test_template_data_matches_expected(): void
    {
        $generator = new DanfseGenerator();
        $nfse = $generator->parseXml($this->realXml);

        $template = new \DanfseNacional\Template\DanfseTemplate();
        $data = $template->buildData($nfse);

        // Chave de acesso (sem prefixo NFS)
        $this->assertSame('3303302112233450000195000000000000100000000001', $data['chave_acesso']);

        // Identificação
        $this->assertSame('Prestador', $data['tipo_emitente']);
        $this->assertSame('NFS-e Gerada', $data['situacao']);
        $this->assertSame('', $data['finalidade']); // finNFSe ausente no XML v1.01
        $this->assertSame('Sistema Nacional da NFS-e', $data['amb_gerador']);

        // Prestador
        $this->assertSame('11.222.333/0001-81', $data['prestador']['cnpj_cpf']);
        $this->assertSame('EMPRESA EXEMPLO DESENVOLVIMENTO LTDA', $data['prestador']['nome']);
        $this->assertSame('Niterói / RJ', $data['prestador']['municipio']);
        $this->assertSame('987654', $data['prestador']['im']);
        $this->assertSame('3303302 / 24020-005', $data['prestador']['codigo_ibge_cep']);

        // Tomador
        $this->assertSame('91.712.343/0001-34', $data['tomador']['cnpj_cpf']);
        $this->assertSame('CLIENTE FICTICIO COMERCIO S.A.', $data['tomador']['nome']);
        $this->assertSame('3550308 / 01310-100', $data['tomador']['codigo_ibge_cep']);

        // Serviço
        $this->assertSame('01.07.00', $data['servico']['codigo_trib_nacional']);
        $this->assertSame('110811000', $data['servico']['codigo_nbs']);

        // Tributação federal
        $this->assertSame('R$ 15,00', $data['tributacao_federal']['contrib_sociais']);
        $this->assertSame('PIS/COFINS Não Retido', $data['tributacao_federal']['desc_contrib_sociais']);

        // Totais
        $this->assertSame('R$ 1.500,00', $data['totais']['valor_servico']);
        $this->assertSame('R$ 1.292,75', $data['totais']['valor_liquido']);

        // Ambiente
        $this->assertSame(1, $data['ambiente']);

        // Tributação municipal
        $this->assertSame('Operação Tributável', $data['tributacao_municipal']['tributacao_issqn']);
        $this->assertSame('Retido pelo Tomador', $data['tributacao_municipal']['retencao_issqn']);
        $this->assertSame('Sociedade de Profissionais', $data['tributacao_municipal']['regime_especial']);
        $this->assertSame('Niterói', $data['tributacao_municipal']['municipio_incidencia']);

        // Emitente: Simples Nacional
        $this->assertSame(
            'Não Optante',
            $data['prestador']['simples_nacional'],
        );
    }

    public function test_finalidade_shown_when_finNFSe_present(): void
    {
        // Injeta o grupo IBS/CBS com finNFSe (modelo v2.0) no infDPS.
        $xml = str_replace(
            '<serv>',
            '<IBSCBS><finNFSe>0</finNFSe></IBSCBS><serv>',
            $this->realXml,
        );

        $generator = new DanfseGenerator();
        $nfse = $generator->parseXml($xml);
        $template = new \DanfseNacional\Template\DanfseTemplate();
        $data = $template->buildData($nfse);

        $this->assertSame('NFS-e regular', $data['finalidade']);
    }

    public function test_destinatario_nao_identificado_sem_ibscbs(): void
    {
        // XML v1.01 sem grupo IBS/CBS: destinatário deve cair na mensagem padrão.
        $generator = new DanfseGenerator();
        $nfse = $generator->parseXml($this->realXml);
        $template = new \DanfseNacional\Template\DanfseTemplate();
        $data = $template->buildData($nfse);

        $this->assertNull($data['destinatario']);
        $this->assertSame('DESTINATÁRIO DA OPERAÇÃO NÃO IDENTIFICADO NA NFS-e', $data['destinatario_msg']);
        $this->assertNull($data['ibs_cbs']);
    }

    public function test_ibscbs_example_fields(): void
    {
        $path = __DIR__ . '/../examples/nfse_exemplo_ibscbs.xml';
        $xml = file_get_contents($path);
        $this->assertNotFalse($xml, "exemplo IBS/CBS não encontrado em $path");

        $generator = new DanfseGenerator();
        $nfse = $generator->parseXml($xml);
        $template = new \DanfseNacional\Template\DanfseTemplate();
        $data = $template->buildData($nfse);

        // Finalidade (finNFSe) agora presente
        $this->assertSame('NFS-e regular', $data['finalidade']);

        // Destinatário identificado
        $this->assertNotNull($data['destinatario']);
        $this->assertSame('DESTINATARIO FINAL SERVICOS LTDA', $data['destinatario']['nome']);
        $this->assertSame('3304557 / 20040-002', $data['destinatario']['codigo_ibge_cep']);
        $this->assertSame('', $data['destinatario_msg']);

        // Bloco IBS/CBS
        $this->assertNotNull($data['ibs_cbs']);
        $this->assertSame('000 / 000001', $data['ibs_cbs']['cst_classtrib']);
        $this->assertSame('100000 / 3303302 / Niterói', $data['ibs_cbs']['indicador_operacao']);
        $this->assertSame('R$ 1.500,00', $data['ibs_cbs']['bc']);
        $this->assertSame('0.10% / 0.05%', $data['ibs_cbs']['aliquota_ibs']);
        $this->assertSame('R$ 0,75', $data['ibs_cbs']['valor_apurado_mun']);
        $this->assertSame('R$ 1,50', $data['ibs_cbs']['valor_apurado_uf']);
        $this->assertSame('R$ 2,25', $data['ibs_cbs']['valor_total_ibs']);
        $this->assertSame('R$ 13,50', $data['ibs_cbs']['valor_total_cbs']);
        // Exclusões = vDescIncond(100) + vISSQN(27) + vPis(9,75) + vCofins(45)
        $this->assertSame('R$ 181,75', $data['ibs_cbs']['exclusoes_reducoes']);

        // Totais com IBS/CBS
        $this->assertSame('R$ 15,75', $data['totais']['total_ibs_cbs']);
        $this->assertSame('R$ 1.308,50', $data['totais']['valor_liquido_ibscbs']);
    }

    public function test_ibscbs_example_generates_pdf(): void
    {
        $xml = file_get_contents(__DIR__ . '/../examples/nfse_exemplo_ibscbs.xml');
        $pdf = (new DanfseGenerator())->generateFromXml($xml);
        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    public function test_prestador_prioriza_no_prest_sobre_emit(): void
    {
        $prestFull = '<prest>'
            . '<CNPJ>11222333000181</CNPJ>'
            . '<IM>111222</IM>'
            . '<xNome>PRESTADOR DECLARADO NA DPS LTDA</xNome>'
            . '<end><endNac><cMun>3304557</cMun><CEP>20040002</CEP></endNac>'
            . '<xLgr>Avenida Rio Branco</xLgr><nro>1</nro><xBairro>Centro</xBairro></end>'
            . '<fone>2199990000</fone>'
            . '<email>dps@prestador.com.br</email>'
            . '<regTrib><opSimpNac>1</opSimpNac><regEspTrib>6</regEspTrib></regTrib>'
            . '</prest>';
        $xml = preg_replace('#<prest>.*?</prest>#s', $prestFull, $this->realXml);

        $nfse = (new DanfseGenerator())->parseXml($xml);
        $data = (new \DanfseNacional\Template\DanfseTemplate())->buildData($nfse);

        $this->assertSame('PRESTADOR DECLARADO NA DPS LTDA', $data['prestador']['nome']);
        $this->assertSame('111222', $data['prestador']['im']);
        $this->assertSame('Rio de Janeiro / RJ', $data['prestador']['municipio']);
        $this->assertSame('3304557 / 20040-002', $data['prestador']['codigo_ibge_cep']);
        $this->assertSame('(21) 9999-0000', $data['prestador']['telefone']);
    }

    public function test_prestador_endereco_exterior(): void
    {
        $prestExt = '<prest>'
            . '<CNPJ>11222333000181</CNPJ>'
            . '<xNome>FOREIGN PROVIDER INC</xNome>'
            . '<end><endExt><cPais>2496</cPais><cEndPost>10001</cEndPost>'
            . '<xCidade>New York</xCidade><xEstProvReg>NY</xEstProvReg></endExt>'
            . '<xLgr>5th Avenue</xLgr><nro>100</nro><xBairro>Manhattan</xBairro></end>'
            . '<regTrib><opSimpNac>1</opSimpNac></regTrib>'
            . '</prest>';
        $xml = preg_replace('#<prest>.*?</prest>#s', $prestExt, $this->realXml);

        $nfse = (new DanfseGenerator())->parseXml($xml);
        $data = (new \DanfseNacional\Template\DanfseTemplate())->buildData($nfse);

        $this->assertSame('New York / NY', $data['prestador']['municipio']);
        $this->assertSame('10001', $data['prestador']['codigo_ibge_cep']);
        $this->assertSame('5th Avenue, 100, Manhattan', $data['prestador']['endereco']);
    }

    public function test_intermediario_im_lido_de_tag_IM(): void
    {
        $xml = file_get_contents(__DIR__ . '/../examples/nfse_exemplo_ibscbs.xml');
        $data = (new \DanfseNacional\Template\DanfseTemplate())
            ->buildData((new DanfseGenerator())->parseXml($xml));

        $this->assertSame('123456', $data['intermediario']['im']);
    }

    public function test_tomador_com_nif(): void
    {
        $xmlNif = preg_replace(
            '#<toma>.*?</toma>#s',
            '<toma><NIF>X1234567890</NIF><xNome>TOMADOR EXTERIOR</xNome>'
            . '<email>t@ext.com</email></toma>',
            $this->realXml,
        );

        $data = (new \DanfseNacional\Template\DanfseTemplate())
            ->buildData((new DanfseGenerator())->parseXml($xmlNif));

        // NIF alfanumérico não é mutilado pelo formatter
        $this->assertSame('X1234567890', $data['tomador']['cnpj_cpf']);
        $this->assertSame('TOMADOR EXTERIOR', $data['tomador']['nome']);
    }

    public function test_nota6_pis_cofins_suprimidos_apos_2026(): void
    {
        $generator = new DanfseGenerator();
        $template = new \DanfseNacional\Template\DanfseTemplate();

        // Competência em 2026 → PIS/COFINS/Descrição visíveis
        $data2026 = $template->buildData($generator->parseXml($this->realXml));
        $this->assertTrue($data2026['tributacao_federal']['mostrar_pis_cofins']);
        $html2026 = $generator->generateHtml($generator->parseXml($this->realXml));
        $this->assertStringContainsString('PIS - Débito Apuração Própria', $html2026);

        // Competência em 2027 → linha suprimida (Nota 6)
        $xml2027 = str_replace('<dCompet>2026-01-15</dCompet>', '<dCompet>2027-03-10</dCompet>', $this->realXml);
        $data2027 = $template->buildData($generator->parseXml($xml2027));
        $this->assertFalse($data2027['tributacao_federal']['mostrar_pis_cofins']);
        $html2027 = $generator->generateHtml($generator->parseXml($xml2027));
        $this->assertStringNotContainsString('PIS - Débito Apuração Própria', $html2027);
        $this->assertStringNotContainsString('Descrição Contrib. Sociais - Retidas', $html2027);
    }

    public function test_homologacao_environment_flag(): void
    {
        // Substitui tpAmb=1 (produção) por tpAmb=2 (homologação)
        $xml = str_replace('<tpAmb>1</tpAmb>', '<tpAmb>2</tpAmb>', $this->realXml);

        $generator = new DanfseGenerator();
        $nfse = $generator->parseXml($xml);
        $template = new \DanfseNacional\Template\DanfseTemplate();
        $data = $template->buildData($nfse);

        $this->assertSame(2, $data['ambiente']);
    }

    public function test_generate_pdf_size_is_reasonable(): void
    {
        $generator = new DanfseGenerator();
        $pdf = $generator->generateFromXml($this->realXml);

        // Um PDF de A4 válido deve ter pelo menos 1KB e no máximo ~5MB
        $size = strlen($pdf);
        $this->assertGreaterThan(1000, $size, 'PDF parece muito pequeno');
        $this->assertLessThan(5_000_000, $size, 'PDF parece muito grande');
    }
}
