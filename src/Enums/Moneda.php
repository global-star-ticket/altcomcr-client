<?php

namespace Altcomcr\Client\Enums;

enum Moneda: int implements \JsonSerializable
{
    use ResolvableEnum {
        resolve as protected baseResolve;
    }

    case Colones = 1;
    case Dolares = 2;
    case Euros   = 3;

    private const ISO_MAP = [
        'CRC' => self::Colones,
        'USD' => self::Dolares,
        'EUR' => self::Euros,
    ];

    /**
     * Resuelve moneda desde enum, valor numérico o código ISO.
     *
     *   Moneda::resolve(Moneda::Colones) -> 1
     *   Moneda::resolve('CRC')           -> 1
     *   Moneda::resolve('crc')           -> 1
     *   Moneda::resolve(1)               -> 1
     */
    public static function resolve(self|\BackedEnum|string|int|float $input): string|int|float
    {
        if (is_string($input) && isset(self::ISO_MAP[strtoupper($input)])) {
            return self::ISO_MAP[strtoupper($input)]->value;
        }

        return self::baseResolve($input);
    }

    /**
     * Obtiene la moneda a partir de su código ISO 4217 (CRC, USD, EUR).
     */
    public static function fromIso(string $iso): self
    {
        $iso = strtoupper(trim($iso));

        return self::ISO_MAP[$iso] ?? throw new \ValueError("Código ISO de moneda no soportado: {$iso}");
    }

    /**
     * Código ISO 4217 de la moneda.
     */
    public function iso(): string
    {
        return match ($this) {
            self::Colones => 'CRC',
            self::Dolares => 'USD',
            self::Euros   => 'EUR',
        };
    }
}
