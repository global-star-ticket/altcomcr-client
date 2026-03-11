<?php

namespace Altcomcr\Client\Enums;

enum TipoDescuento: string implements \JsonSerializable
{
    use ResolvableEnum;

    case Regalia      = '1';
    case Bonificacion = '3';
    case PorVolumen   = '4';
    case PorTemporada = '5';
    case Promocional  = '6';
    case Comercial    = '7';
    case Frecuencia   = '8';
}
