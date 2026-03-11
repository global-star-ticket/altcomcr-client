<?php

namespace Altcomcr\Client\Enums;

enum MedioPagoTipo: int implements \JsonSerializable
{
    use ResolvableEnum;

    case Efectivo      = 1;
    case Tarjeta       = 2;
    case Cheque        = 3;
    case Transferencia = 4;
    case Terceros      = 5;
    case SinpeMovil    = 6;
}
