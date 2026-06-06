<?php

namespace DanfseNacional\Dto;

/**
 * Destinatário da operação (NFSe/infNFSe/DPS/infDPS/IBSCBS/dest = TCRTCInfoDest).
 */
readonly class Destinatario
{
    public function __construct(
        public string $CNPJ = '',
        public string $CPF = '',
        public string $NIF = '',
        public string $cNaoNIF = '',
        public string $xNome = '',
        public ?Endereco $end = null,
        public string $fone = '',
        public string $email = '',
    ) {}

    public function documento(): string
    {
        return $this->CNPJ ?: ($this->CPF ?: $this->NIF);
    }
}
