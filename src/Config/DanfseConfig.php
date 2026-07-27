<?php

namespace DanfseNacional\Config;

readonly class DanfseConfig
{
    public ?string $logo;

    public function __construct(
        public bool $canceled = false,
        public bool $substituted = false,
    ) {
        $this->logo = self::defaultLogo();
    }

    private static function defaultLogo(): ?string
    {
        $path = __DIR__ . '/../../assets/logo-nfse.png';
        return is_readable($path) ? self::pathToDataUri($path) : null;
    }

    private static function pathToDataUri(string $path): string
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Arquivo de logo não encontrado ou ilegível: {$path}");
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException("Não foi possível ler o arquivo de logo: {$path}");
        }

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }
}
