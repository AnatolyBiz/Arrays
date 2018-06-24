<?php
/**
 * Arrays IOption Interface
 *
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\Option;

/**
 * The IOption interface contains some constants and method definitions for to be
 * used with Tree class.
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
interface IOption
{
    
    
    /**
     * 
     * @param int $option Value of option, it's value of one of this interface constants.
     * @param bool $rewrite If true, then all another options will be set to false. In other words, options variable will contain value of only this setting option.
     */
    public function set(int $option, bool $rewrite = false);
    
    /**
     * 
     */
    public function get(int $option) : bool;
    
    /**
     * 
     */
    public function delete(int $option);
    
    /**
     * 
     */
    public function deleteAll();
    
}
