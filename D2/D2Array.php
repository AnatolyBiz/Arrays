<?php
/**
 * Arrays D2Array Class
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\D2;


/**
 * Implements helpful methods for work with D2 arrays (D2 means two-dimensional).
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class D2Array
{
    /**
     * Verifies if a column exists in a D2 array. For search uses only a first row.
     * 
     * @param array             $array      A source array.
     * @param string|mixed      $key        A key to search for.
     * @return                  bool        True if a column/key exists in an array.
     */
    public static function arrayKeyExists(&$array, $key)
    {
        foreach ($array as &$first_row) {
            return array_key_exists($key, $first_row); // searches $key in first $row-array of source $array    
        }
    }
    
    /**
     * Verifies if a column set exists in a D2 array. For search uses only a first row.
     * 
     * @param array             $array      A source array.
     * @param array             $keys       A key set to search for.
     * @return                  bool        True if all column/key set exists in an array.
     */
    public static function arrayKeysExist(&$array, array $keys)
    {
        foreach ($array as &$first_row) {
            $res = 1;
            foreach ($keys as &$key) {
                $res &= array_key_exists($key, $first_row); // searches $key in first $row-array of source $array
                if (!$res) {
                    break;
                }
            }
            return $res;
        }
    }
    
    /**
     * Returns a new array which has index values equal to passed column values. 
     * Sometimes this method is extremely helpful to reduce script execution time.
     * 
     * @param array $source A 2D array which is base for creating new array with new index values.
     * @param int|mixed $index A column name of a source array, witch contains values for index of new creationg array.
     * @return array An new indexed array.
     */
    public static function newIndex(array &$source, $src_index, array &$result) : void
    {
        // verify: is presents 'index' column in source array?
        if (! self::arrayKeyExists($source, $src_index)) {
            throw new \LogicException('The source array has no index column \'' . $src_index . '\'.');
        }
        
        // create new array
        foreach ($source as &$src_row) {
            $result[$src_row[$src_index]] = &$src_row;
        }
    }
}
