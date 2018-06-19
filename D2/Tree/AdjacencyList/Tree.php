<?php
/**
 * Arrays Tree Class
 *
 * This file contains Tree class which is using for building and outputting 
 * relational two-dimensional arrays (calling trees).
 *
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\D2\Tree\AdjacencyList;

use Arrays\D2\Tree\OutputReplacer;

/**
 * The Tree class implements helpful methods for building and outputting two-dimensional 
 * arrays (calling trees).
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class Tree
{
    /** Source data */
    
    /**
     * A source array that contains a raw tree.
     * 
     * Later base on these data, will build relations in a new resulting tree 
     * for output or perform any other operations with it.
     * 
     * Assume the source array is sorted.
     * 
     * Required.
     * 
     * @access private
     * @var array
     */
    private $source; 
    
    /**
     * A column name (or index) which contains values for index in a new tree 
     * array.
     * 
     * Required.
     * @access private
     * @var mixed
     */
    private $src_index;
    
    /**
     * A column name (or index), which contains values of parents of nodes.
     * 
     * Required.
     * @access private
     * @var mixed
     */
    private $src_parent;
    
    /**
     * A column name (or index), which contains values of id of a 'next' node for output.
     * 
     * Optional.
     * @access private
     * @var mixed
     */
    private $src_next;
    
    /**
     * A value to determine a top node.
     * 
     * Optional. Default value is 0 (zero).
     * @access private
     * @var mixed
     */
    private $top_ident;
    
    
    /** Tree */
    
    /**
     * A new tree array.
     * 
     * @access private
     * @var array
     */
    private $tree;
    
    /**
     * An array with a sorted tree. The sorted tree is a tree that cab be outputted
     * with a simple foreach loop (without using the 'next', 'nextSibling' or any 
     * other tree attributes).
     * 
     * Initialize by 'get' method.
     * 
     * @access private
     * @var array
     */
    private $sorted;
    
    /**
     * A lot of constans, which is used to set a tree's options.
     */
    const COUNT_CHILDREN = 1;       // 0000 0000 0000 0001
    const COUNT_DESCENDANTS = 16;   // 0000 0000 0001 0000
    const NUMBER_NODES = 256;       // 0000 0001 0000 0000
    const DEBUG_MODE = 4096;        // 0001 0000 0000 0000
    
    /**
     * A tree options.
     * 
     * @access private
     * @var int
     */
    private $options;
    
    /**
     * Id of first found a top node. Initialized by get_top_id method.
     * Here a sorting of a $source array has value, because output will start
     * from a first found top node.
     * 
     * @access private
     * @var mixed
     */
    private $top_id;
        
    /**
     * Total amount of tree levels. Computes while a tree is building.
     * 
     * @access private
     * @var int
     */
    private $levels = 0;
    
    
    /** Output */
    
    /**
     * Contains default view settings. Is initialize with the construct method.
     * 
     * @access private
     * @var array
     */
    private $view;
    
    /**
     * Contains default expression of 'level block' part.
     * 
     * Is extracted to single variable, because a default value of $view is 
     * overriding with apply_custom_view method, but later is required in 
     * build_output method.
     * 
     * @access private
     * @var string
     */
    private $default_block;
    
    /**
     * Contains default expression of 'level item' part.
     * 
     * Is extracted to single variable, because a default value of $view is 
     * overriding with apply_custom_view method, but later is required in 
     * build_output method.
     * 
     * @access private
     * @var string
     */
    private $default_item;
    
    /**
     * Contains pattern to replace column expression to computed value.
     * Initializes in apply_custom_view method with getReplacingPattern method.
     * 
     * @access private
     * @var string
     */
    private $replacing_pattern;
    
    /**
     * An array of objects to computing of expressions in elements ('block',
     * 'item', 'content').
     * 
     * Example:
     * '<ul data-level="{{level}}">{{}}</ul>',          'block' element, an expression is '{{level}}', while an output, it will be replace to a level value
     * '<li data-children="{{children}}">{{}}</li>',    'item' element, an expression is '{{children}}', while an output, it will be replace to a children value
     * '<a class="{{%active%}}" href="{{link}}" data-id="{{id}}">{{%title%}}</a>',      'content' element, expressions is '{{%active%}}', '{{link}}', '{{id}}', '{{%title%}}'
     * 
     * An every object has a callback. Every callback will call, when a node will output. A callback receives 2 
     * parameters: #1. $node, a current row, #2. $result, to write the result of the computing.
     * 
     * @access private
     * @var array
     */
    private $replacer = [];
    
    /**
     * An array of objects to inserting of node/nodes while an output.
     * 
     * An every object has a callback. Every callback will call, when a node will output. A callback receives 2 
     * parameters: #1. $node, a current row, #2. $result, to write the result of the computing.
     * 
     * @access private
     * @var array
     */
    private $inserter = [];
    
    
    /** Flags */
    
    /**
     * Flag used to determine whether to count children of a node or not.
     * 
     * @access private
     * @var bool
     */
    private $fcount_children;
    
    /**
     * Flag used to determine whether to count descendants of a node or not.
     * 
     * @access private
     * @var bool
     */
    private $fcount_descendants;
    
    /**
     * Flag used to determine whether to number nodes or not.
     * 
     * @access private
     * @var bool
     */
    private $fnumber_nodes;
    
    /**
     * Flag used to determine whether to run extended methods or not.
     * 
     * @access private
     * @var bool
     */
    private $is_extended = false;
    
    /**
     * Flag used to informing that a base for a new tree was built.
     * 
     * Required.
     * @access private
     * @var bool
     */
    private $is_base_built = false;
    
    /**
     * Flag used to informing that relations 'child' and 'next sibling' was setted.
     * 
     * Required.
     * @access private
     * @var bool
     */
    private $is_relation_child_sibling_built = false;
    
    /**
     * Flag used to informing that a tree was built (relation 'next' was setted).
     * 
     * Required.
     * @access private
     * @var bool
     */
    private $is_relation_next_built = false;
    
    
    /** Numbering */
        
    /**
     * A delimiter used to split a symbols while numbering of tree 
     * nodes. Usualy it is '.'
     * 
     * @access private
     * @var string
     */
    private $number_delimiter;
        
    /**
     * Numbering types array.
     * 
     * Every key of this array is one of 'NUMBERING_*' consts, and every value is a
     * closure, which is used to generate a current numbering symbol, based on the children
     * number of a node (offset).
     * Example:
     * [NUMBERING_LOWERLATIN] = function ($offset) {...};
     * 
     * @access private
     * @var array
     */
    private $numbering_types;
    
    /**
     * An array which contains settings of numbering for a default or another particular
     * level. Any level can has it own a numbering type.
     * Example: 1.2.1, 1.A.a, A.1, etc.
     * 
     * @access private
     * @var array
     */
    private $level_numbering;
    
    
    /** Debug */
    
    /**
     * Turns on/off debug mode.
     * 
     * @access private
     * @var bool
     */
    private $fdebug_mode;
    
    /**
     * A string with node's id's separated with dots, something like: '22.18.15'.
     * It has a next helpful sense: last 'id' is a node's id, that has a wrong 
     * 'parent' value.
     * '...dc_rbranch' means Descendant Counting Reversed Branch.
     * 
     * @access private
     * @var string
     */
    private $debug_dc_rbranch;
    
    /**
     * An exception class name, which is used while error arise.
     * 
     * @access private
     * @var string
     */
    private $ExceptionClass;
    

    
    /** INITIALIZING */
    
    /**
     * Initializes the tree properties (like a source array, index column, parent
     * column, etc.). Sets default values for view.
     * 
     * @param array     $source         A source raw tree array. It is base for building a relational tree.
     * @param mixed     $src_index      Values for index in a new tree array.
     * @param mixed     $src_parent     A column name (or index) which contains values of parent nodes.
     * @param mixed     $top_ident      A value to detect a top node.
     */
    public function __construct(array &$source, $src_index, $src_parent, $top_ident = '0', $option = 0)
    {
        // PHP Version, min 7.1.1
        if (version_compare(PHP_VERSION, '7.1.1') == -1) {
            echo '\'' . __CLASS__ . '\' class requires PHP version at least 7.1.1. Your current PHP version is ' . phpversion(); exit;
        }
        
        // error handling
        $this->ExceptionClass = '\\LogicException';
        
        // save source
        $this->source($source, $src_index, $src_parent, $top_ident);
        
        // options
        if ($option != 0) {
            $this->options($option);
        }
        
        
        /**
         * Output (default values).
         * 
         * Example of a struct elements: 'wrapper', 'block', 'item' and 'content':
         *      <nav>                                    'wrapper'
         *          <ul>                                 'block'            (can contain 'item')
         *              <li>                             'item'             (can contain 'content' and 'block', or can be a wrapper for 'content')
         *                  <a href="#"></a>             'content'          (only in 'item')
         *                  <ul>                         'block' in 'item'  (only after 'content')
         *                      <li>                     ...
         *                          <a href=""></a>
         *                      </li>
         *                  </ul>
         *              </li>
         *          </ul>
         *      </nav>
         * Splitters: '{{' and '}}' (in fact, they can be any else You will set).
         * If start and end separator written together - '{{}}', it means they 
         * are a splitter of element (wrapper, block, item) for start and end an element part.
         * If they written around one indivisible word, it means they are a replacement and
         * word among them is a column name of $tree array (while tree will be outputting
         * that expression will replace with column value) or a computing expression.
         */
        $this->view = [
            'splitter' => ['start' => '{{', 'end' => '}}'], // yeah, a la Angular, why not...
            'wrapper' => '<div>{{}}</div>',
            'level' => [
                'block' => '<ul>{{}}</ul>',
                'item' => '<li>{{}}</li>',
                'content' => '<a href="#">node id: \'{{' . $this->src_index . '}}\', parent id: \'{{' . $this->src_parent . '}}\'</a>'
           ]
        ];
        $this->default_block = $this->view['level']['block'];
        $this->default_item = $this->view['level']['item'];
        
        // create pattern to replace column expression with column value
        $this->replacing_pattern = $this->getReplacingPattern(
            $this->view['splitter']['start'],
            $this->view['splitter']['end']
        );
        
        // chaining
        //return $this; // returns new object by default
    }
    
    public function __call($name, $arguments)
    {
        ;
    }
    
    
    /** FLAGS */
    
    /**
     * Sets a given tree options.
     * 
     * @param int $option A bit combination of options.
     * @return void
     */
    public function options(int $option = null, bool $add = true, bool $rewrite = true)
    {
        if ($option != null) {
            // Rewrite by default
            if ($rewrite) {
                $this->options = 0;
            }
            
            // Children Counting
            if (self::COUNT_CHILDREN == ($option & self::COUNT_CHILDREN)) {
                if ($add) {
                    $this->fcount_children = true;
                    $this->options |= self::COUNT_CHILDREN;
                } else {
                    $this->fcount_children = false;
                    $this->options ^= self::COUNT_CHILDREN;
                }
            }
            // Descendants Counting
            if (self::COUNT_DESCENDANTS == ($option & self::COUNT_DESCENDANTS)) {
                if ($add) {
                    $this->fcount_descendants = true;
                    $this->options |= self::COUNT_DESCENDANTS;
                } else {
                    $this->fcount_descendants = false;
                    $this->options ^= self::COUNT_DESCENDANTS;
                }
            }
            // Numbering
            if (self::NUMBER_NODES == ($option & self::NUMBER_NODES)) {
                // check flag and the tree options
                if ($add) {
                    $this->numbering();
                } else {
                    $this->fnumber_nodes = false;
                    $this->options ^= self::NUMBER_NODES;
                }
            }
            // Debug mode
            if (self::DEBUG_MODE == ($option & self::DEBUG_MODE)) {
                if ($add) {
                    $this->fdebug_mode = true;
                    $this->options |= self::DEBUG_MODE;
                } else {
                    $this->fdebug_mode = false;
                    $this->options ^= self::DEBUG_MODE;
                }
            }
        } else {
            return $this->options;
        }
    }
    
    /**
     * Adds one or more a tree options.
     * 
     * @param int $option One or more a tree options.
     * @return void
     */
    public function addOptions(int $option)
    {
        $this->options($option, true, false);
    }
    
    /**
     * Removes one or more a tree options.
     * 
     * @param int $option One or more a tree options.
     * @return void
     */
    public function removeOptions(int $option)
    {
        $this->options($option, false, false);
    }
    
    /**
     * Returns current value of a given flag in a tree options.
     * 
     * @param int $option One of a tree options.
     * @return void
     */
    public function getOption(int $option)
    {
        return ($this->options & $option) != 0;
    }
    
    /**
     * Clears all the tree options.
     * 
     * @return void
     */
    public function clearOptions()
    {
        return $this->options = 0;
    }
        
    /**
     * Determines the kind of method.
     * 
     * @return bool TRUE is must run extended types of methods
     */
    private function isExtended() : bool
    {
        $this->is_extended = ($this->fcount_children || $this->fcount_descendants || $this->fnumber_nodes || $this->fdebug_mode);
        return $this->is_extended;
    }
    
    
    
    /**
     * HELPER METHODS  (used by MAIN LOGIC methods)
     */
    
    /**
     * Returns the current raw tree.
     * 
     * @return void
     */
    public function getCurrent() : ?array
    {
        return $this->tree;
    }
    
    /**
     * 
     */
    private function verifySource(array &$source, $src_index, $src_parent, $src_next = null) : bool
    {
        // make sure the source array is not empty
        if (!is_array($source) || (count($source) == 0)) {
            throw new $this->ExceptionClass('The source array is wrong.');
        }

        // make sure that 'index' and 'parent' columns exist in the source array
        foreach ($source as &$first_row) {
            // make sure that the 'index' column exists in source array
            if (!array_key_exists($src_index, $first_row)) { // searches $key in first $row-array of source $array
                throw new $this->ExceptionClass('The source array has no the \'index\' column \''.$src_index.'\'.');
            }
            // the 'index' column exists, save it to 'this'
            $this->src_index = $src_index;

            // make sure that the 'parent' column exists in source array
            if (!array_key_exists($src_parent, $first_row)) { // searches $key in first $row-array of source $array
                throw new $this->ExceptionClass('The source array has no \'parent\' column \'' . $src_parent . '\'.');
            }
            // the 'parent' column exists, save it to 'this'
            $this->src_parent = $src_parent;
            
            // make sure that the 'next' column exists in source array
            if ($src_next != null) {
                if (!array_key_exists($src_next, $first_row)) { // searches $key in first $row-array of source $array
                    throw new $this->ExceptionClass('The source array has no the \'next\' column \''.$src_next.'\'.');
                }
                // the 'next' column exists, save it to 'this'
                $this->src_next = $src_next;
            }
            
            // leave loop
            break;
        }
        
        return true;
    }
    
    /**
     * Sets an array as the source of the tree.
     * 
     * @param array     $source         A source raw tree array. It is base for building a relational tree.
     * @param mixed     $src_index      Values for index in a new tree array.
     * @param mixed     $src_parent     A column name (or index) which contains values of parent nodes.
     * @param mixed     $top_ident      A value to detect a top node.
     */
    public function source(array &$source = null, $src_index = null, $src_parent = null, $top_ident = '0')
    {
        if (is_null($source)) {
            return $this->source;
        } else {
            // Copy data from previous tree, if it exists
            if (empty($src_index) && ! empty($this->src_index)) {
                $src_index = $this->src_index;
            }
            if (empty($src_parent) && ! empty($this->src_parent)) {
                $src_parent = $this->src_parent;
            }
            
            // #0. verify and assing a new source array:
            $this->verifySource($source, $src_index, $src_parent);

            // #1. keep options, view, replacers

            // #2. release flags:
            $this->top_id = null;
            $this->is_base_built = false;
            $this->is_relation_child_sibling_built = false;
            $this->is_relation_next_built = false;

            // #3. release memory
            if (isset($this->tree)) {
                unset($this->tree);
            }
            if (isset($this->sorted)) {
                unset($this->sorted);
            }

            // #4. a determinator of a top node
            $this->top_ident = $top_ident; // top's determinator

            // #5. the source array is proved, save to 'this'
            $this->source = &$source;
        }
    }
    
    /**
     * Wrapper of 'source' method.
     */
    public function setSource(...$params)
    {
        $this->source(...$params);
    }
    
    /**
     * Wrapper of 'source' method.
     */
    public function getSource()
    {
        return $this->source();
    }
    
    /**
     * 
     */
    public function setAsBuild(array &$source, $src_index, $src_parent, $src_next, $top_ident = '0')
    {
        // verify the source array
        $this->verifySource($source, $src_index, $src_parent, $src_next);
        
        // init required parameters
        $this->tree = &$source;
        $this->src_index = $src_index;
        $this->src_parent = $src_parent;
        $this->src_next = $src_next;
        $this->top_ident = $top_ident;
        $this->top_id = null;
        
        // allow directly output
        $this->is_base_built = true;
        $this->is_relation_child_sibling_built = true;
        $this->is_relation_next_built = true;
        
        // chaining
        return $this;
    }
    
    /**
     * Returns a value of the 'id' field of a first top node.
     * 
     * @return mixed Value of 'id' field of a first top node.
     */
    private function getTopId()
    {
        // seek a first top node, if property is empty
        if (!isset($this->top_id)) {
            foreach ($this->tree as &$node) {
                if ($node[$this->src_parent] === $this->top_ident) {
                    $this->top_id = $node[$this->src_index];
                    break;
                }
            }
            
            // any top node isn't found: no sense to continue the tree building
            if (!isset($this->top_id)) {
                throw new $this->ExceptionClass('Any top node isn\'t found (maybe a types mismatch).');
            }
        }
        
        return $this->top_id;
    }
    
    /**
     * Returns the array with view settings
     */
    public function getView() : array
    {
        return $this->view;
    }
    
    /**
     * Sets the array with view settings.
     * 
     * @param array $view An array with view settings.
     */
    public function setView(array &$view)
    {
        $this->view = &$view;
    }
    
    
    /**
     * Descendants counting
     */
    
    /**
     * Increments the children count for all ancestors of a passed node (node is
     * determined with id).
     * 
     * @param mixed     $id         An id of a node to increment the children count of its ancestors.
     * @param mixed     $parent_id  A parent id of the passed node.
     * @return void
     */
    public function countDescendants(&$id, &$parent_id) : void
    {        
        // debug (to determine closure, if exists)
        if ($this->fdebug_mode) $this->debug_dc_rbranch = "[$id]";

        // a reversed branch (to null the 'loop' field)
        $rbranch = [$id];

        // mark node as used in loop (to detect closure, if exists)
        $this->tree[$id]['loop'] = true;

        $prev_dcparent_id = $id;
        $dcparent_id = $parent_id;

        // the descendant counting loop:
        do {
            // verify: did a node output?
            if ($this->tree[$dcparent_id]['loop']) {
                throw new $this->ExceptionClass(
                    'Descendant counting (in '.__METHOD__.'): probably the current node (id: '.$prev_dcparent_id.') contains wrong a \'parent\', '.
                    'because its parent (id: '.$dcparent_id.') again in loop - it is the closure.' .
                    '(debug: reversed branch: ' . $this->debug_dc_rbranch . ').');
            }
            
            // debug (to determine closure, if exists)
            if ($this->fdebug_mode) {
                $this->debug_dc_rbranch .= ".[$dcparent_id]";
            }

            // increase the descendants count of the parent node
            $this->tree[$dcparent_id]['descendants']++;

            // create a reversed branch (to null the 'loop' field of ancestors)
            $rbranch[] = $dcparent_id;

            // mark the node as used in loop (to determine closure, if exists)
            $this->tree[$dcparent_id]['loop'] = true;

            // save the current parent of the node as a prev parent (ancestor)
            $prev_dcparent_id = $dcparent_id;

            // get a next parent (ancestor) in the branch
            $dcparent_id = $this->tree[$dcparent_id][$this->src_parent];

            // check if a parent is a top node
            if ($dcparent_id == $this->top_ident) {
                // null the 'loop' field
                $this->nullLoopAttribute($rbranch);

                // go out from the descendant counting
                break;
            }
        } while(1);
    }
    
    /**
     * Nulls the 'loop' field of a node.
     *
     * @param   array $rev_branch An array is containing a branch from current node to its parents (reversed branch).
     * @return  void
     */
    private function nullLoopAttribute(array &$rev_branch) : void
    {   
        // REVersed BRANCH
        foreach ($rev_branch as $id) {
            $this->tree[$id]['loop'] = false;
        }
    }
    
    
    /** Numbering */
    
    /**
     * 
     */
    public function numbering(array $level_numbering = ['default' => 'Decimal'], string $delimiter = '.') : self
    {
        // set flag and options
        $this->fnumber_nodes = true;
        $this->options |= self::NUMBER_NODES;
                    
        // initialize the numbering
        $this->level_numbering = &$level_numbering;
        
        // a symbol delimiter
        $this->number_delimiter = $delimiter;
        
        // load a numerators
        $this->numbering_types = [];
        foreach ($level_numbering as &$numbering_type) {
            if (!isset($this->numbering_types[$numbering_type])) {
                $this->numbering_types[$numbering_type] = $this->getNumerator($numbering_type);
            }
        }
        
        // chaining
        return $this;
    }
    
    /**
     * 
     */
    private function getNumerator(string &$name) : \Closure
    {
        // a numerator file
        $path = __DIR__ . '/../../../Numerator/' . $name . '.numerator.php';
        
        // load the numerator
        $numerator = include($path);
        
        // has a problems while a loading?
        if (false === $numerator)
        {
            throw new $this->ExceptionClass('A numerator file \'' . $path . '\' cannot be loaded.');
        }
        
        // verify the numerator
        if(false === $this->isNumerator($numerator))
        {
            throw new $this->ExceptionClass('The numerator has errors (review the code of the numerator \''.$path.'\').');
        }
        
        return $numerator;
    }
    
    /**
     * 
     */
    private function isNumerator($numerator) : bool
    {
        if (is_callable($numerator)) {
            // get a reflection object to verify the callable
            $refl = new \ReflectionFunction($numerator);
            
            // closure parameters
            $total_params = 1; // int &$offset
            $num_of_rparams = $refl->getNumberOfRequiredParameters();
            if ($num_of_rparams != $total_params) {
                throw new $this->ExceptionClass('The numerator method has wrong signature: is passed '.$num_of_rparams.' required parameters, but need - '.$total_params.'.');
            }
            // parameters types
            $error_message = 'The return type of the numerator or a parameter of the numerator method is wrong (it has not an allowed name or is passed not by reference).';
            $params = $refl->getParameters();
            $pi = -1;
            // 'int &$offset' parameter
            $prm = $params[++$pi];
            if (!($prm->getName() === 'offset' && $prm->isPassedByReference() && $prm->hasType() && $prm->getType()->getName() == 'int')) {
                throw new $this->ExceptionClass($error_message);
            }
            
            // closure
            if (!($refl->hasReturnType() && $refl->getReturnType()->getName() == 'string')) {
                throw new $this->ExceptionClass($error_message);
            }
            
            // all a numerator parameters is valid
            return true;
        }
        
        // glaring mistake: $numerator is not a function
        throw new $this->ExceptionClass('The passed numerator is not a function.');
    }
        
    /**
     * Sets an appropriate numbering symbol for a given node.
     * 
     * @param   array   A node row.
     * @return  void
     */
    private function numerator(array &$node)
    {        
        // get a numbering type (DECIMAL, UPPERLATIN, LOWERLATIN, etc.) for a given level
        $nb = isset($this->level_numbering[$node['level']]) ? $this->level_numbering[$node['level']] : $this->level_numbering['default'];
        
        // get a numerator for the numbering
        $nb_numerator = $this->numbering_types[$nb];
        
        // the full numbering symbol of the parent node (something like: '1', '1.1.3', 'A.1', 'A.B', etc.)
        $numbering = ($node[$this->src_parent] != $this->top_ident) ? $this->tree[$node[$this->src_parent]]['numbering'] : '';
        
        // add a delimiter
        if (!empty($numbering)) $numbering .= $this->number_delimiter;
        
        // create a full node numbering symbol
        $numbering .= $nb_numerator($node['childNumber']); // '1.1.' . '3'
        
        // set the numbering symbol to the node
        $node['numbering'] = $numbering;
    }


    /** Output */
    
    /**
     * Applies a custom user view settings and default setting.
     * 
     * @param   array   $cview  An array with a custom user view settings.
     * @return  void
     */
    private function applyCustomView(array &$cview) : void
    {
        // splitter
        if (isset($cview['splitter'])){
            if (isset($cview['splitter']['start'])){
                $this->view['splitter']['start'] = $cview['splitter']['start'];
            }
            if (isset($cview['splitter']['end'])) {
                $this->view['splitter']['end'] = $cview['splitter']['end'];
            }
        }
        // for more explicit syntax, save separators in short named variables
        $splitter_start = $this->view['splitter']['start'];
        $splitter_end = $this->view['splitter']['end'];
        // create pattern for replace a column expression with a column value
        $this->replacing_pattern = $this->getReplacingPattern($splitter_start, $splitter_end);

        // wrapper
        $default_wrapper = $this->view['wrapper'];
        $this->view['wrapper'] = [];
        if (isset($cview['wrapper'])){
            list($this->view['wrapper']['start'], $this->view['wrapper']['end']) = 
                explode($splitter_start.$splitter_end, $cview['wrapper']);
        } else {
            list($this->view['wrapper']['start'], $this->view['wrapper']['end']) = 
                explode($splitter_start.$splitter_end, $default_wrapper);
        }

        // default level
        // block
        $this->view['level']['block'] = [];
        if (isset($cview['level'])  &&  isset($cview['level']['block'])) {
            $this->default_block = $cview['level']['block'];
            list($this->view['level']['block']['start'], $this->view['level']['block']['end']) = 
                explode($splitter_start.$splitter_end, $cview['level']['block']);
        } else {
            list($this->view['level']['block']['start'], $this->view['level']['block']['end']) = 
                explode($splitter_start.$splitter_end, $this->default_block);
        }
        // item
        $this->view['level']['item'] = [];
        if (isset($cview['level']) &&  isset($cview['level']['item'])) {
            $this->default_item = $cview['level']['item'];
            list($this->view['level']['item']['start'], $this->view['level']['item']['end']) = 
                explode($splitter_start.$splitter_end, $cview['level']['item']);
        } else {
            list($this->view['level']['item']['start'], $this->view['level']['item']['end']) = 
                explode($splitter_start.$splitter_end, $this->default_item);
        }
        // content
        if (isset($cview['level']) &&  isset($cview['level']['content'])) {
            $this->view['level']['content'] = $cview['level']['content'];
        }

        // levels (only user settings)
        $aside = 0;
        if (isset($cview['splitter'])) {
            $aside++;
        }
        if (isset($cview['wrapper'])) {
            $aside++;
        }
        if (isset($cview['level'])) {
            $aside++;
        }
        if ((count($cview) - $aside) > 0) {     // if TRUE, is present sets for levels (exclude defaults)
            foreach($cview as $i => &$level) {  // all custom levels
                if (is_int($i)) {               // omit wrapper and level settings (which has string keys)
                    if (isset($level['block'])) {
                        list($this->view[$i]['block']['start'], $this->view[$i]['block']['end']) = 
                            explode($splitter_start.$splitter_end, $level['block']);
                    }
                    if (isset($level['item'])) {
                        list($this->view[$i]['item']['start'], $this->view[$i]['item']['end']) = 
                            explode($splitter_start.$splitter_end, $level['item']);
                    }
                    if (isset($level['content'])) {
                        $this->view[$i]['content'] = $level['content'];
                    }
                }
            }
        }

        // fill the view object with default values for all other levels
        if ($this->is_relation_next_built) { // ONLY after 'relation_next' method
            for ($i = 0; $i <= $this->levels; $i++) { 
                // block
                if (!isset($this->view[$i]['block'])) {
                    list($this->view[$i]['block']['start'], $this->view[$i]['block']['end']) = 
                        explode($splitter_start.$splitter_end, $this->default_block);
                }
                // item
                if (!isset($this->view[$i]['item'])) {
                    list($this->view[$i]['item']['start'], $this->view[$i]['item']['end']) = 
                        explode($splitter_start.$splitter_end, $this->default_item);
                }
                // content
                if (!isset($this->view[$i]['content'])) {
                    $this->view[$i]['content'] = $this->view['level']['content'];
                }
            }
        }
    }
    
    /**
     * Applies a default settings for a passed level. In fact, creates new level
     * in $this->view array with default setting.
     * 
     * Principal: to use level view settings, while node is outputting, this view
     * settings must be exist.
     * 
     * Used only in relationsOutput() method, which is used to speed up a 
     * building and outputting process.
     * 
     * @param   int     $level      The number of a tree level. Range: [0; infinity].
     * @return
     */
    private function addLevelToView($level)
    {
        // for more explicit syntax save separators in short named variables
        $splitter_start = $this->view['splitter']['start'];
        $splitter_end = $this->view['splitter']['end'];
        // block
        if (!isset($this->view[$level]['block'])) {
            list($this->view[$level]['block']['start'], $this->view[$level]['block']['end']) = 
                explode($splitter_start.$splitter_end, $this->default_block);
        }
        // item
        if (!isset($this->view[$level]['item'])) {
            list($this->view[$level]['item']['start'], $this->view[$level]['item']['end']) = 
                explode($splitter_start.$splitter_end, $this->default_item);
        }
        // content
        if (!isset($this->view[$level]['content'])) {
            $this->view[$level]['content'] = $this->view['level']['content'];
        }
    }
    
    /**
     * Adds a new replacer (a callable).
     * 
     * Callable signature: 
     * function(&$id, &$result) : bool {}.
     * 
     * @param string    $key        A replacer key.
     * @param callable  $replacer   A anonymous function (with signature identical to described above).
     * @return void
     */
    public function addReplacer(string $key, OutputReplacer $or_obj) : void
    {
        // verifying signature of $replacer
        $replacer = $or_obj->replacer;
        if (is_callable($replacer)) {
            // get a reflection object to verify the callable
            $refl = new \ReflectionFunction($replacer);
            
            // parameters
            $total_params = 2; // $id, $result
            $num_of_rparams = $refl->getNumberOfRequiredParameters();
            if ($num_of_rparams != $total_params) {
                throw new $this->ExceptionClass('The replacer method has wrong signature: is passed '.$num_of_rparams.' required parameters, but need - 2.');
            }
            
            // parameters types
            $error_message = 'A parameter of the replacer method is wrong (it has not an allowed name or is passed not by reference)).';
            $params = $refl->getParameters();
            $pi = -1;
            // $id parameter
            $prm = $params[++$pi];
            if (!($prm->getName() === 'node' && $prm->isPassedByReference())) {
                throw new $this->ExceptionClass($error_message);
            }
            // $result parameter
            $prm = $params[++$pi];
            if (!($prm->getName() === 'result' && $prm->isPassedByReference())) {
                throw new $this->ExceptionClass($error_message);
            }
            
            // all parameters are valid: add the new callback to the replacer array
            $this->replacer[$key] = $or_obj;
            
            // stop edding
            return;
        }
        
        // glaring mistake: $replacer is not a function
        throw new $this->ExceptionClass('The passed replacer is not a function.');
    }
    
    /**
     * Creates a pattern to matching expressions within an element.
     * An element expression is something like: 
     * '{{%class-aitem%}}' or '{{link}}'.
     * If in html:
     * '<a {{%class-aitem%}} href="{{link}}" data-id="{{id}}">{{%title%}}</a>'
     * 
     * @param   string  $splitter_start  A start splitter to determine a beginning of an element expression.
     * @param   string  $splitter_end    A end splitter to determine a end of an element expression.
     * @return  string                   A resulting pattern.
     */
    private function getReplacingPattern(string &$splitter_start, string &$splitter_end) : string
    {
        // a left part of the splitter
        $start = '';
        $l = strlen($splitter_start);
        for ($i = 0; $i < $l; $i++) {
            $start .= '\\'.$splitter_start[$i];
        }
        // a rihgt part of the splitter
        $end = '';
        $l = strlen($splitter_end);
        for ($i = 0; $i < $l; $i++) {
            $end .= '\\'.$splitter_end[$i];
        }
        // a resulting pattern
        return '/'.$start.'([a-zA-Z%0-9][a-zA-Z0-9%]*)'.$end.'/U';
    }
    
    /**
     * Replaces an element expression with a computed value.
     * 
     * Example. Something like:
     * <a {{%class-aitem%}} href="{{link}}" data-id="{{id}}">{{%title%}}</a>
     * will be replace and the result in html maybe next:
     * <a class="active" href="/admin/menu" data-id="5">Menu - Control panel</a>
     * 
     * @param array $node       An array of the current node.
     * @param string $elem_expr An element expression, ex.: '%class-aitem%', 'link', etc.
     * @return string           Returns computed element string. For {{%class-aitem%}} it will be 'class="active"' etc.
     */
    private function getNodeString(array &$node, string &$element_expression) : string
    {
        // use 'preg_replace' with callback
        $element_string = preg_replace_callback(   // MAIN FUNCTION
            $this->replacing_pattern,                // usually it is: "/\{\{([a-zA-Z%0-9][a-zA-Z0-9%]*)\}\}/U" or more clearly "/{{([a-zA-Z%0-9][a-zA-Z0-9%]*)}}/U". Examples, "{{title}}", "{{description}}", "{{%class-active%}}", "{{%active%}}", etc.
            function ($matches) use (&$node) {     // callback: will run for every occurence pattern {{...}} in $elem_expression
                // pattern matches a replacing part and passes matches to this function
                $replacing_part = &$matches[1]; // here the $replacing_part will be: 'title', 'description', '%class-active%', '%active%', etc.
                return $node[$replacing_part];
            },
            $element_expression
        );
        
        return $element_string;
    }
    
    /**
     * An analog of the function 'getNodeString' with only a difference in an 
     * using of replacers for a computing of node's expressions. Was created for
     * a boosting of the output with 'getNodeString', if the 'replacer' array is
     * empty.
     * 
     * @param array $node       An array of the current node.
     * @param string $elem_expr An element expression, ex.: '%class-aitem%', 'link', etc.
     * @return string           Returns computed element string. For {{%class-aitem%}} it will be 'class="active"' etc.
     */
    private function getNodeStringUseReplacer(array &$node, string &$element_expression) : string
    {
        // use 'preg_replace' with callback
        $element_string = preg_replace_callback(   // MAIN FUNCTION
            $this->replacing_pattern,                // usually it is: "/\{\{([a-zA-Z%0-9][a-zA-Z0-9%]*)\}\}/U" or more clearly "/{{([a-zA-Z%0-9][a-zA-Z0-9%]*)}}/U". Examples, "{{title}}", "{{description}}", "{{%class-active%}}", "{{%active%}}", etc.
            function ($matches) use (&$node) {     // callback: will run for every occurence pattern {{...}} in $elem_expression
                // pattern matches a replacing part and passes matches to this function
                $replacing_part = &$matches[1]; // here the $replacing_part will be: 'title', 'description', '%class-active%', '%active%', etc.
                // 1) a column expression. NOTE: if write: 'isset($this->tree[$id][$expr_content])' a result maybe FALSE, because value in cell can be NULL (but it is!), therefore is wrong to use this construction
                if (array_key_exists($replacing_part, $node)) {
                    return $node[$replacing_part];
                }
                // 2) another expression, like active menu, title, some computions, etc.
                $replacer_obj = $this->replacer[$replacing_part];
                if (!is_null($replacer_obj)) {
                    $result = '';
                    $replacer = $replacer_obj->replacer;
                    if (true === $replacer($node, $result)) { // array keys is unique, but something while a replacing can be wrong
                        return $result;
                    }
                }
                // 3) nothing is matched
                return '';
            },
            $element_expression
        );
        
        return $element_string;
    }
    
    /**
     * Adds plenty of nodes to the output.
     */
    private function getNodeStringUseReplacerInserter()
    {
        
    }

    
    
    /** LOGIC METHODS  (implements a main logic of the class) */
    
    /**
     * Creates a new tree array and initialize it by the source array. Plus adds
     * new fields to the new tree array.
     * 
     * @return void
     */
    private function base() : self
    {
        // if a tree already built
        if ($this->is_base_built) {
            return $this;
        }
        
        // is the tree extended?
        if ($this->is_extended) {
            $this->baseExtended();
            return $this;
        }
        
        // a new tree array
        $tree = [];
        // write all rows (nodes) of the source array to a new tree array
        foreach ($this->source as &$src_node) {
            // the value of 'src_index' column of the source array became an index value of the new tree array
            $i = $src_node[$this->src_index];     // note at now $i has the "string" type,
            $tree[$i] = &$src_node;               // but now $i, like an array index, has "int" type: PHP automatically converts string numbers to its integer values,
            // that's why expression like '$this->tree[$prevtop_id]' or more explicitly '$this->tree["1"]' returns an array element, but not a PHP Notice.
            // relations properties
            $tree[$i]['firstChild'] = null;         // an id/index of a first child
            $tree[$i]['lastChild'] = null;          // an id/index of a last child
            $tree[$i]['nextSibl'] = null;           // an id/index of a next sibling
            $tree[$i]['next'] = null;               // an id/index of a next outputting node
            // statistics properties
            $tree[$i]['level'] = 0;                 // a node's level
            $tree[$i]['children'] = 0;              // children total
            $tree[$i]['added'] = null;              // is used to determine a closure in the tree
            $tree[$i]['upLoop'] = null;             // is used to determine a closure in the tree within 'parent' attribute while 'up loop' in 'relation_next' method
        }
        
        // save the new tree
        $this->tree = &$tree;
        
        // mark the tree as prepared for a relations building
        $this->is_base_built = true;
        
        // chaining
        return $this;
    }
    private function baseExtended()
    {
        // a new tree array
        $tree = [];
        // write all rows (nodes) of the source array to a new tree array
        foreach ($this->source as &$src_node) {
            // the value of 'src_index' column of the source array became an index value of the new tree array
            $i = $src_node[$this->src_index];     // note at now $i has the "string" type,
            $tree[$i] = &$src_node;               // but now $i, like an array index, has "int" type: PHP automatically converts string numbers to its integer values,
            // that's why expression like '$this->tree[$prevtop_id]' or more explicitly '$this->tree["1"]' returns an array element, but not a PHP Notice.
            // relations properties
            $tree[$i]['firstChild'] = null;         // an id/index of a first child
            $tree[$i]['lastChild'] = null;          // an id/index of a last child
            $tree[$i]['nextSibl'] = null;           // an id/index of a next sibling
            $tree[$i]['next'] = null;               // an id/index of a next outputting node
            // statistics properties
            $tree[$i]['level'] = 0;                 // a node's level
            $tree[$i]['children'] = 0;              // children total
            if ($this->fcount_descendants) {
                $tree[$i]['descendants'] = 0;       // descendants total
            }
            if ($this->fnumber_nodes) {
                $tree[$i]['childNumber'] = 0;       // an order in a children array. It will last number in 'numbering' property
                $tree[$i]['numbering'] = null;      // a numbering expression: '1.1', '1.2.1'
            }
            $tree[$i]['loop'] = false;              // is used in relationChildSibling()
            $tree[$i]['added'] = null;              // is used to determine a closure in the tree
            $tree[$i]['upLoop'] = null;             // is used to determine a closure in the tree within 'parent' attribute while 'up loop' in 'relation_next' method
        }
        // save the new tree
        $this->tree = &$tree;
        // mark the tree as prepared for a relations building
        $this->is_base_built = true;
    }
    
    /**
     * Sets 2 types of relations between nodes:
     * - 'who is a next sibling node'
     * - 'who is a child node'
     * 
     * @return void
     */
    private function relationChildSibling() : self
    {
        // if the relation 'child' and 'sibling' already built
        if ($this->is_relation_child_sibling_built) {
            return $this;
        }
                
        // verify: has a tree a base to build relations?
        if (!$this->is_base_built) {
            throw new $this->ExceptionClass('The tree has no a base for building any relations, call $this->base() before.');
        }
        
        // previous top node id
        $prevtop_id = null;    
        // building relations 'child' and 'sibling'
        foreach ($this->tree as $id => &$node) {
            // get the parent for a current node
            $parent_id = $node[$this->src_parent];
            
            // 'top' nodes
            if ($parent_id === $this->top_ident) {
                // is current node a first top node?
                if (isset($prevtop_id)) {
                    // the current node isn't a first top node
                    // save current node as 'nextSibl' in a previous top node: relation 'sibling'
                    $this->tree[$prevtop_id]['nextSibl'] = $id;
                    // numbering (preparing, initial data)
                    if ($this->fnumber_nodes) {
                        $node['childNumber'] = $this->tree[$prevtop_id]['childNumber'] + 1;
                    }
                } else {
                    // the current node is the first top node
                    // numbering (preparing, initial data)
                    if ($this->fnumber_nodes) {
                        $node['childNumber'] = 1;
                    }
                }
                // the current node becomes a previous top node
                $prevtop_id = $id;    
                // any top node has no parent, therefore go to a next node in the tree
                continue;
            }
                        
            // 'child' nodes
            // verify: has node a parent?
            if (!isset($this->tree[$parent_id])) {
                throw new $this->ExceptionClass('Node (id: ' . $id . ') has no parent (id: ' . $parent_id . ').');
            }
            // is the current node a first child?
            if (is_null($this->tree[$parent_id]['firstChild'])) {
                // the current node is the first child
                // save node as a first child: relation 'child'
                $this->tree[$parent_id]['firstChild'] = $id;
                // save the node as a last child too, if a node has siblings: for relaion 'sibling'
                $this->tree[$parent_id]['lastChild'] = $id;
                // mumbering (preparing, initial data)
                if ($this->fnumber_nodes) {
                    $node['childNumber'] = 1;
                }
            } else {
                // the current node isn't first child, then it is some sibling node
                // save the last child to tmp variable
                $last_child = $this->tree[$parent_id]['lastChild'];
                // save a current node as a last child: for relaion 'sibling'
                $this->tree[$parent_id]['lastChild'] = $id;
                // save a current node as a next sibling node: relaion 'sibling'
                $this->tree[$last_child]['nextSibl'] = $id;
                // numbering (preparing, initial data)
                if ($this->fnumber_nodes) {
                    $node['childNumber'] = $this->tree[$last_child]['childNumber'] + 1;
                }
            }
            
            // children counting
            if ($this->fcount_children) {
                $this->tree[$parent_id]['children']++;
            }
            
            // descendant counting
            if ($this->fcount_descendants) {
                $this->countDescendants($id, $parent_id);
            }
        }
        
        // mark the tree as prepared to build the 'next' relation (has 'child' and 'sibling' relations)
        $this->is_relation_child_sibling_built = true;
        
        // chaining
        return $this;
    }
    
    /**
     * Sets finally relations between nodes, relation: 'who is next node'.
     * 
     * Total loops (for source array):
     * 1. base for the tree
     * 2. set relations 'child' and 'next sibling'
     * ?. seek top node
     * 3. set a relation 'next'
     * 
     * @return Arrays\D2\Tree\AdjacencyList\Tree Returns a Tree object for support chaining.
     */
    private function relationNext() : self
    {
        // if the relation 'next' already built
        if ($this->is_relation_next_built) {
            return $this;
        }
        
        // verify: has the tree 'child' and 'sibling' relations?
        if (!$this->is_relation_child_sibling_built) {
            throw new $this->ExceptionClass('The tree has no \'child\' and \'sibling\' relations, call $this->relationChildSibling() before.');
        }
                
        // a start level
        $level = 0;
        // get a first top node
        $top_id = $this->getTopId();
        // the first top node. Settings
        $this->tree[$top_id]['level'] = $level;
        $this->tree[$top_id]['added'] = true;
        // the first top node. Numbering
        if ($this->fnumber_nodes) {
            $this->numerator($this->tree[$top_id]);
        }
        // a current and previous node ID
        $id = $top_id;
        $prev_id = $top_id;
        
        // Tree Building
        do {
            // seek a next node:
            // if a node is child
            if (isset($this->tree[$id]['firstChild'])) {
                $id = $this->tree[$id]['firstChild'];
                $level++;
            } // if a node is sibling
            elseif (isset($this->tree[$id]['nextSibl'])) {
                $id = $this->tree[$id]['nextSibl'];
                //$level++; // Level does not changes
            }
            else { // the cureent node has no any child or sibling
                // the node is a single top or a last top - has no any children or siblings
                if ($this->tree[$id][$this->src_parent] === $this->top_ident) {
                    break;
                }
                
                // go up to check its parent or ancestors
                $this->tree[$id]['upLoop'] = true; // protection: to determine a closure with the 'parent' attribute and to stop the process
                $up_id = $this->tree[$id][$this->src_parent];
                $level--;
                do {
                    // protection: to determine a closure with the 'parent' attribute and to stop the process
                    if ($this->tree[$up_id]['upLoop']) {
                        throw new $this->ExceptionClass('FATAL ERROR: a closure in the tree, the node (id: ' . $id . ') was used within the \'Up-loop\' and is using again.');
                    } else {
                        $this->tree[$up_id]['upLoop'] = true;
                    }
                    
                    // seek sibling
                    if (isset($this->tree[$up_id]['nextSibl'])) {
                        // found sibling! 
                        $id = $this->tree[$up_id]['nextSibl']; 
                        break; // 'breake' is necessary
                    } else {
                        if ($this->tree[$up_id][$this->src_parent] === $this->top_ident) {
                            // it is a top node and it has no a sibling
                            break 2; // the tree is built!
                        } else {
                            // the node has no a sibling and is not a top node, therefore get its parent and continue search
                            $up_id = $this->tree[$up_id][$this->src_parent];
                            $level--;
                        }
                    }
                } while(1);
            }

            // protection: to determine closure and stop process
            if ($this->tree[$id]['added']) {
                throw new $this->ExceptionClass('FATAL ERROR: a closure in the tree, the node (id: ' . $id . ') was added to the tree and is adding again.');
            }
            
            // save a node level
            $this->tree[$id]['level'] = $level;
            
            // numbering
            if ($this->fnumber_nodes) {
                $this->numerator($this->tree[$id]);
            }
            
            // a total count of a tree levels
            if ($level > $this->levels) {
                $this->levels = $level;
            }
            
            // save a current node as 'next' in a previous node ('next' relation)
            $this->tree[$prev_id]['next'] = $id;
            
            // the current node becomes a previous node
            $prev_id = $id;
            
            // mark the node as added
            $this->tree[$id]['added'] = true;
            // go to a 'next' node!...
        } while(1);
        
        // mark the tree as prepared to the output (has the 'next' relation)
        $this->is_relation_next_built = true;
        
        // chaining
        return $this;
    }
        

    
    /** RESULTING METHODS  (allows to get a result: a sorted array with the tree or the outputted tree) */
    
    /**
     * Creates and returns a new sorted array (based on relation 'next' of the tree array).
     * This new returning array is a common 2D array and can be outputted by a simple 'foreach' loop.
     * 
     * Total loops (at source array):
     * 1. base for tree (base())
     * 2. set relations 'child' and 'next sibling' (relationChildSibling())
     * ?. seek top node (if it has middle or last place, it halb or full loop, but if $source is sorted it will first node)
     * 3. set relation 'next' (relationNext())
     * 4. forming new sorted array (get())
     * 
     * In most cases this method is used in output methods, which output 2D 
     * arrays in a form of a table.
     * 
     * @return array A new tree sorted array.
     */
    public function getAsArray() : array 
    {
        // is exists the sorted tree?
        if (!empty($this->sorted)) {
            return $this->sorted;
        }
        
        // determine a kind of method to be run
        $this->isExtended();
        
        // create a new array which is based on the source array
        $this->base();
        
        // set relations 'child' and 'sibling'
        $this->relationChildSibling();
        
        // set relation 'next'
        $this->relationNext(); // zt now the tree is built and can be outputted
        
        // make a new sorded array from a relational tree array
        $id = $this->getTopId();
        do {
            $sorded_tree[] = &$this->tree[$id];
            $id = &$this->tree[$id]['next'];
            if (is_null($id)) { 
                break; // the new sorted array has formed, go out the loop
            }
        } while (1);

        // save result
        $this->sorted = &$sorded_tree;
        
        return $sorded_tree;
    }
    
    /**
     * Outputs a relational tree (with do-while loop).
     * 
     * Total loops/iterates (at source array):
     * 1. base for tree (base())
     * 2. set relations 'child' and 'next sibling' (relationChildSibling())
     * ?. seek top node (if it has middle or last place, it halb or full loop, but if $source is sorted it will first node)
     * 3. set relation 'next' (relationNext())
     * 4. ouput loop
     * 
     * Uses only the 'next' field and user custom view settings.
     *  
     * @param array $cview  The array with custom user view settings.
     * @return void
     */
    public function output(array &$cview = null) : void
    {
        // determine the kind of method to the run
        $this->isExtended();
        
        // create a new array, which is based on the source array
        $this->base();
        
        // set relations 'child' and 'sibling'
        $this->relationChildSibling();
        
        // set the relation 'next'
        $this->relationNext(); // at now the tree is built and can be outputted       
        
        // apply custom user settings of a view
        if (!is_null($cview)) {
            $this->applyCustomView($cview);
        }
        
        // the first top node: id
        $id = $this->getTopId();
        // the first top node: level
        $out_level = $this->tree[$id]['level'];
        $prev_level = $out_level;
        
        // the 'next' attribute
        $next_attr = $this->src_next == null ? 'next' : $this->src_next;
        
        
        // OUTPUT
        
        // output a open tag of a wrapper
        echo $this->view['wrapper']['start'];
        
        // first node
        // output a open tag of a block
        echo $this->getNodeString($this->tree[$id], $this->view[$out_level]['block']['start']);
        // output a open tag of a item
        echo $this->getNodeString($this->tree[$id], $this->view[$out_level]['item']['start']);
        // output a tag of a content
        echo $this->getNodeString($this->tree[$id], $this->view[$out_level]['content']);
        // [output a close tag of a item: if a node has no children]
        if ($this->tree[$id]['children'] == 0) {
            echo $this->view[$out_level]['item']['end'];
        }
        
        do {
            // get a next node
            $id = $this->tree[$id][$next_attr];
            if (is_null($id)) {
                break; // the tree is outputted
            }
            
            // get a level of the current node
            $level = $this->tree[$id]['level'];
            
            if ($level > $prev_level) {
                // the current node level is bigger, than a previous node level: output a open tag of a block
                echo $this->getNodeString($this->tree[$id], $this->view[$level]['block']['start']);
            }
            
            if (($lc = $level - $prev_level) < 0) {
                // the current node level is less, than a previous node level: put one or few end tags for a view block and a view item
                $lc = abs($lc);
                for ($i = 1; $i <= $lc; $i++) {
                    // output a close tag of a block
                    echo $this->view[$prev_level - $i]['block']['end'];
                    
                    // output a close tag of a item
                    echo $this->view[$prev_level - $i]['item']['end'];
                }
            }
            // output a open tag of a item
            echo $this->getNodeString($this->tree[$id], $this->view[$level]['item']['start']);

            // output a tag of a content
            echo $this->getNodeString($this->tree[$id], $this->view[$level]['content']);

            // [output a close tag of a item: if a item has no children]
            if ($this->tree[$id]['children'] == 0) {
                echo $this->view[$level]['item']['end'];
            }

            // save the level
            $prev_level = $level;
            // go to a 'next' node!...
        } while (1) ;
        
        // output a close tag of a block
        echo $this->view[$out_level]['block']['end'];
        
        // output a close tag of a wrapper
        echo $this->view['wrapper']['end'];
    }
    
    private function getNextNode(&$id, &$level) : bool
    {
        if (isset($this->tree[$id]['firstChild'])) {
            $id = $this->tree[$id]['firstChild'];
            $level++;
        } // if a node is sibling
        elseif (isset($this->tree[$id]['nextSibl'])) {
            $id = $this->tree[$id]['nextSibl'];
            //$level++; // Level does not changes
        }
        else { // the cureent node has no any child or sibling
            // the node is a single top or a last top - has no any children or siblings
            if ($this->tree[$id][$this->src_parent] === $this->top_ident) {
                return false;
            }

            // go up to check its parent or ancestors
            $this->tree[$id]['upLoop'] = true; // protection: to determine a closure with the 'parent' attribute and to stop the process
            $up_id = $this->tree[$id][$this->src_parent];
            $level--;
            do {
                // protection: to determine a closure with the 'parent' attribute and to stop the process
                if ($this->tree[$up_id]['upLoop']) {
                    throw new $this->ExceptionClass('FATAL ERROR: a closure in the tree, the node (id: ' . $id . ') was used within the \'Up-loop\' and is using again.');
                } else {
                    $this->tree[$up_id]['upLoop'] = true;
                }

                // seek sibling
                if (isset($this->tree[$up_id]['nextSibl'])) {
                    // found sibling! 
                    $id = $this->tree[$up_id]['nextSibl']; 
                    break; // 'breake' is necessary
                } else {
                    if ($this->tree[$up_id][$this->src_parent] === $this->top_ident) {
                        // it is a top node and it has no a sibling
                        return false; // the tree is built!
                    } else {
                        // the node has no a sibling and is not a top node, therefore get its parent and continue search
                        $up_id = $this->tree[$up_id][$this->src_parent];
                        $level--;
                    }
                }
            } while(1);
        }
               
        return true;
    }
    
    /**
     * Builds a tree and parallely outputs it.
     * 
     * Gives a one big advantage: one less an iterate at a tree (if a tree has 
     * thousands nodes, the using this method can very increase the performance).
     * 
     * Total loops (at a tree array):
     * 1. build base for tree, base()
     * 2. set relations 'child' and 'next sibling', relationChildSibling()
     * ?. seek first top node
     * 3. set relation 'next' and output node
     * 
     * @param array $cview  An array with custom user settings of a view.
     * @return void
     */
    public function quicklyOutput(array &$cview = null) : void
    {
        // protection: if is used built tree as source, then this method must not be used
        if ($this->is_relation_next_built) {
            return;
        }
        
        // determine the kind of method to the run
        $this->isExtended();
        
        // create a new array, which is based on the source array
        $this->base();
        
        // set relations 'child' and 'sibling'
        $this->relationChildSibling();

        // level values
        $level = 0; // current level
        $out_level = $level; // out level
        $prev_level = $level; // previous level

        // get the first top node
        $top_id = $this->getTopId();
        $this->tree[$top_id]['level'] = $level;
        $this->tree[$top_id]['added'] = true; // mark a node as added to a tree, to help determine a closure

        // a current and previous node id
        $id = $top_id;
        $prev_id = $top_id;
        
        // apply custom user settings of a view
        if (!is_null($cview)) {
            $this->applyCustomView($cview);
        }
        
        // get an appropriate replacer
        $getNodeString = 'getNodeString';
        if (!empty($this->replacer)) {
            $getNodeString .= 'UseReplacer';
        }
        //if (!empty($this->inserter)) {
        //    $nodeString .= 'Inserter';
        //}
        
        
        // Output
        //pm_start('Output in '.__METHOD__.'+getNextNode(): 100k');
        // output a open tag of a wrapper
        echo $this->view['wrapper']['start'];
        
        // if view settings for the current level will not be create, below in the 'get_elem_string' method PHP will throw a fatal
        $this->addLevelToView($level);
        
        // first node
        // output a open tag of a block
        echo $this->$getNodeString($this->tree[$id], $this->view[$out_level]['block']['start']);
        // output a open tag of a item
        echo $this->$getNodeString($this->tree[$id], $this->view[$out_level]['item']['start']);
        // output a tag of a content
        echo $this->$getNodeString($this->tree[$id], $this->view[$out_level]['content']);
        // [output a close tag of a item: if a node has no children]
        if ($this->tree[$id]['children'] == 0) {
            echo $this->view[$out_level]['item']['end'];
        }
        
        // The Tree Building & Outputting:
        do {
            
            if (!$this->getNextNode($id, $level)) {
                break;
            }
            
            // if a closure, stop the process
            if ($this->tree[$id]['added']) {
                throw new $this->ExceptionClass('FATAL ERROR: a closure in the tree, a node (id: ' . $id . ') was added to the tree and is adding again.');
            }
            
            // save a level of a node
            $this->tree[$id]['level'] = $level;
            
            // count tree levels
            if ($level > $this->levels) {
                $this->levels = $level;
            }
            
            // save a current node as a 'next' node in a previous node (the 'next' relation)
            $this->tree[$prev_id]['next'] = $id;
            
            // a current node becomes a previous node
            $prev_id = $id;
            
            // mark a node as added
            $this->tree[$id]['added'] = true;
                        
            // OUTPUT
            if ($level > $prev_level) {
                // create view settings for a new level
                $this->addLevelToView($level);
                
                // output a open tag of a block
                echo $this->$getNodeString($this->tree[$id], $this->view[$level]['block']['start']);
            } elseif (($lc = $level - $prev_level) < 0) {
                // the current node level is less, than a previous node level: put one or few end tags for a view block and a view item
                $lc = abs($lc);
                for ($i = 1; $i <= $lc; $i++) {
                    // output a close tag of a block
                    echo $this->view[$prev_level - $i]['block']['end'];
                    
                    // output a close tag of a item
                    echo $this->view[$prev_level - $i]['item']['end'];
                }
            }
            
            // output a open tag of a item
            echo $this->$getNodeString($this->tree[$id], $this->view[$level]['item']['start']);

            // output a tag of a content
            echo $this->$getNodeString($this->tree[$id], $this->view[$level]['content']);

            // [output a close tag of a item: if a item has no children]
            if ($this->tree[$id]['children'] == 0) {
                echo $this->view[$level]['item']['end'];
            }

            // save the level
            $prev_level = $level;
            
            // go to a 'next' node!...
        } while(1);

        // output a close tag of a block
        echo $this->view[$out_level]['block']['end'];

        // output a close tag of a wrapper
        echo $this->view['wrapper']['end'];
        //pm_end();
        
        // mark the tree as prepared to the output (has the 'next' relation)
        $this->is_relation_next_built = true;
    }
}
