<?php

namespace Altcomcr\Client\Enums;

enum TipoDocumentoExoneracion: string implements \JsonSerializable
{
    use ResolvableEnum;

    case ComprasAutorizadas        = '01';
    case VentasDiplomaticos        = '02';
    case AutorizadoPorLey          = '03';
    case ExencionesDGH             = '04';
    case TransitorioV              = '05';
    case ServiciosTuristicos       = '06';
    case TransitorioXVII           = '07';
    case ZonaFranca                = '08';
    case ServiciosExportadores     = '09';
    case Municipalidades           = '10';
    case AutorizacionImpuestoLocal = '11';
}
