<?php

namespace Altcomcr\Client\Enums;

enum InstitucionExoneracion: string implements \JsonSerializable
{
    use ResolvableEnum;

    case MinisterioHacienda             = '01';
    case MinisterioRelacionesExteriores = '02';
    case MinisterioAgricultura          = '03';
    case MinisterioEconomia             = '04';
    case CruzRoja                       = '05';
    case Bomberos                       = '06';
    case ObrasEspirituSanto             = '07';
    case Fecrunapa                      = '08';
    case EARTH                          = '09';
    case INCAE                          = '10';
    case JuntaProteccionSocial          = '11';
    case ARESEP                         = '12';
}
