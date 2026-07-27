# DANFSe Nacional

Biblioteca PHP para geração de PDF do DANFSe (Documento Auxiliar da Nota Fiscal de Serviços eletrônica) a partir do XML da NFS-e Padrão Nacional.

A biblioteca recebe o XML de uma NFS-e autorizada e devolve o conteúdo binário de um PDF em A4 retrato, sem nenhuma dependência de framework. Pode ser usada em projetos Laravel, Symfony, ou em scripts PHP puro.

O layout segue as exigências da **NT 008 (DANFSe v2.0)**: todos os campos são exibidos na mesma ordem definida pela norma e formatados conforme suas regras. Em vez de reproduzir coordenadas fixas, o documento adota um layout fluido — o que o torna adaptável a diferentes volumes de conteúdo sem perda de legibilidade.

Nas NFS-e do ambiente de Homologação, o PDF exibe a mensagem "NFS-e SEM VALIDADE JURÍDICA", conforme previsto na norma. Para notas canceladas ou substituídas, é possível exibir uma marca d’água correspondente — veja a seção [Marca d’água](#marca-dágua) abaixo.

## Exemplos

- [DANFSe - Produção](examples/danfse.pdf)
- [DANFSe - Homologação](examples/danfse-homologacao.pdf)

## Requisitos

PHP >= 8.2 com as extensões `simplexml`, `mbstring` e `fileinfo` habilitadas.

## Instalação

```bash
composer require andrevabo/danfse-nacional
```

## Uso básico

O caminho mais direto é passar o XML e receber o PDF em uma única chamada.

```php
use DanfseNacional\DanfseGenerator;

$xml = file_get_contents('nfse_autorizada.xml');

$generator = new DanfseGenerator();
$pdf = $generator->generateFromXml($xml);

file_put_contents('danfse.pdf', $pdf);
```

## Identificação do município

O cabeçalho do DANFSe exibe automaticamente o município e a UF do ente emissor, extraídos do próprio XML (`infNFSe/xLocEmi` e `infNFSe/emit/enderNac/UF`). A identificação não é exibida quando o código de tributação nacional do serviço for `99` (conforme a NT-008).

## Marca d'água

Para notas canceladas ou substituídas, configure as flags correspondentes em `DanfseConfig`. O PDF exibirá uma marca d'água "CANCELADA" ou "SUBSTITUÍDA" em diagonal sobre o documento.

```php
// Nota cancelada
$config = new DanfseConfig(canceled: true);
$pdf = (new DanfseGenerator($config))->generateFromXml($xml);

// Nota substituída
$config = new DanfseConfig(substituted: true);
$pdf = (new DanfseGenerator($config))->generateFromXml($xml);
```

## Geração em dois passos

É possível acessar o método `parseXml()` para obter um objeto `DanfseNacional\Dto\NFSe` com os dados da NFS-e antes de gerar o PDF.

```php
use DanfseNacional\DanfseGenerator;

$generator = new DanfseGenerator();

$nfse = $generator->parseXml($xml);

// Acessa os dados tipados via DTOs
$numeroNfse = $nfse->infNFSe->nNFSe;
$cnpjEmitente = $nfse->infNFSe->emit->CNPJ;
$valorLiquido = $nfse->infNFSe->valores->vLiq;
$descricaoServico = $nfse->infNFSe->DPS->infDPS->serv->cServ->xDescServ;

$pdf = $generator->generatePdf($nfse);
```

## Geração do HTML intermediário

Para inspecionar o HTML gerado antes da renderização, útil em testes e depuração, use `generateHtml()`.

```php
use DanfseNacional\DanfseGenerator;

$generator = new DanfseGenerator();
$nfse = $generator->parseXml($xml);

$html = $generator->generateHtml($nfse);
```

## Entrega da resposta em aplicações web

Em vez de salvar o arquivo em disco, o conteúdo binário do PDF pode ser enviado diretamente como resposta HTTP.

```php
// PHP puro
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="danfse.pdf"');
echo $pdf;

// Laravel
return response($pdf, 200, [
    'Content-Type' => 'application/pdf',
    'Content-Disposition' => 'inline; filename="danfse.pdf"',
]);
```

## Estrutura dos dados mapeados

O método `parseXml()` retorna um objeto `DanfseNacional\Dto\NFSe` com propriedades tipadas e `readonly`. A hierarquia segue a estrutura do XML da NFS-e Nacional:

```
NFSe
└── infNFSe (InfNFSe)
    ├── emit (Emitente)
    │   └── enderNac (EnderecoEmitente)
    ├── valores (ValoresNFSe)
    ├── IBSCBS (RtcIBSCBSNFSe)
    │   ├── valores (ValoresIbsCbs)
    │   └── totCIBS (TotCIbs)
    │       ├── gIBS (TotIbs)
    │       │   ├── gIBSMun (TotIbsMun)
    │       │   └── gIBSUF (TotIbsUf)
    │       └── gCBS (TotCbs)
    └── DPS (Dps)
        └── infDPS (InfDPS)
            ├── prest (Prestador)
            │   ├── end (Endereco)
            │   │   ├── endNac (EnderecoNacional)
            │   │   └── endExt (EnderecoExt)
            │   └── regTrib (RegTrib)
            ├── toma (Tomador)
            │   └── end (Endereco)
            │       ├── endNac (EnderecoNacional)
            │       └── endExt (EnderecoExt)
            ├── interm (Intermediario)
            │   └── end (Endereco)
            │       ├── endNac (EnderecoNacional)
            │       └── endExt (EnderecoExt)
            ├── serv (Servico)
            │   ├── locPrest (LocPrest)
            │   ├── cServ (CServ)
            │   ├── infoCompl (InfoCompl)
            │   │   └── gItemPed (GItemPed)
            │   ├── obra (Obra)
            │   └── atvEvento (AtvEvento)
            ├── valores (Valores)
            │   ├── vServPrest (VServPrest)
            │   └── trib (Tributacao)
            │       ├── tribMun (TribMunicipal)
            │       │   ├── exigSusp (ExigSuspensa)
            │       │   └── BM (BeneficioMunicipal)
            │       ├── tribFed (TribFederal)
            │       │   └── piscofins (PisCofins)
            │       └── totTrib (TotTrib)
            │           ├── vTotTrib (TotTribValue)
            │           └── pTotTrib (TotTribPercent)
            ├── IBSCBS (RtcIBSCBS)
            │   ├── dest (Destinatario)
            │   │   └── end (Endereco)
            │   ├── valores (InfoValoresIbsCbs)
            │   │   └── trib (TribIbsCbs)
            │   │       └── gIBSCBS (SitClasIbsCbs)
            │   └── imovel (Imovel)
            └── subst (Subst)
```

Todos os campos opcionais no esquema da NFS-e são representados como propriedades `nullable` ou com valor padrão de string vazia, portanto o acesso nunca lança exceções por campo ausente.

## Dependências

A biblioteca depende exclusivamente de pacotes sem acoplamento a frameworks:

`dompdf/dompdf` para renderização do HTML em PDF, `cuyz/valinor` para o mapeamento seguro do array XML para os DTOs tipados, e `bacon/bacon-qr-code` para a geração do QR Code de consulta pública.

## Testes

```bash
composer install
./vendor/bin/phpunit
```
