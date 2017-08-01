<?

namespace AppConnector\Exceptions;

/**
 * Class InvalidApiResponse
 *
 * @package AppConnector\Exceptions
 * @author  Adriaan Meijer
 * @date    2014-11-18
 * @version 1.0    - First draft
 */
class InvalidApiResponse extends \Exception
{
    public function __construct($sMessage = '')
    {
        $this->message = 'API returned an unexpected result. ' . $sMessage;
    }
}