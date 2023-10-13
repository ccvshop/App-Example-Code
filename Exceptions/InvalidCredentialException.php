<?php

namespace AppConnector\Exceptions;

/**
 * Class InvalidCredentialException
 *
 * @package AppConnector\Exceptions
 * @author  Adriaan Meijer
 * @date    2014-10-13
 * @version 1.0    - First draft
 */
class InvalidCredentialException extends \Exception
{
    public function __construct()
    {
        $this->message = 'No credentials found based on public key.';
    }
}