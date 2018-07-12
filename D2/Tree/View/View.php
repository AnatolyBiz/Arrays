<?php
namespace Arrays\D2\Tree\View;

use Arrays\D2\Tree\AdjacencyList\Tree;

/**
 * Used for .
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class View
{
    /**
     * Contains default view settings. Is initialize with the construct method.
     * 
     * @access private
     * @var array
     */
    private $view;
        
    /**
     * Contains pattern part to replace column expression with computed value.
     * Initializes in __constructor method with add/getReplacingPattern methods.
     * This value contains a raw pattern from tree.view.config.php, it will be
     * changed by getReplacingPattern and written to $replacing_pattern property.
     * 
     * @access private
     * @var string
     */
//    private $raw_replacing_pattern;
//    
//    /**
//     * Contains pattern to replace column expression with computed value.
//     * Initializes in apply_custom_view method with getReplacingPattern method.
//     * 
//     * @access private
//     * @var string
//     */
//    private $replacing_pattern;
//    
//    /**
//     * Method name for handle node template.
//     * Possible values is: 'common', 'replacer', 'replacerInserter'.
//     */
//    private $output_handler = 'Column';
//
//    /**
//     * An array of objects to computing of expressions in elements ('block',
//     * 'item', 'content').
//     * 
//     * Example:
//     * '<ul data-level="{{level}}">{{}}</ul>',          'block' element, an expression is '{{level}}', while an output, it will be replace to a level value
//     * '<li data-children="{{children}}">{{}}</li>',    'item' element, an expression is '{{children}}', while an output, it will be replace to a children value
//     * '<a class="{{%active%}}" href="{{link}}" data-id="{{id}}">{{%title%}}</a>',      'content' element, expressions is '{{%active%}}', '{{link}}', '{{id}}', '{{%title%}}'
//     * 
//     * An every object has a callback. Every callback will call, when a node will output. A callback receives 2 
//     * parameters: #1. $node_arr, a current row, #2. $result, to write the result of the computing.
//     * 
//     * @access private
//     * @var array
//     */
//    private $replacer = [];
//    
//    /**
//     * An array of objects to inserting of node/nodes while an output.
//     * 
//     * An every object has a callback. Every callback will call, when a node will output. A callback receives 2 
//     * parameters: #1. $node_arr, a current row, #2. $result, to write the result of the computing.
//     * 
//     * @access private
//     * @var array
//     */
//    private $inserter = [];
    
    /**
     * An exception class name, which is used while error arise.
     * 
     * @access private
     * @var string
     */
    private $exceptionClass;
    
    /**
     * An exception class name, which is used while error arise.
     * 
     * @access private
     * @var string
     */
    private $exceptionClass;
    
    
    
    public function __construct(Tree $tree)
    {
        // exceptions
        $this->exceptionClass = $tree->getExceptionClass();
        
        // get config
        $cnf = require __DIR__ . '/../../../Config/d2.tree.view.config.php';
        
        // get raw replacing pattern
        //$this->raw_replacing_pattern = $cnf['replacing_pattern'];
        
        // get default setting for view
        $view = &$cnf['view'];
                
        // apply settings
        $this->set($view);
    }
    
    /**
     * 
     */
    public function assign(string $prop, $object, string $obj_prop)
    {
        // tree array
        if ($prop === 'view') {
            if (empty($this->view))
                $this->view = [];
        }
               
        $object->assignTo($obj_prop, $this->{$prop});
    }
    
    /**
     * Returns the array with view settings
     */
    public function get() : array
    {
        return $this->view;
    }
    
    /**
     * Sets the array with view settings.
     * 
     * @param array $view An array with view settings.
     */
    public function set(array &$view)
    {
        // Set raw (user comfortable) view
        $this->view = &$view;
        // Prepare view
        $this->prepare($view);
    }
        
    /**
     * Adds a custom view settings.
     * 
     * @param   array   $view  An array with a custom view settings.
     * @return  void
     */
    public function add(array &$view) : void
    {
        // Add settings for defaults.
        $this->addDefaultsSettings($view);

        // Add settings for custom levels
        $this->addLevelsSettings($view);
        
        // Replaces all ~ '<nav>{{}}</nav>' to array ['start'=>'nav', 'end'=>'</nav>']
        $this->prepare($this->view);
    }
    private function addDefaultsSettings(array &$view)
    {
        // splitter
        if (isset($view['splitter']['start'])) {
            $this->view['splitter']['start'] = $view['splitter']['start'];
        }
        if (isset($view['splitter']['end'])) {
            $this->view['splitter']['end'] = $view['splitter']['end'];
        }
        
        // wrapper
        if (isset($view['wrapper']) && is_string($view['wrapper'])) {
            $this->view['wrapper'] = $view['wrapper'];
        }

        // default level
        // 
        // block
        if (isset($view['level']['block']) && is_string($view['level']['block'])) { // it generate E_NOTICE exceptions, but so is more speedly
            $this->view['level']['block'] = $view['level']['block'];
        }
        // item
        if (isset($view['level']['item']) && is_string($view['level']['item'])) {
            $this->view['level']['item'] = $view['level']['item'];
        }
        // content
        if (isset($view['level']['content']) && is_string($view['level']['content'])) {
            $this->view['level']['content'] = $view['level']['content'];
        }
    }
    private function addLevelsSettings(array &$view)
    {
        // levels (only user settings)
        $aside = 0;
        if (isset($view['splitter'])) {
            $aside++;
        }
        if (isset($view['wrapper'])) {
            $aside++;
        }
        if (isset($view['level'])) {
            $aside++;
        }
        if ((count($view) - $aside) > 0) {     // if TRUE, is present sets for levels (exclude defaults)
            foreach($view as $i => &$level) {  // all custom levels
                if (is_int($i)) {               // omit wrapper and level settings (which has string keys)
                    if (isset($level['block']) && is_string($level['block'])) {
                        $this->view[$i]['block'] = $level['block'];
                    }
                    if (isset($level['item']) && is_string($level['item'])) {
                        $this->view[$i]['item'] = $level['item'];
                    }
                    if (isset($level['content']) && is_string($level['content'])) {
                        $this->view[$i]['content'] = $level['content'];
                    }
                }
            }
        }
    }
    
    /**
     * Replaces string template parts with arrays (every array contains 'start'
     * and 'end' attr).
     */
    private function prepare(array &$view)
    {
        $this->prepareDefaults($view);
        $this->prepareLevels($view);
    }
    private function prepareDefaults(array &$view)
    {
        // for more explicit syntax, save separators in short named variables
        list($sp_start, $sp_end, $sp_whole) = $this->getSplitters($view);
        
        // Remake pattern
        //$this->preparePattern($sp_start, $sp_end);
        
        // Wrapper, if not prepared
        if (is_string($view['wrapper'])) {
            $this->prepareWrapper($view, $sp_whole);
        }
        
        // Level Block, if not prepared
        if (is_string($view['level']['block'])) {
            $this->prepareBlock($view, 'level', $sp_whole);
        }
        // Level Item, if not prepared
        if (is_string($view['level']['item'])) {
            $this->prepareItem($view, 'level', $sp_whole);
        }
    }
    private function prepareLevels(array &$view)
    {
        // for more explicit syntax, save separators in short named variables
        list(, , $sp_whole) = $this->getSplitters($view);
        
        // levels (only user settings)
        $aside = 0;
        if (isset($view['splitter'])) {
            $aside++;
        }
        if (isset($view['wrapper'])) {
            $aside++;
        }
        if (isset($view['level'])) {
            $aside++;
        }
        if ((count($view) - $aside) > 0) {     // if TRUE, is present sets for levels (exclude defaults)
            foreach($view as $i => &$level) {  // all custom levels
                if (is_int($i)) {               // omit wrapper and level settings (which has string keys)
                    if (isset($level['block']) && is_string($level['block'])) {
                        $this->prepareBlock($view, $i, $sp_whole);
                    }
                    if (isset($level['item']) && is_string($level['item'])) {
                        $this->prepareItem($view, $i, $sp_whole);
                    }
                }
            }
        }
    }
    public function getSplitters(array &$view = null) : array
    {
        if (is_null($view)) {
            $view = &$this->view;
        }
        
        $sp_start = &$view['splitter']['start'];
        $sp_end = &$view['splitter']['end'];
        $sp_whole = $sp_start . $sp_end;
        
        return [$sp_start, $sp_end, $sp_start . $sp_end];
    }
    
    private function prepareWrapper(array &$view, string &$sp_whole)
    {
        $wrapper_str = $view['wrapper'];
        $view['wrapper'] = [];
        list(
            $view['wrapper']['start'],
            $view['wrapper']['end']
        ) = explode($sp_whole, $wrapper_str);
    }
    private function prepareBlock(array &$view, $level, string &$sp_whole)
    {
        // save defaults
        $this->default_block = $view[$level]['block'];
        
        $block_str = $view[$level]['block'];
        $view[$level]['block'] = [];
        list(
            $view[$level]['block']['start'],
            $view[$level]['block']['end']
        ) = explode($sp_whole, $block_str);
    }
    private function prepareItem(array &$view, $level, string &$sp_whole)
    {
        // save defaults
        $this->default_item = $view[$level]['item'];
        
        $item_str = $view[$level]['item'];
        $view[$level]['item'] = [];
        list(
            $view[$level]['item']['start'],
            $view[$level]['item']['end']
        ) = explode($sp_whole, $item_str);
    }
    
    /**
     * Applies a default settings for a passed level. In fact, creates new level
     * in $this->view array with default setting.
     * 
     * For what?: to use level view settings, while node is outputting, these view
     * settings must be exist.
     * 
     * Used only in outputFast() method, which is used to speed up a 
     * building and outputting process.
     * 
     * @param   int     $level      The number of a tree level. Range: [0; infinity].
     * @return
     */
    public function addLevel($level)
    {
        $view = &$this->view;
        
        // block
        if (! isset($view[$level]['block'])) {
            $view[$level]['block']['start'] = &$view['level']['block']['start'];
            $view[$level]['block']['end'] = &$view['level']['block']['end'];
        }
        // item
        if (! isset($view[$level]['item'])) {
            $view[$level]['item']['start'] = &$view['level']['item']['start'];
            $view[$level]['item']['end'] = &$view['level']['item']['end'];
        }
        // content
        if (! isset($view[$level]['content'])) {
            $view[$level]['content'] = &$view['level']['content'];
        }
    }
    
    /**
     * Fill the view object with new levels and give to level default values. Is has
     * sence only if 'is_relation_next', when levels count is known.
     */
    public function addLevels(int $levels)
    {
        $view = &$this->view;
        for ($i = 0; $i <= $levels; $i++) {
            // block
            if (! isset($view[$i]['block'])) {
                $view[$i]['block']['start'] = &$view['level']['block']['start'];
                $view[$i]['block']['end'] = &$view['level']['block']['end'];
            }
            // item
            if (! isset($view[$i]['item'])) {
                $view[$i]['item']['start'] = &$view['level']['item']['start'];
                $view[$i]['item']['end'] = &$view['level']['item']['end'];
            }
            // content
            if (! isset($view[$i]['content'])) {
                $view[$i]['content'] = &$view['level']['content'];
            }
        }
    }
}
