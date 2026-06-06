<?php

namespace DanfseNacional\Dto;

readonly class TotIbs
{
    public function __construct(
        public string $vIBSTot = '',
        public ?TotIbsUf $gIBSUFTot = null,
        public ?TotIbsMun $gIBSMunTot = null,
    ) {}
}
