<?php

namespace Altcomcr\Client\Enums;

enum TipoNota: string implements \JsonSerializable
{
    use ResolvableEnum;

    case Credito = 'credito';
    case Debito  = 'debito';
}
