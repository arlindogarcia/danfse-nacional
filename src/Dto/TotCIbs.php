<?php

namespace DanfseNacional\Dto;

/**
 * Totalizadores de IBS/CBS (NFSe/infNFSe/IBSCBS/totCIBS).
 */
readonly class TotCIbs
{
    public function __construct(
        public string $vTotNF = '',
        public ?TotIbs $gIBS = null,
        public ?TotCbs $gCBS = null,
    ) {}
}
