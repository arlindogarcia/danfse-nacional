<?php

namespace DanfseNacional\Dto;

/**
 * Valores do serviço prestado para IBS/CBS declarados na DPS
 * (.../IBSCBS/valores = TCRTCInfoValoresIBSCBS).
 */
readonly class InfoValoresIbsCbs
{
    public function __construct(
        public ?TribIbsCbs $trib = null,
    ) {}
}
