<?php

namespace Altcomcr\Client\Tests\Integration;

use Altcomcr\Client\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class ConsultaHaciendaServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        static::requiresCredentials();
    }

    public function test_consultar_hacienda(): void
    {
        $clave = FacturaServiceTest::$claveFacturaContado;

        if (empty($clave)) {
            $this->markTestSkipped('No hay factura emitida previamente.');
        }

        // Esperar un momento para que Hacienda procese
        sleep(5);

        $response = static::altcom()
            ->consultas()
            ->consultarHacienda($clave);

        $this->assertTrue($response->success);
        $this->assertSame('Ok', $response->respuesta);
        $this->assertNotEmpty($response->getClave());
        $this->assertContains($response->getMensajeHacienda(), ['Aceptado', 'Rechazado']);
    }

    public function test_consulta_interna(): void
    {
        $documento = FacturaServiceTest::$documentoFacturaContado;

        if (empty($documento)) {
            $this->markTestSkipped('No hay documento de factura previamente emitido.');
        }

        $response = static::altcom()
            ->consultas()
            ->consultarInterno(documento: $documento);

        $this->assertTrue($response->success);
        $this->assertNotEmpty($response->getClave());
        $this->assertNotNull($response->getXml());
    }
}
