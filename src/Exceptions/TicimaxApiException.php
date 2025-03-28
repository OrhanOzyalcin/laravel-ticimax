<?php

namespace LaravelTicimax\Ticimax\Exceptions;

use Exception;

class TicimaxApiException extends Exception
{
    protected array $details;

    public function __construct(
        string $message = "", 
        int $code = 0, 
        array $details = [], 
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}