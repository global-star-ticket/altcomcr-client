<?php

namespace Altcomcr\Client\Tests;

use Altcomcr\Client\Altcom;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static ?Altcom $altcom = null;

    protected static function env(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    protected static function loadEnv(): void
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

    protected static function createAltcom(): Altcom
    {
        static::loadEnv();

        return new Altcom(usuario: static::env('ALTCOM_TEST_USUARIO'), clave: static::env('ALTCOM_TEST_CLAVE'), cedula: static::env('ALTCOM_TEST_CEDULA'), sucursal: static::env('ALTCOM_TEST_SUCURSAL', '001'), terminal: static::env('ALTCOM_TEST_TERMINAL', '00001'), actividad: static::env('ALTCOM_TEST_ACTIVIDAD'), sandbox: true,);
    }

    protected static function altcom(): Altcom
    {
        if (static::$altcom === null) {
            static::$altcom = static::createAltcom();
        }

        return static::$altcom;
    }

    protected static function requiresCredentials(): void
    {
        static::loadEnv();

        if (empty(static::env('ALTCOM_TEST_USUARIO')) || empty(static::env('ALTCOM_TEST_CLAVE'))) {
            static::markTestSkipped('Credenciales de sandbox no configuradas en .env');
        }
    }

    protected static function uniqueDocumento(string $prefix = 'TEST'): string
    {
        return $prefix.'-'.time().'-'.mt_rand(1000, 9999);
    }
}
