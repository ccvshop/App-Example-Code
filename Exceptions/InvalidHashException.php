<?

namespace AppConnector\Exceptions;

/**
 * Class InvalidHashException
 *
 * @package AppConnector\Exceptions
 * @author  Adriaan Meijer
 * @date    2014-10-13
 * @version 1.0    - First draft
 */
class InvalidHashException extends \Exception
{
    public function __construct()
    {
        $this->message = 'Calculated hash does not match the hash included in the headers.';
    }
}