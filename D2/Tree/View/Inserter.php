<?php
namespace Arrays\D2\Tree\View;

/**
 * Used for an adding a node set to the current node while the tree outputting.
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class OutputInserter extends \stdClass
{
    public $inserter;
    
    public function __construct(\Closure $inserter, array $data = [])
    {
        
    }
}
