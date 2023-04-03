<?php

declare(strict_types=1);

namespace App\Router;

/**
 * Class RouterException.
 */
class RouterException extends \Exception
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    // Custom string representation of object
    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}";
    }

    public function getException()
    {
        return $this->message;
    }
}
