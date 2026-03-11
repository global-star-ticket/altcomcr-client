<?php

namespace Altcomcr\Client\Enums;

enum TipoPago: int implements \JsonSerializable
{
    use ResolvableEnum;

    case Contado       = 1;
    case Credito       = 2;
    case Consignacion  = 3;
    case Apartado      = 4;
    case CreditoEstado = 8;
    case Credito90Dias = 10;
    case NoNacional    = 12;
}
