<?php

namespace Altcomcr\Client\DTOs;

use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

readonly class CargoExtra
{
    public function __construct(
        public BackedEnum|string      $tipocargo,
        public string                 $detalle,
        public float                  $monto,
        public BackedEnum|string|null $tipoid = null,
        public ?string                $identificacion = null,
        public ?string                $nombre = null,
        public ?string                $documento = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
                                'tipocargo'      => EnumHelper::value($this->tipocargo),
                                'detalle'        => $this->detalle,
                                'monto'          => $this->monto,
                                'tipoid'         => EnumHelper::value($this->tipoid),
                                'identificación' => $this->identificacion,
                                'nombre'         => $this->nombre,
                                'documento'      => $this->documento,
                            ], fn($value) => $value !== null);
    }
}
