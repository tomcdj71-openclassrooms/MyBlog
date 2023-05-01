<?php

declare(strict_types=1);

namespace App\Router;

/**
 * Class RouterException.
 */
class RouterException extends \Exception
{
    public function __construct($code = 404, \Exception $previous = null)
    {
        $message = 'Exception code = '.$code.', previous = '.$previous;
        parent::__construct($message, $code, $previous);
        error_log($message);
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
