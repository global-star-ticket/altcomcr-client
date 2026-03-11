<?php

namespace Altcomcr\Client\DTOs;

readonly class OtroTexto
{
    public function __construct(
        public string  $valor,
        public string  $contenido,
        public ?string $atributo = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
                                'atributo'  => $this->atributo,
                                'valor'     => $this->valor,
                                'contenido' => $this->contenido,
                            ], fn($value) => $value !== null);
    }
}
