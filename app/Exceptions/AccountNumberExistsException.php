<?php

namespace App\Exceptions;

use Exception;

class AccountNumberExistsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Account number has already been generated');
    }
}
