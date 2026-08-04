<?php

namespace DanfseNacional\Dto;

readonly class TotTrib
{
    public function __construct(
        public ?TotTribValue $vTotTrib = null,
        public ?TotTribPercent $pTotTrib = null,
        public string $indTotTrib = '',
        public string $pTotTribSN = '',
    ) {}
}
