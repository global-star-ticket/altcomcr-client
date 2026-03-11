<?php

namespace Altcomcr\Client\Enums;

use BackedEnum;

/**
 * Trait para enums que permite resolución transparente de valores.
 *
 * Uso en servicios/DTOs:
 *   EnumHelper::value($input) -> devuelve el valor primitivo (string|int|float)
 *
 * Uso en enums:
 *   TipoPago::resolve('contado') -> 1
 *   TipoPago::resolve(TipoPago::Contado) -> 1
 *   TipoPago::resolve(1) -> 1
 */
trait ResolvableEnum
{
    /**
     * Resuelve cualquier input al valor primitivo del enum.
     *
     * Acepta:
     *   - Instancia del enum -> retorna ->value
     *   - Valor primitivo válido del enum -> retorna tal cual
     *   - Nombre del case (case-insensitive) -> retorna ->value
     *   - Cualquier otro valor -> retorna tal cual (para no romper compatibilidad)
     */
    public static function resolve(self|BackedEnum|string|int|float $input): string|int|float
    {
        if ($input instanceof self) {
            return $input->value;
        }

        if ($input instanceof BackedEnum) {
            return $input->value;
        }

        // Intentar por valor directo (solo si el tipo coincide con el backing type)
        $backingType = (new \ReflectionEnum(self::class))->getBackingType();
        $compatible  = ($backingType?->getName() === 'int' && is_int($input))
            || ($backingType?->getName() === 'string' && is_string($input));

        if ($compatible) {
            $found = self::tryFrom($input);
            if ($found !== null) {
                return $found->value;
            }
        }

        // Intentar por nombre del case (case-insensitive)
        foreach (self::cases() as $case) {
            if (strcasecmp($case->name, (string) $input) === 0) {
                return $case->value;
            }
        }

        return $input;
    }

    public function jsonSerialize(): string|int
    {
        return $this->value;
    }
}
