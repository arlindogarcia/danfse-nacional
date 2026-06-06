<?php

namespace DanfseNacional\Dto;

readonly class BeneficioMunicipal
{
    public function __construct(
        public string $nBM = '',
        public string $vRedBCBM = '',
        public string $pRedBCBM = '',
    ) {}
}
