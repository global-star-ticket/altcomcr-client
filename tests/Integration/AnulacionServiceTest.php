<?php

namespace Altcomcr\Client\Tests\Integration;

use Altcomcr\Client\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class AnulacionServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        static::requiresCredentials();
    }

    public function test_anular_tiquete(): void
    {
        $clave = FacturaServiceTest::$claveTiquete;

        if (empty($clave)) {
            $this->markTestSkipped('No hay tiquete emitido previamente.');
        }

        $response = static::altcom()
            ->anulacion()
            ->anular($clave);

        $this->assertTrue($response->success);
        $this->assertSame('Aplicado', $response->respuesta);
        $this->assertNotEmpty($response->getClave());
    }
}
