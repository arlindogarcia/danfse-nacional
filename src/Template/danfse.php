<?php
/** @var array $data */
/** @var string $logo */
/** @var string $qrCode */
/** @var \DanfseNacional\Config\MunicipalityBranding $municipality */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>DANFSe - <?= $data['numero_nfse'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            color: #000;
            margin: 7pt;
            padding: 4pt 7pt;
            border: 1pt #000 solid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        td {
            padding: 1pt 2pt;
            border: none;
            vertical-align: top;
        }

        table > tbody > tr > td {
            padding-bottom: 3pt;
        }

        .bordered-section {
            margin-bottom: 1pt;
            border-bottom: 1px solid #000;
        }

        .bordered-section:last-of-type {
            border-bottom: none;
        }

        .first-section table td {
            padding-bottom: 0 !important;
        }

        .label {
            font-size: 7pt;
            font-weight: bold;
            color: #000;
            display: block;
            margin-bottom: 2pt;
        }

        .value {
            font-size: 8pt;
            font-weight: normal;
            color: #000;
        }

        .section-header {
            font-weight: bold;
            font-size: 8pt;
            text-align: left;
            padding: 3pt;
        }

        .section-title {
            font-size: 8pt;
        }

        .header-table {
            margin-bottom: 2pt;
            border-bottom: 1px solid #000;
        }

        .header-table td {
            border: none;
            padding-bottom: 1pt !important;
        }

        .logo-cell {
            width: 130pt;
            text-align: left;
        }

        .title-cell {
            text-align: center;
            vertical-align: middle;
        }

        .municipality-cell {
            width: 150pt;
            text-align: left;
            font-size: 5.5pt;
            vertical-align: top;
        }

        .qr-container {
            text-align: center;
            /*padding: 3pt;*/
            position: absolute;
            right: 0;
            top: 0;
        }

        /* Watermark para homologação */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48pt;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.3);
            z-index: -1;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php if ($data['ambiente'] == 2): ?>
    <div class="watermark">HOMOLOGAÇÃO</div>
    <?php endif; ?>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <?php if ($logo): ?>
                <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="max-width: 130pt; max-height: 40pt;">
                <?php endif; ?>
            </td>
            <td class="title-cell">
                <div style="font-size: 10pt; font-weight: bold;">DANFSe v2.0</div>
                <div style="font-size: 8pt; font-weight: bold;">Documento Auxiliar da NFS-e</div>
                <?php if ($data['ambiente'] == 2): ?>
                    <div style="color: red; font-weight: bold;">NFS-e SEM VALIDADE JURÍDICA</div>
                <?php endif; ?>
            </td>
            <td class="municipality-cell">
                <?php if ($municipality): ?>
                <table>
                    <tr>
                        <?php if ($municipality->logoDataUri): ?>
                        <td><img style="height: 30pt; width: auto" src="<?= htmlspecialchars($municipality->logoDataUri) ?>" alt="Prefeitura" /></td>
                        <?php endif; ?>
                        <td style="font-size: 7pt;">
                            <?= htmlspecialchars($municipality->name) ?><br>
                            <?php if ($municipality->department): ?>
                            <?= htmlspecialchars($municipality->department) ?><br>
                            <?php endif; ?>
                            <?php if ($municipality->email): ?>
                            <?= htmlspecialchars($municipality->email) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <?php endif; ?>
                <div style="font-size: 5.5pt; padding-top: 1pt;">
                    Ambiente Gerador: <?= $data['amb_gerador'] ?><br>
                    Tipo de Ambiente: <?= $data['ambiente'] == 2 ? 'Homologação' : 'Produção' ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- Grade de Identificação -->
    <div class="bordered-section first-section">
        <table style="min-height: 110px;">
            <tr>
                <td colspan="3">
                    <span class="label">Chave de Acesso da NFS-e</span>
                    <span class="value"><?= $data['chave_acesso'] ?></span>
                </td>
                <td style="width: 25%; position: relative;" rowspan="3">
                    <div class="qr-container">
                        <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR Code" style="width: 70px; height: 70px; display: block; margin: 0 auto;" />
                        <div style="font-size: 6pt; padding-top: 2pt; text-align: left; line-height: 1.2;">
                            A autenticidade desta NFS-e pode ser verificada pela leitura deste código QR ou pela consulta da chave de acesso no portal nacional da NFS-e
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 25%;">
                    <span class="label">Número da NFS-e</span>
                    <span class="value"><?= $data['numero_nfse'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Competência da NFS-e</span>
                    <span class="value"><?= $data['competencia'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Data e Hora da emissão da NFS-e</span>
                    <span class="value"><?= $data['emissao_nfse'] ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Número do DPS</span>
                    <span class="value"><?= $data['numero_dps'] ?></span>
                </td>
                <td>
                    <span class="label">Série do DPS</span>
                    <span class="value"><?= $data['serie_dps'] ?></span>
                </td>
                <td>
                    <span class="label">Data e Hora da emissão da DPS</span>
                    <span class="value"><?= $data['emissao_dps'] ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Emitente da NFS-e</span>
                    <span class="value"><?= $data['tipo_emitente'] ?></span>
                </td>
                <td>
                    <?php if ($data['situacao'] !== ''): ?>
                    <span class="label">Situação da NFS-e</span>
                    <span class="value"><?= $data['situacao'] ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($data['finalidade'] !== ''): ?>
                    <span class="label">Finalidade</span>
                    <span class="value"><?= $data['finalidade'] ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Emitente -->
    <div class="bordered-section">
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold; font-size: 7pt;">
                    <span class="section-title">PRESTADOR / FORNECEDOR</span>
                </td>
                <td style="width: 25%;">
                    <span class="label">CNPJ / CPF / NIF</span>
                    <span class="value"><?= $data['prestador']['cnpj_cpf'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Inscrição Municipal</span>
                    <span class="value"><?= $data['prestador']['im'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Telefone</span>
                    <span class="value"><?= $data['prestador']['telefone'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Nome / Nome Empresarial</span>
                    <span class="value"><?= $data['prestador']['nome'] ?></span>
                </td>
                <td colspan="2">
                    <span class="label">E-mail</span>
                    <span class="value"><?= $data['prestador']['email'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Endereço</span>
                    <span class="value"><?= $data['prestador']['endereco'] ?></span>
                </td>
                <td>
                    <span class="label">Município</span>
                    <span class="value"><?= $data['prestador']['municipio'] ?></span>
                </td>
                <td>
                    <span class="label">Código IBGE / CEP</span>
                    <span class="value"><?= $data['prestador']['codigo_ibge_cep'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Simples Nacional na Data de Competência</span>
                    <span class="value"><?= $data['prestador']['simples_nacional'] ?></span>
                </td>
                <td colspan="2">
                    <span class="label">Regime de Apuração Tributária pelo SN</span>
                    <span class="value"><?= $data['prestador']['regime_sn'] ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tomador -->
    <div class="bordered-section">
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold; font-size: 7pt;">
                    <span class="section-title">TOMADOR DO SERVIÇO</span>
                </td>
                <td style="width: 25%;">
                    <span class="label">CNPJ / CPF / NIF</span>
                    <span class="value"><?= $data['tomador']['cnpj_cpf'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Inscrição Municipal</span>
                    <span class="value"><?= $data['tomador']['im'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Telefone</span>
                    <span class="value"><?= $data['tomador']['telefone'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="width: 50%;">
                    <span class="label">Nome / Nome Empresarial</span>
                    <span class="value"><?= $data['tomador']['nome'] ?></span>
                </td>
                <td colspan="2" style="width: 50%;">
                    <span class="label">E-mail</span>
                    <span class="value"><?= $data['tomador']['email'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="width: 50%;">
                    <span class="label">Endereço</span>
                    <span class="value"><?= $data['tomador']['endereco'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Município</span>
                    <span class="value"><?= $data['tomador']['municipio'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Código IBGE / CEP</span>
                    <span class="value"><?= $data['tomador']['codigo_ibge_cep'] ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Destinatário -->
    <?php if ($data['destinatario'] !== null): ?>
    <div class="bordered-section">
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold; font-size: 7pt;">
                    <span class="section-title">DESTINATÁRIO DA OPERAÇÃO</span>
                </td>
                <td style="width: 25%;">
                    <span class="label">CNPJ / CPF / NIF</span>
                    <span class="value"><?= $data['destinatario']['cnpj_cpf'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Telefone</span>
                    <span class="value"><?= $data['destinatario']['telefone'] ?></span>
                </td>
                <td style="width: 25%;"></td>
            </tr>
            <tr>
                <td colspan="2" style="width: 50%;">
                    <span class="label">Nome / Nome Empresarial</span>
                    <span class="value"><?= $data['destinatario']['nome'] ?></span>
                </td>
                <td colspan="2" style="width: 50%;">
                    <span class="label">E-mail</span>
                    <span class="value"><?= $data['destinatario']['email'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="width: 50%;">
                    <span class="label">Endereço</span>
                    <span class="value"><?= $data['destinatario']['endereco'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Município</span>
                    <span class="value"><?= $data['destinatario']['municipio'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Código IBGE / CEP</span>
                    <span class="value"><?= $data['destinatario']['codigo_ibge_cep'] ?></span>
                </td>
            </tr>
        </table>
    </div>
    <?php else: ?>
    <div class="bordered-section" style="text-align: center; font-weight: normal; font-size: 7pt;">
        <?= $data['destinatario_msg'] ?>
    </div>
    <?php endif; ?>

    <!-- Intermediário -->
    <?php if ($data['intermediario'] !== null): ?>
    <div class="bordered-section">
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold; font-size: 7pt;">
                  <span class="section-title">INTERMEDIÁRIO DO SERVIÇO</span>
                </td>
                <td style="width: 25%;">
                    <span class="label">CNPJ / CPF</span>
                    <span class="value"><?= $data['intermediario']['cnpj_cpf'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Inscrição Municipal</span>
                    <span class="value"><?= $data['intermediario']['im'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Telefone</span>
                    <span class="value"><?= $data['intermediario']['telefone'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="width: 50%;">
                    <span class="label">Nome / Nome Empresarial</span>
                    <span class="value"><?= $data['intermediario']['nome'] ?></span>
                </td>
                <td colspan="2" style="width: 50%;">
                    <span class="label">E-mail</span>
                    <span class="value"><?= $data['intermediario']['email'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="width: 50%;">
                    <span class="label">Endereço</span>
                    <span class="value"><?= $data['intermediario']['endereco'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Município</span>
                    <span class="value"><?= $data['intermediario']['municipio'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Código IBGE / CEP</span>
                    <span class="value"><?= $data['intermediario']['codigo_ibge_cep'] ?></span>
                </td>
            </tr>
        </table>
    </div>
    <?php else: ?>
    <div class="bordered-section" style="text-align: center; font-weight: normal; font-size: 7pt;">
        INTERMEDIÁRIO DO SERVIÇO NÃO IDENTIFICADO NA NFS-e
    </div>
    <?php endif; ?>

    <!-- Serviço Prestado -->
    <div class="bordered-section">
        <table>
            <tr>
                <td colspan="4" class="section-header">
                  <span class="section-title">SERVIÇO PRESTADO</span>
                </td>
            </tr>
            <tr>
                <td style="width: 25%;">
                    <span class="label">Código de Tributação Nacional</span>
                    <span class="value"><?= $data['servico']['codigo_trib_nacional'] ?> - <?= $data['servico']['desc_trib_nacional'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Código de Tributação Municipal</span>
                    <span class="value"><?= $data['servico']['codigo_trib_municipal'] ?> - <?= $data['servico']['desc_trib_municipal'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Local da Prestação</span>
                    <span class="value"><?= $data['servico']['local_prestacao'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">País da Prestação</span>
                    <span class="value"><?= $data['servico']['pais_prestacao'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <span class="label">Código da NBS</span>
                    <span class="value"><?= $data['servico']['codigo_nbs'] ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <span class="label">Descrição do Serviço</span>
                    <span class="value"><?= $data['servico']['descricao'] ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tributação Municipal -->
    <div class="bordered-section">
        <table>
            <tr>
                <td colspan="4" class="section-header">
                  <span class="section-title">TRIBUTAÇÃO MUNICIPAL</span>
                </td>
            </tr>
            <tr>
                <td style="width: 25%;">
                    <span class="label">Tributação do ISSQN</span>
                    <span class="value"><?= $data['tributacao_municipal']['tributacao_issqn'] ?? '-' ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">País Resultado da Prestação do Serviço</span>
                    <span class="value"><?= $data['tributacao_municipal']['pais_resultado'] ?? '-' ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Município de Incidência do ISSQN</span>
                    <span class="value"><?= $data['tributacao_municipal']['municipio_incidencia'] ?? '-' ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Regime Especial de Tributação</span>
                    <span class="value"><?= $data['tributacao_municipal']['regime_especial'] ?? '-' ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Tipo de Imunidade</span>
                    <span class="value"><?= $data['tributacao_municipal']['tipo_imunidade'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">Suspensão da Exigibilidade do ISSQN</span>
                    <span class="value"><?= $data['tributacao_municipal']['suspensao_exigibilidade'] ?? 'Não' ?></span>
                </td>
                <td>
                    <span class="label">Número Processo Suspensão</span>
                    <span class="value"><?= $data['tributacao_municipal']['num_processo_suspensao'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">Benefício Municipal</span>
                    <span class="value"><?= $data['tributacao_municipal']['beneficio_municipal'] ?? '-' ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Valor do Serviço</span>
                    <span class="value"><?= $data['tributacao_municipal']['valor_servico'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">Desconto Incondicionado</span>
                    <span class="value"><?= $data['tributacao_municipal']['desconto_incondicionado'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">Total Deduções/Reduções</span>
                    <span class="value"><?= $data['tributacao_municipal']['total_deducoes'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">Cálculo do BM</span>
                    <span class="value"><?= $data['tributacao_municipal']['calculo_bm'] ?? '-' ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">BC ISSQN</span>
                    <span class="value"><?= $data['tributacao_municipal']['bc_issqn'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">Alíquota Aplicada</span>
                    <span class="value"><?= $data['tributacao_municipal']['aliquota'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">Retenção do ISSQN</span>
                    <span class="value"><?= $data['tributacao_municipal']['retencao_issqn'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">ISSQN Apurado</span>
                    <span class="value"><?= $data['tributacao_municipal']['issqn_apurado'] ?? '-' ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tributação Federal -->
    <div class="bordered-section">
        <table>
            <tr>
                <td colspan="4" class="section-header">
                  <span class="section-title">TRIBUTAÇÃO FEDERAL</span>
                </td>
            </tr>
            <tr>
                <td style="width: 25%;">
                    <span class="label">IRRF</span>
                    <span class="value"><?= $data['tributacao_federal']['irrf'] ?? '-' ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Contribuição Previdenciária - Retida</span>
                    <span class="value"><?= $data['tributacao_federal']['cp'] ?? '-' ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Contribuições Sociais - Retidas</span>
                    <span class="value"><?= $data['tributacao_federal']['contrib_sociais'] ?? '-' ?></span>
                </td>
                <td style="width: 25%;">
                    <?php if ($data['tributacao_federal']['mostrar_pis_cofins']): ?>
                    <span class="label">Descrição Contrib. Sociais - Retidas</span>
                    <span class="value"><?= $data['tributacao_federal']['desc_contrib_sociais'] ?? '-' ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($data['tributacao_federal']['mostrar_pis_cofins']): ?>
            <tr>
                <td colspan="2">
                    <span class="label">PIS - Débito Apuração Própria</span>
                    <span class="value"><?= $data['tributacao_federal']['pis'] ?? '-' ?></span>
                </td>
                <td colspan="2">
                    <span class="label">COFINS - Débito Apuração Própria</span>
                    <span class="value"><?= $data['tributacao_federal']['cofins'] ?? '-' ?></span>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Tributação IBS/CBS -->
    <?php if ($data['ibs_cbs'] !== null): ?>
    <div class="bordered-section">
        <table>
            <tr>
                <td colspan="4" class="section-header">
                    <span class="section-title">TRIBUTAÇÃO IBS / CBS</span>
                </td>
            </tr>
            <tr>
                <td style="width: 25%;">
                    <span class="label">CST / cClassTrib</span>
                    <span class="value"><?= $data['ibs_cbs']['cst_classtrib'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Ind. Operação / Cód. IBGE / Município Incidência</span>
                    <span class="value"><?= $data['ibs_cbs']['indicador_operacao'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Exclusões e Reduções da BC</span>
                    <span class="value"><?= $data['ibs_cbs']['exclusoes_reducoes'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">BC após Exclusões e Reduções</span>
                    <span class="value"><?= $data['ibs_cbs']['bc'] ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Red. Alíquota IBS / CBS</span>
                    <span class="value"><?= $data['ibs_cbs']['red_aliquota'] ?></span>
                </td>
                <td>
                    <span class="label">Alíquota IBS UF / Mun</span>
                    <span class="value"><?= $data['ibs_cbs']['aliquota_ibs'] ?></span>
                </td>
                <td>
                    <span class="label">Alíq. Efetiva Municipal - IBS</span>
                    <span class="value"><?= $data['ibs_cbs']['aliq_efetiva_mun'] ?></span>
                </td>
                <td>
                    <span class="label">Valor Apurado Municipal - IBS</span>
                    <span class="value"><?= $data['ibs_cbs']['valor_apurado_mun'] ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Alíq. Efetiva Estadual - IBS</span>
                    <span class="value"><?= $data['ibs_cbs']['aliq_efetiva_uf'] ?></span>
                </td>
                <td>
                    <span class="label">Valor Apurado Estadual - IBS</span>
                    <span class="value"><?= $data['ibs_cbs']['valor_apurado_uf'] ?></span>
                </td>
                <td>
                    <span class="label">Valor Total Apurado - IBS</span>
                    <span class="value"><?= $data['ibs_cbs']['valor_total_ibs'] ?></span>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <span class="label">Alíquota - CBS</span>
                    <span class="value"><?= $data['ibs_cbs']['aliquota_cbs'] ?></span>
                </td>
                <td>
                    <span class="label">Alíquota Efetiva - CBS</span>
                    <span class="value"><?= $data['ibs_cbs']['aliq_efetiva_cbs'] ?></span>
                </td>
                <td>
                    <span class="label">Valor Total Apurado - CBS</span>
                    <span class="value"><?= $data['ibs_cbs']['valor_total_cbs'] ?></span>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    <!-- Valor Total -->
    <div class="bordered-section">
        <table>
            <tr>
                <td colspan="4" class="section-header">
                  <span class="section-title">VALOR TOTAL DA NFS-e</span>
                </td>
            </tr>
            <tr>
                <td style="width: 25%;">
                    <span class="label">Valor do Serviço</span>
                    <span class="value"><?= $data['totais']['valor_servico'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Desconto Condicionado</span>
                    <span class="value"><?= $data['totais']['desconto_condicionado'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Desconto Incondicionado</span>
                    <span class="value"><?= $data['totais']['desconto_incondicionado'] ?></span>
                </td>
                <td style="width: 25%;">
                    <span class="label">ISSQN Retido</span>
                    <span class="value"><?= $data['totais']['issqn_retido'] ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Total das Retenções Federais</span>
                    <span class="value"><?= $data['totais']['retencoes_federais'] ?? '-' ?></span>
                </td>
                <td colspan="2">
                    <span class="label">PIS/COFINS - Débito Apur. Própria</span>
                    <span class="value"><?= $data['totais']['pis_cofins'] ?? '-' ?></span>
                </td>
                <td>
                    <span class="label">Valor Líquido da NFS-e</span>
                    <span class="value" style="font-weight: bold;"><?= $data['totais']['valor_liquido'] ?></span>
                </td>
            </tr>
            <?php if ($data['ibs_cbs'] !== null): ?>
            <tr>
                <td colspan="2">
                    <span class="label">Total do IBS/CBS</span>
                    <span class="value"><?= $data['totais']['total_ibs_cbs'] ?></span>
                </td>
                <td colspan="2">
                    <span class="label">Valor Líquido da NFS-e + IBS/CBS</span>
                    <span class="value" style="font-weight: bold;"><?= $data['totais']['valor_liquido_ibscbs'] ?></span>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Totais Aproximados de Tributos -->
    <div class="bordered-section">
        <table>
            <tr>
                <td colspan="3" class="section-header">
                  <span class="section-title">TOTAIS APROXIMADOS DOS TRIBUTOS</span>
                </td>
            </tr>
            <tr>
                <td style="width: 33.33%; text-align: center;">
                    <span class="label">Federais</span>
                    <span class="value"><?= $data['totais_tributos']['federais'] ?? '-' ?></span>
                </td>
                <td style="width: 33.33%; text-align: center;">
                    <span class="label">Estaduais</span>
                    <span class="value"><?= $data['totais_tributos']['estaduais'] ?? '-' ?></span>
                </td>
                <td style="width: 33.33%; text-align: center;">
                    <span class="label">Municipais</span>
                    <span class="value"><?= $data['totais_tributos']['municipais'] ?? '-' ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Informações Complementares -->
    <div class="bordered-section">
        <table>
            <tr>
                <td class="section-header">
                  <span class="section-title">INFORMAÇÕES COMPLEMENTARES</span>
                </td>
            </tr>
            <tr>
                <td style="min-height: 30pt; padding: 5pt;">
                    <span class="value"><?= $data['informacoes_complementares'] ?></span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
