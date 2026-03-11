<?php

namespace Altcomcr\Client\Enums;

enum IvaCondicion: string implements \JsonSerializable
{
    use ResolvableEnum;

    case GeneraCreditoIva = '01';
    case CreditoParcial   = '02';
    case BienesCapital    = '03';
    case GastoCorriente   = '04';
    case Proporcionalidad = '05';
}
