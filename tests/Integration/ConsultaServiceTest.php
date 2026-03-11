<?php

namespace Altcomcr\Client\Tests\Integration;

use Altcomcr\Client\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class ConsultaServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        static::requiresCredentials();
    }

    public function test_consultar_saldo(): void
    {
        $response = static::altcom()
            ->consultas()
            ->saldo();

        $this->assertTrue($response->success);
        $this->assertSame('Ok', $response->respuesta);
        $this->assertArrayHasKey('vence', $response->data);
        $this->assertArrayHasKey('dias', $response->data);
    }

    public function test_consultar_llave(): void
    {
        $response = static::altcom()
            ->consultas()
            ->llave();

        $this->assertTrue($response->success);
        $this->assertSame('Ok', $response->respuesta);
        $this->assertArrayHasKey('vence', $response->data);
        $this->assertArrayHasKey('actividades', $response->data);
        $this->assertIsArray($response->data['actividades']);
    }
}
