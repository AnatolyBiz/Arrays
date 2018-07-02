<?php
/**
 * Arrays Option Class
 *
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\Option;

/**
 * The Option class contains method implementations for to be
 * used with Tree class.
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class Option implements IOption
{
    /**
     * A options.
     * 
     * @access private
     * @var int
     */
    private $options;
    
    
    
    public function __construct(int $options = 0)
    {
        $this->options = $options;
    }


    
    /**
     * Adds one or more a tree options.
     * 
     * @param int $option One or more a tree options.
     * @return void
     */
    public function set(int $option, bool $rewrite = false)
    {
        if ($rewrite) {
            $this->options = $option;
        } else {
            $this->options |= $option;
        }
    }
    
    /**
     * Returns given option value.
     * 
     * @param int $option
     * @return int
     */
    public function get(int $option) : bool
    {
        return $this->options & $option;
    }
    
    /**
     * Removes one option (sets to false).
     * 
     * @param int $option.
     * @return void
     */
    public function delete(int $option)
    {
        $option = ~$option;
        $this->options &= $option;
    }
    
    /**
     * Clears all options (sets all to false).
     * 
     * @return void
     */
    public function deleteAll()
    {
        return $this->options = 0;
    }
}
