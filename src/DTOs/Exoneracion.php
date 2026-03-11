<?php

namespace Altcomcr\Client\DTOs;

use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

readonly class Exoneracion
{
    public function __construct(
        public BackedEnum|string $tipodocumento,
        public string            $numerodocumento,
        public BackedEnum|string $nombreinstitucion,
        public string            $fechaemision,
        public ?string           $articulo = null,
        public ?string           $inciso = null,
        public ?int              $porcentajecompra = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
                                'tipodocumento'     => EnumHelper::value($this->tipodocumento),
                                'numerodocumento'   => $this->numerodocumento,
                                'articulo'          => $this->articulo,
                                'inciso'            => $this->inciso,
                                'nombreinstitucion' => EnumHelper::value($this->nombreinstitucion),
                                'fechaemision'      => $this->fechaemision,
                                'porcentajecompra'  => $this->porcentajecompra,
                            ], fn($value) => $value !== null);
    }
}
