<?php

namespace Altcomcr\Client\Enums;

use BackedEnum;

class EnumHelper
{
    /**
     * Extrae el valor primitivo de un enum o devuelve el valor tal cual.
     *
     * Esto permite que métodos acepten tanto enums como valores crudos:
     *   EnumHelper::value(Moneda::Colones) -> 1
     *   EnumHelper::value(1)               -> 1
     *   EnumHelper::value('Sp')            -> 'Sp'
     */
    public static function value(BackedEnum|string|int|float|null $input): string|int|float|null
    {
        if ($input instanceof BackedEnum) {
            return $input->value;
        }

        return $input;
    }
}
