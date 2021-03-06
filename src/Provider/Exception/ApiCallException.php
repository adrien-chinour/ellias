<?php

namespace App\Provider\Exception;

class ApiCallException extends \Exception
{
    public function __construct($provider, $method, $message = '', $code = 0, \Throwable $previous = null)
    {
        $message = "Call API from method '{$method}' on provider '{$provider}' has failed. $message";
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }
}
