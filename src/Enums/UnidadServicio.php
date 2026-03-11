<?php

namespace Altcomcr\Client\Enums;

/**
 * Unidades de medida para servicios.
 * Usar cuando el CABYS inicia con 5, 6, 7, 8 o 9.
 */
enum UnidadServicio: string implements \JsonSerializable
{
    use ResolvableEnum;

    case ServiciosProfesionales = 'Sp';
    case ServiciosPersonales    = 'Spe';
    case ServiciosTecnicos      = 'St';
    case Otros                  = 'Os';
    case Hora                   = 'h';
    case Dia                    = 'D';
    case AlquilerHabitacion     = 'Al';
    case AlquilerComercial      = 'Alc';
    case Comisiones             = 'Cm';
    case Intereses              = 'I';
}
