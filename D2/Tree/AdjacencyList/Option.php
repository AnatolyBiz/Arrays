<?php
/**
 * Arrays Option Class
 *
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\D2\Tree\AdjacencyList;

use Arrays\Option\Option as _Option;
use Arrays\D2\Tree\View\View;
use Arrays\Numbering\Numbering;

/**
 * The Option class contains constants and another objects for Tree class.
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class Option extends _Option
{
    use Num;
    /**
     * A lot of constans, which is used to set a tree's options.
     */
    const COUNT_DESCENDANTS = 1;    // 0000 0000 0000 0001
    const NUMBERING = 2;            // 0000 0001 0000 0010
    const DEBUG_MODE = 8;           // 0001 0000 0000 1000
    
    public $view;
    public $numbering;
    
    public function __construct(int $options, $src_index, $src_parent)
    {
        // Call parent method
        parent::__construct($options);
        
        // Init view
        $this->view = new View($src_index, $src_parent);
        
        // Init numbering, will be loaded only default numerator 'Decimal'
        $this->numbering = new Numbering();
    }
}
