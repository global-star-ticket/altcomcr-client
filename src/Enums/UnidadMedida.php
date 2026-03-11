<?php

namespace Altcomcr\Client\Enums;

/**
 * Unidades de medida para productos (Bienes).
 * Usar cuando el CABYS inicia con 0, 1, 2, 3 o 4.
 */
enum UnidadMedida: string implements \JsonSerializable
{
    use ResolvableEnum;

    case Metro         = 'm';
    case Kilogramo     = 'kg';
    case MetroCuadrado = 'm²';
    case Otros         = 'Otros';
    case Unidad        = 'Unid';
    case Litro         = 'L';
    case Mililitro     = 'mL';
    case Gramo         = 'G';
    case Pulgada       = 'In';
    case MetroCubico   = 'm³';
    case Galon         = 'Gal';
}
