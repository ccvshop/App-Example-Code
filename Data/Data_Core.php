<?
	namespace AppConnector\Data;
	/**
	 * Class Data_Core
	 * Contains basic functions for the data classes.
	 *
	 * @package AppConnector\Data
	 * @author  Adriaan Meijer
	 * @date    2014-10-13
	 * @version 1.0    - First draft
	 */
	abstract class Data_Core {
		const DataFile = null;

		/**
		 * Opens the data file to read data
		 *
		 * @static
		 * @return resource
		 */
		static protected function OpenFileToWrite() {
			$rHandle = @fopen(static::GetDataFile(), 'a');
			if($rHandle === false) {
				$rHandle = @fopen(static::GetDataFile(), 'x+');
			}
			return $rHandle;
		}

		/**
		 * Returns the last used ID in the data file.
		 *
		 * @static
		 * @return resource
		 */
		static protected function OpenFileToRead() {
			$rHandle = @fopen(static::GetDataFile(), 'r');
			if($rHandle === false) {
				$rHandle = @fopen(static::GetDataFile(), 'x+');
			}
			return $rHandle;
		}

		/**
		 * Returns data file
		 *
		 * @static
		 * @return null
		 * @throws \Exception
		 */
		static private function GetDataFile() {
			if(is_null(static::DataFile)) {
				throw new \Exception('Data class needs constant DataFile');
			}
			return static::DataFile;
		}
	}