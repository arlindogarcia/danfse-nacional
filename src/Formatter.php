<?php

namespace DanfseNacional;

/**
 * Formatadores para padrões brasileiros (CNPJ, CPF, telefone, CEP, moeda, datas)
 */
class Formatter
{
    public function cnpjCpf(string $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        // Remove apenas separadores da máscara; preserva letras do CNPJ alfanumérico.
        $clean = preg_replace('/[\.\-\/\s]/', '', $value);

        // CNPJ: 14 caracteres alfanuméricos com os 2 últimos (DV) obrigatoriamente numéricos.
        if (strlen($clean) === 14 && preg_match('/^[A-Z0-9]{12}\d{2}$/i', $clean)) {
            $clean = strtoupper($clean);
            return preg_replace('/^([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{3})([A-Z0-9]{4})(\d{2})$/', '$1.$2.$3/$4-$5', $clean);
        }

        // CPF: 11 dígitos numéricos.
        if (strlen($clean) === 11 && ctype_digit($clean)) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean);
        }

        // NIF ou outro identificador: retorna como informado.
        return $value;
    }

    public function phone(string $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        $value = preg_replace('/\D/', '', $value);

        if (strlen($value) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $value);
        }

        if (strlen($value) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $value);
        }

        return $value;
    }

    public function cep(string $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        $value = preg_replace('/\D/', '', $value);

        if (strlen($value) === 8) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})/', '$1.$2-$3', $value);
        }

        return $value;
    }

    public function munIbge(string $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        $value = preg_replace('/\D/', '', $value);

        if (strlen($value) === 7) {
            return preg_replace('/(\d{2})(\d{5})/', '$1.$2', $value);
        }

        return $value;
    }

    public function date(string $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        try {
            $dt = new \DateTimeImmutable($value);
            return $dt->format('d/m/Y');
        } catch (\Exception) {
            return $value;
        }
    }

    public function dateTime(string $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        try {
            $dt = new \DateTimeImmutable($value);
            return $dt->format('d/m/Y H:i:s');
        } catch (\Exception) {
            return $value;
        }
    }

    public function currency(string|float $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }

    /**
     * Formata código de tributação nacional para o padrão XX.XX.XX
     */
    public function codTribNacional(string $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        $value = preg_replace('/\D/', '', $value);

        if (strlen($value) === 6) {
            return preg_replace('/(\d{2})(\d{2})(\d{2})/', '$1.$2.$3', $value);
        }

        return $value;
    }

    public function codigoNbs(string $value): string
    {
        if ($value === '' || $value === '-') {
            return '-';
        }

        $value = preg_replace('/\D/', '', $value);

        if (strlen($value) === 9) {
            return preg_replace('/(\d{1})(\d{4})(\d{2})(\d{2})/', '$1.$2.$3.$4', $value);
        }

        return $value;
    }

    public function limit(string $value, int $limit, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return mb_substr($value, 0, $limit - 3) . $end;
    }
}
