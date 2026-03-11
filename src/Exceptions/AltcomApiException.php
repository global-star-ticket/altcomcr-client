<?php

namespace Altcomcr\Client\Exceptions;

class AltcomApiException extends AltcomException
{
    public static function fromResponse(array $response): self
    {
        $message = $response['respuesta'] ?? 'Error desconocido de la API Altcom';

        return new self($message, json_encode($response), 400);
    }
}
