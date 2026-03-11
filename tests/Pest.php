<?php

use Altcomcr\Client\Altcom;
use Altcomcr\Client\Exceptions\AltcomApiException;
use Altcomcr\Client\Exceptions\AltcomConnectionException;

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
*/

class SharedState
{
    public static string  $claveFacturaContado     = '';

    public static string  $documentoFacturaContado = '';

    public static string  $claveFacturaCredito     = '';

    public static string  $documentoFacturaCredito = '';

    public static string  $claveTiquete            = '';

    public static ?Altcom $altcom                  = null;

    public static bool    $credentialsValidated    = false;

    public static ?string $credentialsError        = null;

    public static bool    $credentialsPrinted      = false;
}

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function env(string $key, string $default = ''): string
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

function loadEnv(): void
{
    $envFile = __DIR__.'/../.env';

    if (! file_exists($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        if (! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        if (! array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

function createAltcom(): Altcom
{
    loadEnv();

    return new Altcom(usuario: env('ALTCOM_TEST_USUARIO'), clave: env('ALTCOM_TEST_CLAVE'), cedula: env('ALTCOM_TEST_CEDULA'), sucursal: env('ALTCOM_TEST_SUCURSAL', '001'), terminal: env('ALTCOM_TEST_TERMINAL', '00001'), actividad: env('ALTCOM_TEST_ACTIVIDAD'), sandbox: true,);
}

function altcom(): Altcom
{
    if (SharedState::$altcom === null) {
        SharedState::$altcom = createAltcom();
    }

    return SharedState::$altcom;
}

/**
 * Valida credenciales contra el sandbox una sola vez por ejecución.
 * Almacena el resultado en SharedState para evitar llamadas repetidas.
 * Debe llamarse desde beforeAll() — no tiene acceso a test().
 */
function validateCredentials(): void
{
    loadEnv();

    $usuario   = env('ALTCOM_TEST_USUARIO');
    $clave     = env('ALTCOM_TEST_CLAVE');
    $cedula    = env('ALTCOM_TEST_CEDULA');
    $sucursal  = env('ALTCOM_TEST_SUCURSAL', '001');
    $terminal  = env('ALTCOM_TEST_TERMINAL', '00001');
    $actividad = env('ALTCOM_TEST_ACTIVIDAD');

    if (empty($usuario) || empty($clave)) {
        SharedState::$credentialsValidated = true;
        SharedState::$credentialsError     = 'Credenciales de sandbox no configuradas en .env';

        return;
    }

    if (SharedState::$credentialsValidated) {
        return;
    }

    SharedState::$credentialsValidated = true;

    try {
        $response = altcom()
            ->consultas()
            ->saldo();

        if (! $response->success) {
            SharedState::$credentialsError = "Credenciales de sandbox inválidas: {$response->respuesta}";
        }
    } catch (AltcomApiException $e) {
        SharedState::$credentialsError = "Credenciales de sandbox inválidas: {$e->getMessage()}";
    } catch (AltcomConnectionException $e) {
        // Error de red/timeout — no cachear, podría ser temporal
        SharedState::$credentialsValidated = false;

        throw $e;
    }
}

/**
 * Skipea el test actual si las credenciales son inválidas.
 * Debe llamarse desde beforeEach() donde test() sí está disponible.
 */
function skipIfInvalidCredentials(): void
{
    if (! SharedState::$credentialsPrinted) {
        SharedState::$credentialsPrinted = true;

        loadEnv();

        $usuario   = env('ALTCOM_TEST_USUARIO');
        $clave     = env('ALTCOM_TEST_CLAVE');
        $cedula    = env('ALTCOM_TEST_CEDULA');
        $sucursal  = env('ALTCOM_TEST_SUCURSAL', '001');
        $terminal  = env('ALTCOM_TEST_TERMINAL', '00001');
        $actividad = env('ALTCOM_TEST_ACTIVIDAD');
        $claveMask = str_repeat('*', max(0, strlen($clave) - 3)).substr($clave, -3);

        echo PHP_EOL;
        echo "  Credenciales de integración:".PHP_EOL;
        echo "    Usuario:   {$usuario}".PHP_EOL;
        echo "    Clave:     {$claveMask}".PHP_EOL;
        echo "    Cédula:    {$cedula}".PHP_EOL;
        echo "    Sucursal:  {$sucursal}".PHP_EOL;
        echo "    Terminal:  {$terminal}".PHP_EOL;
        echo "    Actividad: {$actividad}".PHP_EOL;
        echo "    Entorno:   sandbox".PHP_EOL;
        echo PHP_EOL;
    }

    if (SharedState::$credentialsError !== null) {
        test()->markTestSkipped(SharedState::$credentialsError);
    }
}

function uniqueDocumento(string $prefix = 'TEST'): string
{
    return $prefix.'-'.time().'-'.mt_rand(1000, 9999);
}
