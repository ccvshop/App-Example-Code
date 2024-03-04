<?php

namespace AppConnector\Exceptions;

/**
 * Class InvalidJsonException
 *
 * @package AppConnector\Exceptions
 * @author  Adriaan Meijer
 * @date    2014-10-13
 * @version 1.0    - First draft
 */
class InvalidJsonException extends \Exception
{
    public function __construct()
    {
        $this->message = 'Data supplied is not conform the JSON standards.';
    }
}