<?php

namespace DanfseNacional\Dto;

readonly class Prestador
{
    public function __construct(
        public string $CNPJ = '',
        public string $CPF = '',
        public string $NIF = '',
        public string $IM = '',
        public string $xNome = '',
        public ?Endereco $end = null,
        public ?RegTrib $regTrib = null,
        public string $fone = '',
        public string $email = '',
    ) {}

    public function documento(): string
    {
        return $this->CNPJ ?: ($this->CPF ?: $this->NIF);
    }
}
