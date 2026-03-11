<?php

namespace Altcomcr\Client;

class AltcomFactory
{
    public function __construct(
        protected bool $sandbox = true,
        protected int  $timeout = 30,
        protected int  $retries = 3,
        protected int  $retryDelay = 100,
    ) {
    }

    /**
     * Crea una instancia de Altcom con las credenciales proporcionadas,
     * usando la configuración de infraestructura precargada.
     */
    public function make(
        string $usuario,
        string $clave,
        string $cedula,
        string $sucursal = '001',
        string $terminal = '00001',
        string $actividad = '',
    ): Altcom {
        return new Altcom(usuario: $usuario, clave: $clave, cedula: $cedula, sucursal: $sucursal, terminal: $terminal, actividad: $actividad, sandbox: $this->sandbox, timeout: $this->timeout, retries: $this->retries, retryDelay: $this->retryDelay);
    }
}
