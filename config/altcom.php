<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ambiente (sandbox o producción)
    |--------------------------------------------------------------------------
    |
    | En modo sandbox se usa https://sandbox.altcomcr.net/api
    | En producción se usa https://www.altcomcr.net/f/api
    |
    */

    'sandbox' => env('ALTCOM_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | URLs de la API
    |--------------------------------------------------------------------------
    */

    'urls' => [
        'production' => 'https://www.altcomcr.net/f/api',
        'sandbox'    => 'https://sandbox.altcomcr.net/api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout de las peticiones HTTP (segundos)
    |--------------------------------------------------------------------------
    */

    'timeout' => env('ALTCOM_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Reintentos en caso de fallo de conexión
    |--------------------------------------------------------------------------
    */

    'retries'     => env('ALTCOM_RETRIES', 3),
    'retry_delay' => env('ALTCOM_RETRY_DELAY', 100),

];
