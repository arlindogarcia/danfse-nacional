<?php

namespace DanfseNacional\Dto;

/**
 * Valores brutos de IBS/CBS calculados pelo sistema (NFSe/infNFSe/IBSCBS/valores).
 */
readonly class ValoresIbsCbs
{
    public function __construct(
        public string $vBC = '',
        public string $vCalcReeRepRes = '',
        public ?IbsCbsUf $uf = null,
        public ?IbsCbsMun $mun = null,
        public ?IbsCbsFed $fed = null,
    ) {}
}
