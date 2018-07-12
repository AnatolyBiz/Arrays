<?php
/**
 * 
 */
namespace Arrays\D2\Tree\AdjacencyList\Output;

/**
 * .
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class Replacer extends \stdClass
{
    public $replacer;
    
    public function __construct(\Closure $replacer, array $data = [])
    {
        $this->replacer = $replacer->bindTo($this, __CLASS__);
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
