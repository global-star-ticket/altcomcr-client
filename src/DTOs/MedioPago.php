<?php

namespace Altcomcr\Client\DTOs;

use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

readonly class MedioPago
{
    public function __construct(
        public BackedEnum|int $tipomp,
        public ?float         $montomp = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
                                'tipomp'  => (string) EnumHelper::value($this->tipomp),
                                'montomp' => $this->montomp !== null ? (string) $this->montomp : null,
                            ], fn($value) => $value !== null);
    }
}
