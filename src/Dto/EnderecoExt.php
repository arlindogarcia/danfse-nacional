<?php

namespace DanfseNacional\Dto;

/**
 * Endereço no exterior (TCEnderExt): país, código postal, cidade e região.
 */
readonly class EnderecoExt
{
    public function __construct(
        public string $cPais = '',
        public string $cEndPost = '',
        public string $xCidade = '',
        public string $xEstProvReg = '',
    ) {}
}
