<?php

namespace Altcomcr\Client\Exceptions;

use Exception;

class AltcomException extends Exception
{
    protected string $altcomResponse;

    public function __construct(string $message, string $altcomResponse = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->altcomResponse = $altcomResponse;
        parent::__construct($message, $code, $previous);
    }

    public function getAltcomResponse(): string
    {
        return $this->altcomResponse;
    }
}
