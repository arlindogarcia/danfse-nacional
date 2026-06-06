<?php

namespace DanfseNacional\Template;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use DanfseNacional\Config\DanfseConfig;
use DanfseNacional\Dto\Endereco;
use DanfseNacional\Dto\NFSe;
use DanfseNacional\Dto\RtcIBSCBS;
use DanfseNacional\Dto\RtcIBSCBSNFSe;
use DanfseNacional\Dto\SitClasIbsCbs;
use DanfseNacional\Dto\TotCIbs;
use DanfseNacional\Dto\TribFederal;
use DanfseNacional\Dto\TribMunicipal;
use DanfseNacional\Dto\ValoresIbsCbs;
use DanfseNacional\Enums\AmbGerador;
use DanfseNacional\Enums\FinNFSe;
use DanfseNacional\Enums\OpSimpNac;
use DanfseNacional\Enums\RegApTribSN;
use DanfseNacional\Enums\RegEspTrib;
use DanfseNacional\Enums\SituacaoNFSe;
use DanfseNacional\Enums\TpBM;
use DanfseNacional\Enums\TpEmitente;
use DanfseNacional\Enums\TpExigSusp;
use DanfseNacional\Enums\TpImunidade;
use DanfseNacional\Enums\TpRetISSQN;
use DanfseNacional\Enums\TpRetPisCofins;
use DanfseNacional\Enums\TribISSQN;
use DanfseNacional\Data\Municipios;
use DanfseNacional\Formatter;

/**
 * Constrói o array de dados para o template e gera o QR Code.
 */
class DanfseTemplate
{
    private Formatter $fmt;

    public function __construct()
    {
        $this->fmt = new Formatter();
    }

