<?
	namespace AppConnector\Exceptions;

	/**
	 * Class InvalidMethod
	 *
	 * @package AppConnector\Exceptions
	 * @author  Adriaan Meijer
	 * @date    2014-10-13
	 * @version 1.0    - First draft
	 */
	class InvalidMethod extends \Exception {
		public function __construct() {
			$this->message = 'Data supplied is not conform the JSON standards.';
		}
	}