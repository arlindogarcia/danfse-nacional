<?php

namespace DanfseNacional\Dto;

readonly class TotTribValue
{
    public function __construct(
        public string $vTotTribFed = '',
        public string $vTotTribEst = '',
        public string $vTotTribMu = '',
    ) {}
}
