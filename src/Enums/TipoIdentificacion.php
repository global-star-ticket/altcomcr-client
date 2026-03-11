<?php

namespace Altcomcr\Client\Enums;

enum TipoIdentificacion: string implements \JsonSerializable
{
    use ResolvableEnum;

    case Fisico     = '01';
    case Juridico   = '02';
    case Dimex      = '03';
    case Nite       = '04';
    case Extranjero = '09';
}
