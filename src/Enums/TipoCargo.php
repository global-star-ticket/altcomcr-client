<?php

namespace Altcomcr\Client\Enums;

enum TipoCargo: string implements \JsonSerializable
{
    use ResolvableEnum;

    case ContribucionParafiscal = '01';
    case TimbreCruzRoja         = '02';
    case TimbreBomberos         = '03';
    case CobroTercero           = '04';
    case CostosExportacion      = '05';
    case ServicioSaloneros      = '06';
    case TimbreColegioProf      = '07';
    case Depositos              = '08';
    case Multas                 = '09';
    case Intereses              = '10';
    case Otros                  = '99';
}
