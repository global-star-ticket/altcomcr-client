<?php

namespace Altcomcr\Client\Enums;

enum Destino: int implements \JsonSerializable
{
    use ResolvableEnum;

    case Tiquete     = 0;
    case Factura     = 1;
    case Exportacion = 2;
}
