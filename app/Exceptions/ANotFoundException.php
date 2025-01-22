<?php

namespace App\Exceptions;

use Exception;

class ANotFoundException extends Exception
{
    public function __construct(string $message = "Not Found")
    {
        parent::__construct($message, 404);
    }
}
