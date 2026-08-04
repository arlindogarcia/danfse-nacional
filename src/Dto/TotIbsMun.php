<?php

namespace DanfseNacional\Dto;

readonly class TotIbsMun
{
    public function __construct(
        public string $vDifMun = '',
        public string $vIBSMun = '',
    ) {}
}
