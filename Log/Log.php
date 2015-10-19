<?
	namespace AppConnector\Log;

	/**
	 * Class Log
	 *
	 * @package AppConnector\Log
	 * @author  Adriaan Meijer
	 * @date    2014-11-18
	 * @version 1.0    - First Draft
	 */
	class Log {
		static private $rLogFile;

		/**
		 * Write a log entry into a log file
		 *
		 * @static
		 *
		 * @param string $sFunction = Name of the file or function currently in
		 * @param string $sType     = Type of action or log
		 * @param string $sValue    = Value associated with this action
		 */
		static public function Write($sFunction = '', $sType = '', $sValue = '') {

			$sLog = date('Y-m-d H:i:s') . ' '; #timestamp
			$sLog .= '[' . str_pad($sFunction, 34, ' ', STR_PAD_LEFT) . '] - '; #File/Function Name
			$sLog .= str_pad(strtoupper($sType), 15, ' ', STR_PAD_LEFT) . ' - '; #Type of log
			$sLog .= '[' . $sValue . ']'; #Value of the action
			$sLog .= "\r\n"; #EOL
			fwrite(static::OpenFile(), $sLog);

		}


		static public function WriteStartCall(){
			static::Write('START CALL');
		}
		static public function WriteEndCall(){
			static::Write('END CALL');
			fwrite(static::OpenFile(), "\r\n");
		}

		/**
		 * Function to open or create a logfile.
		 *
		 * @static
		 * @return mixed
		 */
		static private function OpenFile() {
			if(!is_resource(static::$rLogFile)) {
				static::$rLogFile = @fopen('./Log/' . date('Y-m-d') . '_AppConnector.log', 'a');
			}
			return static::$rLogFile;
		}
	}