    /**
     * Renderiza o template e retorna o HTML completo
     */
    public function render(NFSe $nfse, DanfseConfig $config): string
    {
        $data = $this->buildData($nfse);
        $logo = $config->logoDataUri;
        $municipality = $config->municipality;
        $qrCode = $this->generateQrCode($data['chave_acesso']);
        array_walk_recursive($data, fn(&$v) => $v = is_string($v) ? htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $v);

        $templatePath = __DIR__ . '/danfse.php';

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Constrói o array de dados para o template a partir dos DTOs
     */
    public function buildData(NFSe $nfse): array
    {
        $inf = $nfse->infNFSe;
        $dps = $inf?->DPS;
        $infDps = $dps?->infDPS;
        $prest = $infDps?->prest;
        $regTrib = $prest?->regTrib;
        $toma = $infDps?->toma;
        $endToma = $toma?->end;
        $interm = $infDps?->interm;
        $endInterm = $interm?->end;
        $serv = $infDps?->serv;
        $locPrest = $serv?->locPrest;
        $cServ = $serv?->cServ;
        $valores = $infDps?->valores;
        $vServPrest = $valores?->vServPrest;
        $trib = $valores?->trib;
        $tribMun = $trib?->tribMun;
        $tribFed = $trib?->tribFed;
        $totTrib = $trib?->totTrib;
        $valoresNfse = $inf?->valores;

        // Grupos IBS/CBS: declarado (infDPS) e calculado pelo sistema (infNFSe)
        $ibsCbsDps = $infDps?->IBSCBS;
        $ibsCbsNfse = $inf?->IBSCBS;
        $gIBSCBS = $ibsCbsDps?->valores?->trib?->gIBSCBS;
        $dest = $ibsCbsDps?->dest;
        $valIbs = $ibsCbsNfse?->valores;
        $totC = $ibsCbsNfse?->totCIBS;

        // Chave de acesso (remove prefixo "NFS")
        $id = $inf?->Id ?? '';
        $chaveAcesso = str_starts_with($id, 'NFS') ? substr($id, 3) : $id;

        // Bloco PRESTADOR/FORNECEDOR (NT-008 2.1.3): dados lidos exclusivamente
        // do nó prest (infDPS/prest). A NT-008 não prevê o nó emit como fonte.
        $prestEnd = $prest?->end;

        $endDest = $dest?->end;

        // Finalidade só é exibida quando informada no XML (grupo IBS/CBS).
        $finNFSe = $ibsCbsDps?->finNFSe ?? '';

        // Destinatário: bloco com dados ou mensagem de supressão (Notas 2 e 3).
        $indDest = $ibsCbsDps?->indDest ?? '';
        $destinatario = null;
        $destinatarioMsg = '';
        if ($dest !== null && $indDest !== '0') {
            $destinatario = [
                'nome' => $dest->xNome ?: '-',
                'cnpj_cpf' => $this->fmt->cnpjCpf($dest->documento()),
                'telefone' => $this->fmt->phone($dest->fone),
                'email' => strtolower($dest->email),
                'endereco' => $this->enderecoLinha($endDest) ?: '-',
                'municipio' => $this->municipioUf($endDest) ?: '-',
                'codigo_ibge_cep' => $this->ibgeCepFrom($endDest) ?: '-',
            ];
        } elseif ($indDest === '0') {
            $destinatarioMsg = 'O DESTINATÁRIO É O PRÓPRIO TOMADOR/ADQUIRENTE DA OPERAÇÃO';
        } else {
            $destinatarioMsg = 'DESTINATÁRIO DA OPERAÇÃO NÃO IDENTIFICADO NA NFS-e';
        }

        // Bloco de Tributação IBS/CBS (somente quando há grupo IBS/CBS no XML).
        $ibsCbs = ($ibsCbsNfse !== null || $ibsCbsDps !== null)
            ? $this->buildIbsCbs($gIBSCBS, $ibsCbsDps, $ibsCbsNfse, $valIbs, $totC, $tribMun, $tribFed)
            : null;

        // Nota 6 (NT-008): PIS, COFINS e Descrição Contrib. Sociais - Retidas só
        // são impressos para NFS-e com competência até o fim do ano-calendário de 2026.
        $anoCompet = (int) substr($infDps?->dCompet ?? '', 0, 4);
        $mostrarPisCofins = $anoCompet === 0 || $anoCompet <= 2026;

        return [
            'chave_acesso' => $chaveAcesso,
            'numero_nfse' => $inf?->nNFSe ?? '-',
            'competencia' => $this->fmt->date($infDps?->dCompet ?? ''),
            'emissao_nfse' => $this->fmt->dateTime($inf?->dhProc ?? ''),
            'numero_dps' => $infDps?->nDPS ?? '-',
            'serie_dps' => $infDps?->serie ?? '-',
            'emissao_dps' => $this->fmt->dateTime($infDps?->dhEmi ?? ''),
            'ambiente' => (int) ($infDps?->tpAmb ?? 1),
            'amb_gerador' => AmbGerador::labelFor($inf?->ambGer ?? ''),
            'tipo_emitente' => TpEmitente::labelFor($infDps?->tpEmit ?? ''),
            // Situação (cStat) é obrigatória; Finalidade (finNFSe) é condicional.
            'situacao' => ($inf?->cStat ?? '') !== '' ? SituacaoNFSe::labelFor($inf->cStat) : '',
            'finalidade' => $finNFSe !== '' ? FinNFSe::labelFor($finNFSe) : '',

            'prestador' => [
                'nome' => $prest?->xNome ?: '-',
                'cnpj_cpf' => $this->fmt->cnpjCpf($prest?->documento() ?? ''),
                'im' => $prest?->IM ?: '-',
                'telefone' => $this->fmt->phone($prest?->fone ?? ''),
                'email' => strtolower($prest?->email ?? ''),
                'endereco' => $this->enderecoLinha($prestEnd) ?: '-',
                'municipio' => $this->municipioUf($prestEnd) ?: '-',
                'codigo_ibge_cep' => $this->ibgeCepFrom($prestEnd) ?: '-',
                'simples_nacional' => OpSimpNac::labelFor($regTrib?->opSimpNac ?? ''),
                'regime_sn' => RegApTribSN::labelFor($regTrib?->regApTribSN ?? ''),
            ],

            'tomador' => [
                'nome' => $toma?->xNome ?? '-',
                'cnpj_cpf' => $this->fmt->cnpjCpf($toma?->documento() ?? ''),
                'im' => $toma?->IM ?: '-',
                'telefone' => $this->fmt->phone($toma?->fone ?? ''),
                'email' => strtolower($toma?->email ?? ''),
                'endereco' => $this->enderecoLinha($endToma) ?: '-',
                'municipio' => $this->municipioUf($endToma) ?: '-',
                'codigo_ibge_cep' => $this->ibgeCepFrom($endToma) ?: '-',
            ],

            'intermediario' => $interm !== null ? [
                'nome' => $interm->xNome ?: '-',
                'cnpj_cpf' => $this->fmt->cnpjCpf($interm->documento()),
                'im' => $interm->IM ?: '-',
                'telefone' => $this->fmt->phone($interm->fone),
                'email' => strtolower($interm->email),
                'endereco' => $this->enderecoLinha($endInterm) ?: '-',
                'municipio' => $this->municipioUf($endInterm) ?: '-',
                'codigo_ibge_cep' => $this->ibgeCepFrom($endInterm) ?: '-',
            ] : null,

            'destinatario' => $destinatario,
            'destinatario_msg' => $destinatarioMsg,
            'ibs_cbs' => $ibsCbs,

            'servico' => [
                'codigo_trib_nacional' => $this->fmt->codTribNacional($cServ?->cTribNac ?? ''),
                'desc_trib_nacional' => $this->fmt->limit(trim($inf?->xTribNac ?? ''), 60),
                'codigo_trib_municipal' => $cServ?->cTribMun ?? '-',
                'desc_trib_municipal' => $this->fmt->limit(trim($inf?->xTribMun ?? ''), 60),
                'codigo_nbs' => $cServ?->cNBS ?: '-',
                'local_prestacao' => $inf?->xLocPrestacao ?? '-',
                'pais_prestacao' => $locPrest?->cPaisPrestacao ?? '-',
                'descricao' => $cServ?->xDescServ ?? '-',
            ],

            'tributacao_municipal' => [
                'tributacao_issqn' => TribISSQN::labelFor($tribMun?->tribISSQN ?? ''),
                'pais_resultado' => $tribMun?->cPaisResult ?: '-',
                'municipio_incidencia' => $inf?->xLocIncid ?? '-',
                'regime_especial' => RegEspTrib::labelFor($regTrib?->regEspTrib ?? ''),
                'tipo_imunidade' => ($tribMun?->tpImunidade ?? '') !== ''
                    ? TpImunidade::labelFor($tribMun->tpImunidade)
                    : '-',
                'suspensao_exigibilidade' => $tribMun?->exigSusp
                    ? TpExigSusp::labelFor($tribMun->exigSusp->tpSusp)
                    : 'Não',
                'num_processo_suspensao' => $tribMun?->exigSusp?->nProcesso ?: '-',
                'beneficio_municipal' => ($valoresNfse?->tpBM ?? '') !== ''
                    ? TpBM::labelFor($valoresNfse->tpBM)
                    : '-',
                'calculo_bm' => $this->firstCurrency($valoresNfse?->vCalcBM ?? '', $tribMun?->BM?->vRedBCBM ?? ''),
                'total_deducoes' => $this->firstCurrency($valoresNfse?->vCalcDR ?? '', $tribMun?->vDeducao ?? ''),
                'desconto_incondicionado' => $tribMun?->vDescIncond ? $this->fmt->currency($tribMun->vDescIncond) : '-',
                'valor_servico' => $this->fmt->currency($vServPrest?->vServ ?? ''),
                'bc_issqn' => $tribMun?->vBC ? $this->fmt->currency($tribMun->vBC) : '-',
                'aliquota' => $tribMun?->pAliq ? $tribMun->pAliq . '%' : '-',
                'retencao_issqn' => TpRetISSQN::labelFor($tribMun?->tpRetISSQN ?? ''),
                'issqn_apurado' => $tribMun?->vISSQN ? $this->fmt->currency($tribMun->vISSQN) : '-',
            ],

            'tributacao_federal' => [
                'irrf' => $tribFed?->vRetIRRF ? $this->fmt->currency($tribFed->vRetIRRF) : '-',
                'cp' => $tribFed?->vRetCP ? $this->fmt->currency($tribFed->vRetCP) : '-',
                'contrib_sociais' => $tribFed?->vRetCSLL ? $this->fmt->currency($tribFed->vRetCSLL) : '-',
                'desc_contrib_sociais' => ($tribFed?->piscofins?->tpRetPisCofins ?? '') !== ''
                    ? TpRetPisCofins::labelFor($tribFed->piscofins->tpRetPisCofins)
                    : '-',
                'pis' => $tribFed?->piscofins?->vPis ? $this->fmt->currency($tribFed->piscofins->vPis) : '-',
                'cofins' => $tribFed?->piscofins?->vCofins ? $this->fmt->currency($tribFed->piscofins->vCofins) : '-',
                // Nota 6: visível apenas para competência até o fim de 2026.
                'mostrar_pis_cofins' => $mostrarPisCofins,
            ],

            'totais' => [
                'valor_servico' => $this->fmt->currency($vServPrest?->vServ ?? ''),
                'desconto_condicionado' => $tribMun?->vDescCond ? $this->fmt->currency($tribMun->vDescCond) : '-',
                'desconto_incondicionado' => $tribMun?->vDescIncond ? $this->fmt->currency($tribMun->vDescIncond) : '-',
                'issqn_retido' => ($tribMun?->vISSQN && ($tribMun?->tpRetISSQN ?? '1') !== '1')
                    ? $this->fmt->currency($tribMun->vISSQN)
                    : '-',
                'retencoes_federais' => $this->sumCurrency(
                    $tribFed?->vRetIRRF ?? '',
                    $tribFed?->vRetCP ?? '',
                    $tribFed?->vRetCSLL ?? '',
                ),
                'pis_cofins' => $this->sumCurrency(
                    $tribFed?->piscofins?->vPis ?? '',
                    $tribFed?->piscofins?->vCofins ?? '',
                ),
                'valor_liquido' => $this->fmt->currency($valoresNfse?->vLiq ?? ''),
                'total_ibs_cbs' => $this->sumCurrency(
                    $totC?->gIBS?->vIBSTot ?? '',
                    $totC?->gCBS?->vCBS ?? '',
                ),
                'valor_liquido_ibscbs' => ($totC?->vTotNF ?? '') !== ''
                    ? $this->fmt->currency($totC->vTotNF)
                    : '-',
            ],

            'totais_tributos' => [
                'federais' => $totTrib?->pTotTrib?->pTotTribFed ? $totTrib->pTotTrib->pTotTribFed . '%' : '-',
                'estaduais' => $totTrib?->pTotTrib?->pTotTribEst ? $totTrib->pTotTrib->pTotTribEst . '%' : '-',
                'municipais' => $totTrib?->pTotTrib?->pTotTribMun ? $totTrib->pTotTrib->pTotTribMun . '%' : '-',
            ],

            'informacoes_complementares' => $serv?->infoCompl?->xInfComp ?? '',
        ];
    }

    /**
     * Monta os campos do bloco de Tributação IBS/CBS a partir dos grupos
     * declarado (infDPS) e calculado pelo sistema (infNFSe).
     */
    private function buildIbsCbs(
        ?SitClasIbsCbs $gIBSCBS,
        ?RtcIBSCBS $dps,
        ?RtcIBSCBSNFSe $nfse,
        ?ValoresIbsCbs $val,
        ?TotCIbs $tot,
        ?TribMunicipal $tribMun,
        ?TribFederal $tribFed,
    ): array {
        $uf = $val?->uf;
        $mun = $val?->mun;
        $fed = $val?->fed;
        $gIBS = $tot?->gIBS;
        $gCBS = $tot?->gCBS;

        $cstClass = implode(' / ', array_filter([
            $gIBSCBS?->CST ?? '',
            $gIBSCBS?->cClassTrib ?? '',
        ], fn($v) => $v !== '')) ?: '-';

        $indicador = implode(' / ', array_filter([
            $dps?->cIndOp ?? '',
            $nfse?->cLocalidadeIncid ?? '',
            $nfse?->xLocalidadeIncid ?? '',
        ], fn($v) => $v !== '')) ?: '-';

        return [
            'cst_classtrib' => $cstClass,
            'indicador_operacao' => $indicador,
            'exclusoes_reducoes' => $this->sumCurrency(
                $tribMun?->vDescIncond ?? '',
                $val?->vCalcReeRepRes ?? '',
                $tribMun?->vISSQN ?? '',
                $tribFed?->piscofins?->vPis ?? '',
                $tribFed?->piscofins?->vCofins ?? '',
            ),
            'bc' => ($val?->vBC ?? '') !== '' ? $this->fmt->currency($val->vBC) : '-',
            'red_aliquota' => $this->pctList($uf?->pRedAliqUF ?? '', $mun?->pRedAliqMun ?? '', $fed?->pRedAliqCBS ?? ''),
            'aliquota_ibs' => $this->pctList($uf?->pIBSUF ?? '', $mun?->pIBSMun ?? ''),
            'aliq_efetiva_mun' => $this->pct($mun?->pAliqEfetMun ?? ''),
            'valor_apurado_mun' => ($gIBS?->gIBSMunTot?->vIBSMun ?? '') !== ''
                ? $this->fmt->currency($gIBS->gIBSMunTot->vIBSMun) : '-',
            'aliq_efetiva_uf' => $this->pct($uf?->pAliqEfetUF ?? ''),
            'valor_apurado_uf' => ($gIBS?->gIBSUFTot?->vIBSUF ?? '') !== ''
                ? $this->fmt->currency($gIBS->gIBSUFTot->vIBSUF) : '-',
            'valor_total_ibs' => ($gIBS?->vIBSTot ?? '') !== ''
                ? $this->fmt->currency($gIBS->vIBSTot) : '-',
            'aliquota_cbs' => $this->pct($fed?->pCBS ?? ''),
            'aliq_efetiva_cbs' => $this->pct($fed?->pAliqEfetCBS ?? ''),
            'valor_total_cbs' => ($gCBS?->vCBS ?? '') !== ''
                ? $this->fmt->currency($gCBS->vCBS) : '-',
        ];
    }

    /**
     * Formata um percentual (ex.: "2.00%") ou '-' quando vazio.
     */
    private function pct(string $v): string
    {
        return $v !== '' ? $v . '%' : '-';
    }

    /**
     * Formata uma lista de percentuais separados por " / " (ex.: "2.00% / 1.00%").
     */
    private function pctList(string ...$values): string
    {
        return implode(' / ', array_map(fn($v) => $v !== '' ? $v . '%' : '-', $values));
    }

    /**
     * Linha de endereço a partir de um Endereco (xLgr, nro, xCpl, xBairro).
     */
    private function enderecoLinha(?Endereco $e): string
    {
        if ($e === null) {
            return '';
        }
        return implode(', ', array_filter([
            $e->xLgr, $e->nro, $e->xCpl, $e->xBairro,
        ], fn($v) => $v !== ''));
    }

    /**
     * "Município / UF" a partir de um Endereco: nacional via tabela IBGE (cMun),
     * ou cidade/região para endereço no exterior (endExt).
     */
    private function municipioUf(?Endereco $e): string
    {
        if (($e?->endNac?->cMun ?? '') !== '') {
            return Municipios::lookup($e->endNac->cMun);
        }
        if ($e?->endExt !== null) {
            return implode(' / ', array_filter([
                $e->endExt->xCidade, $e->endExt->xEstProvReg,
            ], fn($v) => $v !== ''));
        }
        return '';
    }

    /**
     * "Código IBGE / CEP" a partir de um Endereco nacional, ou código postal (endExt).
     */
    private function ibgeCepFrom(?Endereco $e): string
    {
        if ($e?->endNac !== null) {
            return $this->ibgeCep($e->endNac->cMun, $e->endNac->CEP);
        }
        if ($e?->endExt !== null) {
            return $e->endExt->cEndPost;
        }
        return '';
    }

    /**
     * Concatena o código IBGE do município com o CEP (ex.: "3303302 / 24020-005").
     * Retorna string vazia quando ambos estão ausentes.
     */
    private function ibgeCep(string $cMun, string $cep): string
    {
        $cep = $this->fmt->cep($cep);
        $cep = $cep === '-' ? '' : $cep;

        return match (true) {
            $cMun !== '' && $cep !== '' => "{$cMun} / {$cep}",
            $cMun !== '' => $cMun,
            $cep !== '' => $cep,
            default => '',
        };
    }

    /**
     * Retorna o primeiro valor monetário não vazio já formatado, ou '-' se todos vazios.
     */
    private function firstCurrency(string ...$values): string
    {
        foreach ($values as $v) {
            if ($v !== '') {
                return $this->fmt->currency($v);
            }
        }
        return '-';
    }

    /**
     * Soma valores monetários e retorna formatado, ou '-' se todos forem vazios.
     */
    private function sumCurrency(string ...$values): string
    {
        $sum = 0.0;
        $hasValue = false;
        foreach ($values as $v) {
            if ($v !== '') {
                $sum += (float) $v;
                $hasValue = true;
            }
        }
        return $hasValue ? $this->fmt->currency((string) $sum) : '-';
    }

    /**
     * Gera QR Code como data URI PNG
     */
    private function generateQrCode(string $chaveAcesso): string
    {
        $url = "https://www.nfse.gov.br/ConsultaPublica/?tpc=1&chave={$chaveAcesso}";

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd(),
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($url);

        // Retorna como SVG embutido em data URI
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
