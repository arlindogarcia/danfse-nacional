<?php

namespace DanfseNacional\Dto;

/**
 * Situação/Classificação tributária do IBS/CBS declarada na DPS
 * (.../IBSCBS/valores/trib/gIBSCBS = TCRTCInfoTributosSitClas).
 */
readonly class SitClasIbsCbs
{
    public function __construct(
        public string $CST = '',
        public string $cClassTrib = '',
    ) {}
}
