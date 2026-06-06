<?php

namespace DanfseNacional\Dto;

readonly class TotIbsUf
{
    public function __construct(
        public string $vDifUF = '',
        public string $vIBSUF = '',
    ) {}
}
