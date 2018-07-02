<?php
/**
 * Arrays Tree Class
 *
 * This file contains Tree class which is using for building and outputting 
 * relational two-dimensional arrays (calling trees).
 *
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\D2\Tree\AdjacencyList;

use Arrays\D2\Tree\AdjacencyList\Option;

/**
 * The Tree class implements helpful methods for building and outputting two-dimensional 
 * arrays (calling trees).
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
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
     * Id of first found a top node. Initialized by getFirstTopId method.
     * Here a sorting of a $source array has value, because output will start
     * from a first found top node.
     * 
     * @access private
     * @var mixed
     */
    private $first_top_id;
        
    /**
     * Total amount of tree levels. Computes while a tree is building.
     * 
     * @access private
     * @var int
     */
    private $levels = 0;
    
    
    /** Flags */
        
    /**
     * Flag used to informing that a base for a new tree was built.
     * 
     * Required.
     * @access private
     * @var bool
     */
    private $is_base = false;
    
    /**
     * Flag used to informing that relations 'child' and 'next sibling' was setted.
     * 
     * Required.
     * @access private
     * @var bool
     */
    private $is_relation_child_sibling = false;
    
    /**
     * Flag used to informing that a tree was built (relation 'next' was setted).
     * 
     * Required.
     * @access private
     * @var bool
     */
    private $is_relation_next = false;
        
    
    /** Exceptions */
    
    /**
     * The default exception class.
     */
    private $defaultExceptionClass = '\Arrays\Exception\ArraysException';
    
    /**
     * A custom exception class name, which is used for error handling.
     * 
     * @access private
     * @var string
     */
    private $exceptionClass;
    
    
    
    /**
     * Initializes the tree properties (like a source array, index column, parent
     * column, etc.). Sets default values for view.
     * 
     * @param array     $source         A source raw tree array. It is base for building a relational tree.
     * @param mixed     $src_index      Values for index in a new tree array.
     * @param mixed     $src_parent     A column name (or index) which contains values of parent nodes.
     * @param mixed     $top_ident      A value to detect a top node.
     */
    public function __construct(array &$source, $src_index, $src_parent, $top_ident = '0', $options = 0)
    {
        // PHP Version, min 7.1.1
        if (version_compare(PHP_VERSION, '7.1.1') == -1) {
            echo '\'' . __CLASS__ . '\' class requires PHP version at least 7.1.1. Your current PHP version is ' . phpversion();
            exit;
        }
        
        // error handling ( Note: setSource method uses getExceptionClass method which uses $exceptionClass property)
        $this->exceptionClass = $this->defaultExceptionClass;
                
        // save source
        $this->setSource($source, $src_index, $src_parent, null, $top_ident);
        
        // options
        if ($options != 0)
            $this->options = new Option($this, $options);
    }
    public function __get($name)
    {
        if ($name == 'options') {
            $this->options = new Option($this, 0);
            return $this->options;
        }
    }
        
    /**
     * Wrapper of 'source' method.
     */
    public function getSource()
    {
        return $this->source;
    }
    
    /**
     * Sets an array as the source of the tree.
     * 
     * @param array     $source         A source raw tree array. It is base for building a relational tree.
     * @param mixed     $src_index      Values for index in a new tree array.
     * @param mixed     $src_parent     A column name (or index) which contains values of parent nodes.
     * @param mixed     $top_ident      A value to detect a top node.
     */
    public function setSource(array &$source = null, $src_index = null, $src_parent = null, $src_next = null, $top_ident = '0')
    {
        // Copy data from previous tree, if it exists
        if (empty($src_index) && ! empty($this->src_index)) {
            $src_index = $this->src_index;
        }
        if (empty($src_parent) && ! empty($this->src_parent)) {
            $src_parent = $this->src_parent;
        }

        // #1. verify and assing a new source array:
        $this->verifySource($source, $src_index, $src_parent);
        $this->src_index = $src_index;
        $this->src_parent = $src_parent;
        $this->src_next = $src_next;
        
        // #2. release flags:
        $this->top_id = null;
        $this->is_base = false;
        $this->is_relation_child_sibling = false;
        $this->is_relation_next = false;

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
    
    /**
     * 
     */
    private function verifySource(array &$source, $src_index, $src_parent, $src_next = null) : bool
    {
        // make sure the source array is not empty
        if (! is_array($source) || (count($source) == 0)) {
            throw new $this->exceptionClass('The source array is wrong (is empty or is not array at all).');
        }

        // make sure that 'index' and 'parent' columns exist in the source array
        foreach ($source as &$first_row) {
            // make sure that the 'index' column exists in source array
            if (! array_key_exists($src_index, $first_row)) { // searches $key in first $row-array of source $array
                throw new $this->exceptionClass('The source array has no the \'index\' column \''.$src_index.'\'.');
            }
          
            // make sure that the 'parent' column exists in source array
            if (! array_key_exists($src_parent, $first_row)) { // searches $key in first $row-array of source $array
                throw new $this->exceptionClass('The source array has no \'parent\' column \'' . $src_parent . '\'.');
            }
          
            // make sure that the 'next' column exists in source array
            if ($src_next != null) {
                if (! array_key_exists($src_next, $first_row)) { // searches $key in first $row-array of source $array
                    throw new $this->exceptionClass('The source array has no the \'next\' column \''.$src_next.'\'.');
                }
            }
            
            // leave loop
            break;
        }
        
        return true;
    }
    
    /**
     * 
     */
    public function getSrcParent()
    {
        return $this->src_parent;
    }
    
    /**
     * 
     */
    public function getTopIdent()
    {
        return $this->top_ident;
    }
    
    /**
     * 
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * 
     */
    private function getOptionsArray() : array
    {
        $opts = $this->options;
               
        return [
            $opts->get(Option::COUNT_DESCENDANTS),
            $opts->get(Option::NUMBERING),
            $opts->get(Option::DEBUG_MODE),
        ];
    }
    
    /**
     * 
     */
    public function getExceptionClass()
    {
        return $this->exceptionClass;
    }
    
    /**
     * 
     */
    public function setExceptionClass($exceptionClass)
    {
        $this->exceptionClass = $exceptionClass;
    }
    
    /**
     * Returns a value of the 'id' field of a first top node.
     * 
     * @return mixed Value of 'id' field of a first top node.
     */
    private function getFirstTopId()
    {
        // seek a first top node, if property is empty
        if (! isset($this->first_top_id)) {
            foreach ($this->tree as &$node) {
                if ($node[$this->src_parent] === $this->top_ident) {
                    $this->first_top_id = $node[$this->src_index];
                    break;
                }
            }
            
            // any top node isn't found: no sense to continue the tree building
            if (! isset($this->first_top_id)) {
                throw new $this->exceptionClass('Any top node isn\'t found (maybe a types mismatch).');
            }
        }
        
        return $this->first_top_id;
    }
       
    /**
     * 
     */
    private function createNode(array &$tree, array &$src_node, &$src_index, &$is_descendants, &$is_numbering)
    {
        // the value of 'src_index' column of the source array became an index value of the new tree array
        $i = $src_node[$src_index];             // note at now $i has the "string" type,
        $tree[$i] = &$src_node;                 // but now $i, like an array index, has "int" type: PHP automatically converts string numbers to its integer values,
                                                // that's why expression like '$this->tree[$prevtop_id]' or more explicitly '$this->tree["1"]' returns an array element, but not a PHP E_Notice.
        // relations properties
        $tree[$i]['firstChild'] = null;         // an id/index of a first child
        $tree[$i]['lastChild'] = null;          // an id/index of a last child
        $tree[$i]['nextSibl'] = null;           // an id/index of a next sibling
        $tree[$i]['children'] = 0;              // children total
        $tree[$i]['level'] = 0;                 // a node's level
        $tree[$i]['next'] = null;               // an id/index of a next outputting node
        $tree[$i]['loop'] = false;              // is used in createRelationChildSibling()
        $tree[$i]['added'] = null;              // is used to determine a closure in the tree
        $tree[$i]['upLoop'] = null;             // is used to determine a closure in the tree within 'parent' attribute while 'up loop' in 'relation_next' method

        // additional properties
        if ($is_descendants) {
            $tree[$i]['descendants'] = 0;       // descendants total
        }
        if ($is_numbering) {
            $tree[$i]['level'] = 0;             // the node level
            $tree[$i]['childNumber'] = 0;       // an order in a children array. It will be the last number in 'numbering' property
            $tree[$i]['numbering'] = null;      // a numbering expression: '1.1', '1.2.1'
        }
    }
    
    /**
     * 
     */
    public function createBase() : self
    {
        // if a tree already built
        if ($this->is_base) {
            return $this;
        }
        
        // a new tree array
        $tree = [];
        $src_index = $this->src_index;
        
        // options
        list($is_descendants, $is_numbering, ) = $this->getOptionsArray();
                
        // write all rows (nodes) of the source array to a new tree array
        foreach ($this->source as &$src_node) {
            $this->createNode($tree, $src_node, $src_index, $is_descendants, $is_numbering);
        }
        
        // save new tree
        $this->tree = &$tree;
        
        // mark the tree as prepared for a relations building
        $this->is_base = true;
        
        // chaining
        return $this;
    }
        
    /**
     * Sets 2 types of relations between nodes:
     * - 'who is a next sibling node'
     * - 'who is a child node'
     * 
     * @return void
     */
    public function createRelationChildSibling() : self
    {
        // if the relation 'child' and 'sibling' already built
        if ($this->is_relation_child_sibling) {
            return $this;
        }
                
        // verify: has a tree a base to build relations?
        if (! $this->is_base) {
            $this->createBase();
        }
        
        
        // options
        list($is_descendants, $is_numbering, $is_debug_mode) = $this->getOptionsArray();
        // descendants counting
        if ($is_descendants) {
            $descendant = new Descendant($this, $this->tree, $is_debug_mode);
        }
        
        
        // previous top node id
        $prevtop_id = null;
        $parent_node;
        
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
                    if ($is_numbering) {
                        $node['childNumber'] = $this->tree[$prevtop_id]['childNumber'] + 1;
                    }
                } else {
                    // the current node is the first top node
                    
                    
                    // numbering (preparing, initial data)
                    if ($is_numbering) {
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
            if (! isset($this->tree[$parent_id])) {
                throw new $this->exceptionClass( 'Node (id: ' . $id . ') has no parent (id: ' . $parent_id . ').' );
            }
            
            // is the current node a first child?
            $parent_node = &$this->tree[$parent_id];
            if (is_null($parent_node['firstChild'])) {
                // the current node is the first child
                // save node as a first child: relation 'child'
                $parent_node['firstChild'] = $id;
                // save the node as a last child too, if a node has siblings: for relaion 'sibling'
                $parent_node['lastChild'] = $id;
                
                
                // mumbering (preparing, initial data)
                if ($is_numbering) {
                    $node['childNumber'] = 1;
                }
            } else {
                // the current node isn't first child, then it is some sibling node
                // save the last child to tmp variable
                $last_child = $parent_node['lastChild'];
                // save a current node as a last child: for relaion 'sibling'
                $parent_node['lastChild'] = $id;
                // save a current node as a next sibling node: relaion 'sibling'
                $this->tree[$last_child]['nextSibl'] = $id;
                
                
                // numbering (preparing, initial data)
                if ($is_numbering) {
                    $node['childNumber'] = $this->tree[$last_child]['childNumber'] + 1;
                }
            }
            
            
            // children counting
            $parent_node['children']++;
            
            
            // descendant counting
            if ($is_descendants) {
                $descendant->count($id, $parent_id);
            }
        }
        
        // mark the tree as prepared to build the 'next' relation (has 'child' and 'sibling' relations)
        $this->is_relation_child_sibling = true;
        
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
     * @return Arrays\Tree Returns a Tree object for support chaining.
     */
    public function createRelationNext() : self
    {
        // if the relation 'next' already built
        if ($this->is_relation_next) {
            return $this;
        }

        // create tree base (additional fields)
        if (! $this->is_base) {
            $this->createBase();
        }
        
        // create relation 'child & sibling'
        if (! $this->is_relation_child_sibling) {
            $this->createRelationChildSibling();
        }
        
        
        
        // options
        list($is_descendants, $is_numbering, ) = $this->getOptionsArray();
        if ($is_numbering) {
            $nb = $this->options->numbering;
        }
        
        // current node level
        $level = 0;
        // first node
        $first_id = $this->getFirstTopId();
        $first_node = &$this->tree[$first_id];
        // current node
        $id = $first_id;
        $node = &$first_node;
        // previous node
        $prev_id = $first_id;
        //$prev_node = &$first_node; // useless
        // up node (parent node)
        $up_node;
        
        
        // first top node
        $first_node['level'] = $level;
        $first_node['added'] = true;
        // additional
        if ($is_numbering) {
            $numbering = $nb->getNumbering($first_node['level'], $first_node['childNumber']);
            $first_node['numbering'] = $numbering;
        }
        

        
        // Tree Building
        do {
            // Seek a next node:
            if (! $this->getNextNode($id, $level)) {
                break;
            } else {
                $node = &$this->tree[$id];
            }
            
            
            // Node properties / operations (it's new found next node)
            
            // protection: determine closure (and stop process if found)
            if ($node['added']) {
                $message = 'Closure in the tree, the node (id: ' . $id . ') was added to the tree and is adding again.';
                throw new $this->exceptionClass($message);
            }
            
            // mark the node as added
            $node['added'] = true;
            // save level
            $node['level'] = $level;
                        
            // a total count of a tree levels
            if ($level > $this->levels) {
                $this->levels = $level;
            }
            
            // additional
            // numbering
            if ($is_numbering) {
                // the full numbering symbol of the parent node (something like: '1', '1.1.3', 'A.1', 'A.B', etc.)
                $parent_id = $node[$this->src_parent];
                $parentNumbering = $this->tree[$parent_id]['numbering'];
                $node['numbering'] = $nb->getNumbering($node['level'], $node['childNumber'], $parentNumbering);
            }
            
            
            // Algorithm: prev_node, node
            
            // save a current node as 'next' in a previous node ('next' relation)
            $this->tree[$prev_id]['next'] = $id;
            // the current node becomes a previous node
            $prev_id = $id;
                        
            // go to a 'next' node!...
            
        } while(1);
        
        // mark the tree as prepared to the output (has the 'next' relation)
        $this->is_relation_next = true;
        
        // chaining
        return $this;
    }
    
    /**
     * 
     */
    private function getNextNode(&$id, &$level) : bool
    {
        $node = &$this->tree[$id];
        if (isset($node['firstChild'])) {
            $id = $node['firstChild'];
            $level++;
        } // if a node is sibling
        elseif (isset($node['nextSibl'])) {
            $id = $node['nextSibl'];
            //$level++; // Level does not changes
        }
        else { // the cureent node has no any child or sibling
            // the node is a single top or a last top - has no any children or siblings
            if ($node[$this->src_parent] === $this->top_ident) {
                return false;
            }

            // go up to check its parent or ancestors
            $node['upLoop'] = true; // protection: to determine a closure with the 'parent' attribute and to stop the process
            
            $up_id = $node[$this->src_parent];
            $up_node = &$this->tree[$up_id];
            
            $level--;
            
            do {
                // protection: to determine a closure with the 'parent' attribute and to stop the process
                if ($up_node['upLoop']) {
                    $msg = 'Closure in the tree, the node (id: ' . $id . ') was used within the \'Up-loop\' and is using again.';
                    throw new $this->exceptionClass($msg);
                } else {
                    $up_node['upLoop'] = true;
                }

                // seek sibling
                if (isset($up_node['nextSibl'])) {
                    // found sibling! 
                    $id = $up_node['nextSibl']; 
                    break; // 'breake' is necessary
                } else {
                    if ($up_node[$this->src_parent] === $this->top_ident) {
                        // it is a top node and it has no a sibling
                        return false; // the tree is built!
                    } else {
                        // the node has no a sibling and is not a top node, therefore get its parent and continue search
                        $up_id = $up_node[$this->src_parent];
                        $up_node = &$this->tree[$up_id];
                        $level--;
                    }
                }
            } while(1);
        }
               
        return true;
    }
    
    /**
     * Returns the current raw tree.
     * 
     * @return null|array
     */
    public function get()
    {
        return $this->tree;
    }
    
    /**
     * Returns the current raw tree.
     * 
     * @return null|array
     */
    public function getCurrent()
    {
        return $this->get();
    }
    
    /**
     * Creates and returns a new sorted array (based on relation 'next' of the tree array).
     * This new returning array is a common 2D array and can be outputted by a simple 'foreach' loop.
     * 
     * Total loops (at source array):
     * 1. base for tree (base())
     * 2. set relations 'child' and 'next sibling' (createRelationChildSibling())
     * ?. seek top node (if it has middle or last place, it halb or full loop, but if $source is sorted it will first node)
     * 3. set relation 'next' (createRelationNext())
     * 4. forming new sorted array (get())
     * 
     * In most cases this method is used in output methods, which output 2D 
     * arrays in a form of a table.
     * 
     * @return array A new tree sorted array.
     */
    public function getSorted() : array 
    {
        // is exists the sorted tree?
        if (! empty($this->sorted)) {
            return $this->sorted;
        }
        
        // create tree base (additional fields)
        if (! $this->is_base) {
            $this->createBase();
        }
        
        // create relation 'child & sibling'
        if (! $this->is_relation_child_sibling) {
            $this->createRelationChildSibling();
        }
        
        // create relation 'next'
        if (! $this->is_relation_next) {
            $this->createRelationNext(); // at now the tree is built and can be outputted       
        }
        
        // make a new sorded array from a relational tree array
        $id = $this->getFirstTopId();
        
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
        $this->is_base = true;
        $this->is_relation_child_sibling = true;
        $this->is_relation_next = true;
        
        // chaining
        return $this;
    }
    
    /**
     * Outputs a relational tree (with do-while loop).
     * 
     * Total loops/iterates (at source array):
     * 1. base for tree (base())
     * 2. set relations 'child' and 'next sibling' (createRelationChildSibling())
     * ?. seek top node (if it has middle or last place, it halb or full loop, but if $source is sorted it will first node)
     * 3. set relation 'next' (createRelationNext())
     * 4. ouput loop
     * 
     * Uses only the 'next' field and user custom view settings.
     *  
     * @param array $cview  The array with custom user view settings.
     * @return void
     */
    public function output()
    {
        // create tree base (additional fields)
        if (! $this->is_base) {
            $this->createBase();
        }
        
        // create relation 'child & sibling'
        if (! $this->is_relation_child_sibling) {
            $this->createRelationChildSibling();
        }
        
        // create relation 'next'
        if (! $this->is_relation_next) {
            $this->createRelationNext(); // at now the tree is built and can be outputted       
        }
        
        
        
        // the first top node: id
        $first_id = $this->getFirstTopId();
        $id = $first_id;
        // the first top node: level
        $out_level = $this->tree[$first_id]['level'];
        $prev_level = $out_level;
        
        // the 'next' attribute
        $next_attr = $this->src_next == null ? 'next' : $this->src_next;
        
        // view
        $view = $this->options->view;
        // add new levels and fills them with default values
        $view->addLevels($this->levels);
        
        
        // OUTPUT
        
        // output a open tag of a wrapper
        echo $view->getWrapperStart($this->tree[$id]);
        
        // first node
        // output an open tag of block
        echo $view->getBlockStart($out_level, $this->tree[$first_id]);
        // output an open tag of item
        echo $view->getItemStart($out_level, $this->tree[$first_id]);
        // output a tag of a content
        echo $view->getContent($out_level, $this->tree[$first_id]);
        // [output a close tag of a item: if a node has no children]
        if ($this->tree[$id]['children'] == 0) {
            echo $view->getItemEnd($out_level, $this->tree[$first_id]);
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
                echo $view->getBlockStart($level, $this->tree[$id]);
            }
            
            if (($lc = $level - $prev_level) < 0) {
                // the current node level is less, than a previous node level: put one or few end tags for a view block and a view item
                $lc = abs($lc);
                for ($i = 1; $i <= $lc; $i++) {
                    // output a close tag of a block
                    echo $view->getBlockEnd($prev_level - $i, $this->tree[$id]); // here node_arr is ambiguous
                    
                    // output a close tag of a item
                    echo $view->getItemEnd($prev_level - $i, $this->tree[$id]);
                }
            }
            // output a open tag of a item
            echo $view->getItemStart($level, $this->tree[$id]);

            // output a tag of a content
            echo $view->getContent($level, $this->tree[$id]);

            // [output a close tag of a item: if a item has no children]
            if ($this->tree[$id]['children'] == 0) {
                echo $view->getItemEnd($level, $this->tree[$id]);
            }

            // save the level
            $prev_level = $level;
            // go to a 'next' node!...
        } while (1);
        
        // output a close tag of a block
        echo $view->getBlockEnd($out_level, $this->tree[$first_id]);
        
        // output a close tag of a wrapper
        echo $view->getWrapperEnd($this->tree[$first_id]);
    }
        
    /**
     * Builds a tree and parallely outputs it.
     * 
     * Gives a one big advantage: one less an iterate at a tree (if a tree has 
     * thousands nodes, the using this method can very increase the performance).
     * 
     * Total loops (at a tree array):
     * 1. build base for tree, base()
     * 2. set relations 'child' and 'next sibling', createRelationChildSibling()
     * ?. seek first top node
     * 3. set relation 'next' and output node
     * 
     * @param array $cview  An array with custom user settings of a view.
     * @return void
     */
    public function outputFast()
    {
        // create tree base (additional fields)
        if (! $this->is_base) {
            $this->createBase();
        }
        
        // create relation 'child & sibling'
        if (! $this->is_relation_child_sibling) {
            $this->createRelationChildSibling();
        }
        
        
        // options
        list($is_descendants, $is_numbering, ) = $this->getOptionsArray();
        if ($is_numbering) {
            $nb = $this->options->numbering;
        }


        // level values
        $level = 0; // current level
        $out_level = $level; // out level
        $prev_level = $level; // previous level

        // if of first top node
        $first_id = $this->getFirstTopId();
        // a current and previous node id
        $id = $first_id;
        $prev_id = $first_id;
        $first_node = &$this->tree[$id];
        $node = &$this->tree[$id];
        
        // first node
        $first_node['level'] = $level;
        $first_node['added'] = true; // mark a node as added to a tree, to help determine a closure
        if ($is_numbering) {
            $numbering = $nb->getNumbering($first_node['level'], $first_node['childNumber']);
            $first_node['numbering'] = $numbering;
        }
        
        // view
        $view = $this->options->view;
        
        
        
        
        
        // Output
    
        // output a open tag of a wrapper
        echo $view->getWrapperStart($first_node);
        
        // if view settings for the current level will not be create, below in the 'get_elem_string' method PHP will throw a fatal
        $view->addLevel($level);
        
        // first node
        // output a open tag of a block
        echo $view->getBlockStart($out_level, $first_node);
        // output a open tag of a item
        echo $view->getItemStart($out_level, $first_node);
        // output a tag of a content
        echo $view->getContent($out_level, $first_node);
        // [output a close tag of a item: if a node has no children]
        if ($node['children'] == 0) {
            echo $view->getItemEnd($out_level, $first_node); // $node is 
        }
        
        // The Tree Building & Outputting:
        do {
            
            if (! $this->getNextNode($id, $level)) {
                break;
            } else {
                $node = &$this->tree[$id];
            }
            
            
            // if a closure, stop the process
            if ($node['added']) {
                throw new $this->exceptionClass('Closure in the tree, a node (id: ' . $id . ') was added to the tree and is adding again.');
            }
            
            // save a level of a node
            $node['level'] = $level;
            
            // count tree levels
            if ($level > $this->levels) {
                $this->levels = $level;
            }
            
            // save a current node as a 'next' node in a previous node (the 'next' relation)
            $this->tree[$prev_id]['next'] = $id;
            
            // a current node becomes a previous node
            $prev_id = $id;
            
            // mark a node as added
            $node['added'] = true;
            // numbering
            if ($is_numbering) {
                // the full numbering symbol of the parent node (something like: '1', '1.1.3', 'A.1', 'A.B', etc.)
                $parent_id = $node[$this->src_parent];
                $parentNumbering = $this->tree[$parent_id]['numbering'];
                $node['numbering'] = $nb->getNumbering($node['level'], $node['childNumber'], $parentNumbering);
            }

            // OUTPUT
            if ($level > $prev_level) {
                // create view settings for a new level
                $view->addLevel($level);
                
                // output a open tag of a block
                echo $view->getBlockStart($level, $node);
            } elseif (($lc = $level - $prev_level) < 0) {
                // the current node level is less, than a previous node level: put one or few end tags for a view block and a view item
                $lc = abs($lc);
                for ($i = 1; $i <= $lc; $i++) {
                    // output a close tag of a block
                    echo $view->getBlockEnd($prev_level - $i, $node); // 
                    
                    // output a close tag of a item                    
                    echo $view->getItemEnd($prev_level - $i, $node); // 
                }
            }
            
            // output a open tag of a item
            echo $view->getItemStart($level, $node); // 

            // output a tag of a content
            echo $view->getContent($level, $node); // 

            // [output a close tag of a item: if a item has no children]
            if ($node['children'] == 0) {
                echo $view->getItemEnd($level, $node); // 
            }

            // save the level
            $prev_level = $level;
            
            // go to a 'next' node!...
        } while(1);

        // output a close tag of a block
        echo $view->getBlockEnd($out_level, $first_node);
        
        // output a close tag of a wrapper
        echo $view->getWrapperEnd($first_node);
        //pm_end();
        
        // mark the tree as prepared to the output (has the 'next' relation)
        $this->is_relation_next = true;
    }
}
