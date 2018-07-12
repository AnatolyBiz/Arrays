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
use Arrays\Numbering\Numbering;
use Arrays\D2\Tree\View\View;
use Arrays\D2\Tree\AdjacencyList\Output\Output;

/**
 * The Option class contains constants and another objects for Tree class.
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class Option extends _Option
{
    /**
     * A lot of constans, which is used to set a tree's options.
     */
    const COUNT_DESCENDANTS = 1;    // 0000 0000 0000 0001
    const NUMBERING = 2;            // 0000 0000 0000 0010
    const DEBUG_MODE = 8;           // 0000 0000 0000 1000
    
    
    private $tree;
    

    
    public function __construct(Tree $tree, int $options)
    {
        // Call parent method
        parent::__construct($options);
        
        // tree
        $this->tree = $tree;
    }
    
    public function __get($name)
    {
        switch ($name) {
            case 'numbering':
                // Init numbering, will be loaded only default numerator 'Decimal'
                $nb = new Numbering();
                $nb->setExceptionClass($this->tree->getExceptionClass());
                $this->numbering = $nb;
                return $this->numbering;
            case 'view':
                // Init view
                $this->view = new View($this->tree);
                return $this->view;
            case 'output':
                // Init output
                $this->output = new Output($this->tree, $this->view);
                return $this->output;
        }        
    }
}
