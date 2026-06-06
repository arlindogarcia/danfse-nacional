<?php

namespace DanfseNacional\Dto;

/**
 * Grupo IBS/CBS calculado pelo sistema, no nível da NFS-e
 * (NFSe/infNFSe/IBSCBS = TCRTCIBSCBS).
 */
readonly class RtcIBSCBSNFSe
{
    public function __construct(
        public string $cLocalidadeIncid = '',
        public string $xLocalidadeIncid = '',
        public string $pRedutor = '',
        public ?ValoresIbsCbs $valores = null,
        public ?TotCIbs $totCIBS = null,
    ) {}
}
