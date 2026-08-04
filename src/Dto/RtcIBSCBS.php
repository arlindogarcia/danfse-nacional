<?php

namespace DanfseNacional\Dto;

/**
 * Grupo IBS/CBS declarado na DPS (NFSe/infNFSe/DPS/infDPS/IBSCBS = TCRTCInfoIBSCBS).
 */
readonly class RtcIBSCBS
{
    public function __construct(
        public string $finNFSe = '',
        public string $cIndOp = '',
        public string $indDest = '',
        public ?Destinatario $dest = null,
        public ?InfoValoresIbsCbs $valores = null,
        public ?Imovel $imovel = null,
    ) {}
}
