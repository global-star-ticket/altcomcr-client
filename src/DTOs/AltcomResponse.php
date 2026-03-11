<?php

namespace Altcomcr\Client\DTOs;

readonly class AltcomResponse
{
    public function __construct(
        public bool   $success,
        public string $respuesta,
        public array  $data,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $error = $data['error'] ?? 0;

        return new self(success: $error == 0, respuesta: $data['respuesta'] ?? '', data: $data,);
    }

    public function getClave(): ?string
    {
        return $this->data['Clave'] ?? $this->data['clave'] ?? null;
    }

    public function getConsecutivo(): ?string
    {
        return $this->data['Consecutivo'] ?? $this->data['consecutivo'] ?? null;
    }

    public function getXml(): ?string
    {
        return $this->data['Xml'] ?? $this->data['xml'] ?? null;
    }

    public function getXmlDecoded(): ?string
    {
        $xml = $this->getXml();

        return $xml ? base64_decode($xml) : null;
    }

    public function getDoc(): ?int
    {
        $value = $this->data['Doc'] ?? $this->data['doc'] ?? null;

        return $value !== null ? (int) $value : null;
    }

    public function getTotal(): ?float
    {
        return isset($this->data['total']) ? (float) $this->data['total'] : null;
    }

    public function getImpuesto(): ?float
    {
        return isset($this->data['impuesto']) ? (float) $this->data['impuesto'] : null;
    }

    public function getFirmado(): ?string
    {
        return $this->data['Firmado'] ?? $this->data['firmado'] ?? null;
    }

    public function getMensajeHacienda(): ?string
    {
        return $this->data['mensaje'] ?? null;
    }

    public function getHaciendaDetalle(): ?string
    {
        return $this->data['HaciendaDetalle'] ?? null;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
