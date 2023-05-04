<?php

declare(strict_types=1);

namespace App\Router;

class HttpException extends \Exception
{
    private int $statusCode;

    public function __construct(int $statusCode, string $message = '', int $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}";
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
