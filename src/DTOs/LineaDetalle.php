<?php

namespace Altcomcr\Client\DTOs;

use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

readonly class LineaDetalle
{
    public function __construct(
        public string                 $codigo,
        public BackedEnum|string      $unidad,
        public string                 $descripcion,
        public float                  $cantidad,
        public float                  $precio,
        public string                 $cabys,
        public BackedEnum|float|int   $impuesto = 13,
        public float                  $descuento = 0,
        public ?float                 $descuento_monto = null,
        public BackedEnum|string|null $desc_codigo = null,
        public BackedEnum|int|null    $tarifa = null,
        public ?string                $unidadcomercial = null,
        public BackedEnum|int|null    $transaccion = null,
        public ?string                $farma_reg = null,
        public ?string                $farma_cod = null,
        public ?string                $vin = null,
        public ?string                $partida = null,
        public ?Exoneracion           $exonerado = null,
        public ?float                 $importe = null,
    ) {
    }

    public function toArray(): array
    {
        $data = array_filter([
                                 'codigo'          => $this->codigo,
                                 'unidad'          => EnumHelper::value($this->unidad),
                                 'unidadcomercial' => $this->unidadcomercial,
                                 'descripcion'     => $this->descripcion,
                                 'cantidad'        => $this->cantidad,
                                 'precio'          => $this->precio,
                                 'descuento'       => $this->descuento,
                                 'descuento_monto' => $this->descuento_monto,
                                 'desc_codigo'     => EnumHelper::value($this->desc_codigo),
                                 'impuesto'        => EnumHelper::value($this->impuesto),
                                 'tarifa'          => EnumHelper::value($this->tarifa),
                                 'cabys'           => $this->cabys,
                                 'transaccion'     => EnumHelper::value($this->transaccion),
                                 'farma_reg'       => $this->farma_reg,
                                 'farma_cod'       => $this->farma_cod,
                                 'vin'             => $this->vin,
                                 'partida'         => $this->partida,
                                 'importe'         => $this->importe,
                             ], fn($value) => $value !== null);

        if ($this->exonerado) {
            $data['exonerado'] = $this->exonerado->toArray();
        }

        return $data;
    }
}
