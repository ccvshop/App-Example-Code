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
abstract class Data_Core
{
    const DATA_FILE = null;

    /**
     * Opens the data file to read data
     *
     * @static
     * @return resource
     */
    protected static function openFileToWrite()
    {
        $rHandle = @fopen(static::getDataFile(), 'a');
        if ($rHandle === false) {
            $rHandle = @fopen(static::getDataFile(), 'x+');
        }
        return $rHandle;
    }

    /**
     * Returns the last used ID in the data file.
     *
     * @static
     * @return resource
     */
    protected static function openFileToRead()
    {
        $rHandle = @fopen(static::getDataFile(), 'r');
        if ($rHandle === false) {
            $rHandle = @fopen(static::getDataFile(), 'x+');
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
    private static function getDataFile()
    {
        if (is_null(static::DATA_FILE)) {
            throw new \Exception('Data class needs constant DataFile');
        }
        return $_SERVER['DOCUMENT_ROOT'] . '/' . static::DATA_FILE;
    }
}