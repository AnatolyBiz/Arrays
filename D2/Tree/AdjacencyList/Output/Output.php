<?php
/**
 * Arrays Output Class
 *
 * This file contains Output class which is used for output relational two-dimensional
 * arrays (calling trees).
 *
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\D2\Tree\AdjacencyList\Output;

use Arrays\D2\Tree\AdjacencyList\Tree;
use Arrays\D2\Tree\View\View;

/**
 * The Output class implements helpful methods for output two-dimensional arrays (calling trees).
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class Output
{
    /**
     * A Tree object.
     */
    private $alTree;
    
    /**
     * A tree array that contains a tree.
     * 
     * @access private
     * @var array
     */
    private $tree = [];
    
    /**
     * A view object.
     */
    private $alView;
    
    /**
     * A view array (view template).
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
    private $raw_replacing_pattern;
    
    /**
     * Contains pattern to replace column expression with computed value.
     * Initializes in apply_custom_view method with getReplacingPattern method.
     * 
     * @access private
     * @var string
     */
    private $replacing_pattern;
    
    /**
     * Method name for handle node template.
     * Possible values is: 'common', 'replacer', 'replacerInserter'.
     */
    private $handler = 'Column';

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
     * parameters: #1. $node_arr, a current row, #2. $result, to write the result of the computing.
     * 
     * @access private
     * @var array
     */
    private $replacer = [];
    
    /**
     * 
     */
    private $is_buffered = false;

    /**
     * 
     */
    private $output;
    
    /**
     * An array of objects to inserting of node/nodes while an output.
     * 
     * An every object has a callback. Every callback will call, when a node will output. A callback receives 2 
     * parameters: #1. $node_arr, a current row, #2. $result, to write the result of the computing.
     * 
     * @access private
     * @var array
     */
    //private $inserter = [];

    /**
     * An exception class name, which is used for error handling.
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
    public function __construct(Tree $alTree, View $alView)
    {
        // Tree object
        $this->alTree = $alTree;
        
        // View object
        $this->alView = $alView;
        
        // get config
        $cnf = require __DIR__ . '/../../../../Config/d2.tree.output.config.php';
        // get raw replacing pattern
        $this->raw_replacing_pattern = $cnf['replacing_pattern'];
        
        // Exception class
        $this->exceptionClass = $alTree->getExceptionClass();
    }
    
    /**
     * 
     */
    public function assignTo(string $prop, &$value)
    {
        $this->{$prop} = &$value;
    }
    
    /**
     * Gets or sets buffering mode.
     */
    public function isBuffered($val = null)
    {
        if (is_null($val)) {
            return $this->is_buffered;
        } else {
            $this->is_buffered = $val;
        }
    }
    
    /**
     * Saves buffered output to passed variable.
     */
    public function saveTo(string &$output)
    {
        $output = $this->output;
    }
    
    /**
     * Returns buffered output.
     */
    public function get()
    {
        return $this->output;
    }
    
    /**
     * 
     */
    private function preparePattern()
    {
        list($sp_start, $sp_end, ) = $this->alView->getSplitters();
        
        // create pattern for replace a column expression with a column value
        $this->replacing_pattern = $this->getReplacingPattern($sp_start, $sp_end);
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
        return '/'.$start.'('.$this->raw_replacing_pattern.')'.$end.'/U';
    }
    
    /**
     * Adds a new replacer (a callable).
     * 
     * Callable signature: 
     * function(&$id, &$result) : bool {}.
     * 
     * @param string    $key        A replacer key.
     * @param Replacer  $or_obj     A Replacer object with property, which contains an anonymous function (with signature identical to described above).
     * @return void
     */
    public function addReplacer(string $key, Replacer $or_obj) : void
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
                throw new $this->exceptionClass('The replacer method has wrong signature: is passed '.$num_of_rparams.' required parameters, but need - 2.');
            }
            
            // parameters types
            $error_message = 'A parameter of the replacer method is wrong (it has not an allowed name or is passed not by reference)).';
            $params = $refl->getParameters();
            $pi = -1;
            // $node_arr parameter
            $prm = $params[++$pi];
            if (!($prm->getName() === 'node_arr' && $prm->isPassedByReference())) {
                throw new $this->exceptionClass($error_message);
            }
            // $result parameter
            $prm = $params[++$pi];
            if (!($prm->getName() === 'result' && $prm->isPassedByReference())) {
                throw new $this->exceptionClass($error_message);
            }
            
            // all parameters are valid: add the new callback to the replacer array
            $this->replacer[$key] = $or_obj;
            
            // stop edding
            return;
        }
        
        // glaring mistake: $replacer is not a function
        throw new $this->exceptionClass('The passed replacer is not a function.');
    }
    
    /**
     * 
     */
    public function setHandler($name)
    {
        $this->handler = $name;
    }
    
    public function echoWrapperStart($node_arr, array &$view)
    {
        echo $this->getItemString( $node_arr, $view['wrapper']['start'] );
    }
    public function echoWrapperEnd($node_arr, array &$view)
    {
        echo $this->getItemString($node_arr, $view['wrapper']['end']);
    }
    public function echoBlockStart($level, $node_arr, array &$view)
    {
        echo $this->getItemString($node_arr, $view[$level]['block']['start']);
    }
    public function echoBlockEnd($level, $node_arr, array &$view)
    {
        echo $this->getItemString($node_arr, $view[$level]['block']['end']);
    }
    public function echoItemStart($level, $node_arr, array &$view)
    {
        echo $this->getItemString($node_arr, $view[$level]['item']['start']);
    }
    public function echoItemEnd($level, $node_arr, array &$view)
    {
        echo $this->getItemString($node_arr, $view[$level]['item']['end']);
    }
    public function echoContent($level, $node_arr, array &$view)
    {
        echo $this->getItemString($node_arr, $view[$level]['content']);
    }
    
    /**
     * Routes to appropriate output handler method, with replaces an item expression
     * with a computed value. Returns computed value.
     * 
     * @param array $node_arr       An array of the current node.
     * @param string $elem_expr     An element expression, ex.: '%class-aitem%', 'link', etc.
     * @return string               Returns computed element string. For {{%class-aitem%}} it will be 'class="active"' etc.
     */
    private function getItemString(array &$node_arr, string &$item_expression) : string
    {
        return $this->{'handler'.$this->handler}($item_expression, $node_arr);
    }
    
    /**
     * Replaces an item expression with a computed value.
     * 
     * Example. Something like:
     * <a href="{{link}}" data-id="{{id}}">{{title}}</a>
     * will be replaced and the result in html maybe next:
     * <a href="/node/desc" data-id="5">Node 5</a>
     * 
     * @param array $node_arr       An array of the current node.
     * @param string $elem_expr     An item expression, ex.: 'link', 'id' or another column name etc.
     * @return string               Returns computed element string.
     */
    private function handlerColumn(string &$item_expression, array &$node_arr) : string
    {
        // use 'preg_replace' with callback
        $item_string = preg_replace_callback(           // MAIN FUNCTION
            $this->replacing_pattern,                   // usually it is: "/\{\{([a-zA-Z%0-9][a-zA-Z0-9%]*)\}\}/U" or more clearly "/{{([a-zA-Z%0-9][a-zA-Z0-9%]*)}}/U". Examples, "{{title}}", "{{description}}", "{{%class-active%}}", "{{%active%}}", etc.
            function ($matches) use (&$node_arr) {      // callback: will run for every occurence pattern {{...}} in $elem_expression
                // pattern matches a replacing part and passes matches to this function
                $replacing_part = &$matches[1];         // here the $replacing_part will be: 'title', 'description' or another column name etc.
                return $node_arr[$replacing_part];
            },
            $item_expression
        );
        
        return $item_string;
    }
    
    /**
     * An analog of the 'outputHandlerCommon' with only difference in using of 
     * replacers for a computing of item's expressions. Was created for
     * a boosting of the output with 'getNodeString', if the 'replacer' array is
     * empty.
     * 
     * @param array $node_arr       An array of the current node.
     * @param string $elem_expr An element expression, ex.: '%class-aitem%', 'link', etc.
     * @return string           Returns computed element string. For {{%class-aitem%}} it will be 'class="active"' etc.
     */
    private function handlerReplacer(string &$item_expression, array &$node_arr) : string
    {
        // use 'preg_replace' with callback
        $item_string = preg_replace_callback(           // MAIN FUNCTION
            $this->replacing_pattern,                   // usually it is: "/\{\{([a-zA-Z%0-9][a-zA-Z0-9%]*)\}\}/U" or more clearly "/{{([a-zA-Z%0-9][a-zA-Z0-9%]*)}}/U". Examples, "{{title}}", "{{description}}", "{{%class-active%}}", "{{%active%}}", etc.
            function ($matches) use (&$node_arr) {      // callback: will run for every occurence pattern {{...}} in $elem_expression
                // pattern matches a replacing part and passes matches to this function
                $replacing_part = &$matches[1];         // here the $replacing_part will be: 'title', 'description', '%class-active%', '%active%', etc.
                // 1) a column expression. NOTE: if write: 'isset($this->tree[$id][$expr_content])' a result maybe FALSE, because value in cell can be NULL (but it is!), therefore is wrong to use this construction
                if (array_key_exists($replacing_part, $node_arr)) {
                    return $node_arr[$replacing_part];
                }
                // 2) another expression, like active menu, title, some computions, etc.
                $replacer_obj = $this->replacer[$replacing_part];
                if (! is_null($replacer_obj)) {
                    $result = '';
                    $replacer = $replacer_obj->replacer;
                    if (true === $replacer($node_arr, $result)) { // array keys is unique, but something while a replacing can be wrong
                        return $result;
                    }
                }
                // 3) nothing is matched
                return '';
            },
            $item_expression
        );
        
        return $item_string;
    }
    
    /**
     * Adds plenty of nodes to the output.
     */
    private function handlerInserter()
    {
        
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
    public function write()
    {
        // Tree object
        $alTree = $this->alTree;
        // build tree
        if (! $alTree->isBuild()) {
            $alTree->build();
        }
        // get reference of tree array
        $alTree->assign('tree', $this, 'tree'); // I wanna to get a ref of the tree array (instead to get its copy)
        // tree array
        $tree = &$this->tree;
        // tree node array
        $node = [];
        
        // View
        $alView = $this->alView;
        // add new levels and fills them with default values
        $alView->addLevels($alTree->getLevels());
        // get reference of view array
        $alView->assign('view', $this, 'view');
        // view array
        $view = &$this->view;
        
        // Replacing pattern
        $this->preparePattern();
        
        // The 'next' attribute
        $next_attr = $alTree->getSrcNext();
        
        // First node
        $first_node = [];
        $first_id = $alTree->getFirstTopId();
        $first_node = &$tree[$first_id];
        $out_level = $first_node['level'];
        
        // Current node
        $id = $first_id;
        $node = &$first_node;
        $prev_level = $out_level;
        
        // Buffer
        if ($this->is_buffered) {
            ob_end_clean();
            ob_start();
        }
        
        
        // OUTPUT
        
        // output a open tag of a wrapper
        $this->echoWrapperStart($node, $view);
        
        // first node
        // output an open tag of block
        $this->echoBlockStart($out_level, $node, $view);
        // output an open tag of item
        $this->echoItemStart($out_level, $node, $view);
        // output a tag of a content
        $this->echoContent($out_level, $node, $view);
        // [output a close tag of a item: if a node has no children]
        if ($node['children'] == 0) {
            $this->echoItemEnd($out_level, $node, $view);
        }
        
        do {
            // get a next node
            $id = $node[$next_attr];
            if (is_null($id)) {
                break; // the tree is outputted
            } else {
                $node = &$tree[$id];
            }
            
            // get a level of the current node
            $level = $node['level'];
            
            if ($level > $prev_level) {
                // the current node level is bigger, than a previous node level: output a open tag of a block
                $this->echoBlockStart($level, $node, $view);
            }
            
            if (($lc = $level - $prev_level) < 0) {
                // the current node level is less, than a previous node level: put one or few end tags for a view block and a view item
                $lc = abs($lc);
                for ($i = 1; $i <= $lc; $i++) {
                    // output a close tag of a block
                    $this->echoBlockEnd($prev_level - $i, $node, $view); // here node_arr is ambiguous
                    
                    // output a close tag of a item
                    $this->echoItemEnd($prev_level - $i, $node, $view);
                }
            }
            // output a open tag of a item
            $this->echoItemStart($level, $node, $view);

            // output a tag of a content
            $this->echoContent($level, $node, $view);

            // [output a close tag of a item: if a item has no children]
            if ($node['children'] == 0) {
                $this->echoItemEnd($level, $node, $view);
            }

            // save the level
            $prev_level = $level;
            // go to a 'next' node!...
        } while (1);
        
        // output a close tag of a block
        $this->echoBlockEnd($out_level, $first_node, $view);
        
        // output a close tag of a wrapper
        $this->echoWrapperEnd($first_node, $view);
        
        
        // Buffer
        if ($this->is_buffered) {
            $this->output = ob_get_contents();
            ob_end_clean();
        }
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
    public function writeFast()
    {
        // Tree object
        $alTree = $this->alTree;
        // build relations
        if (! $alTree->isBase()) {
            $alTree->createBase();
        }
        if (! $alTree->isRelationChildSibling()) {
            $alTree->createRelationChildSibling();
        }
        // get reference of tree array
        $alTree->assign('tree', $this, 'tree'); // I wanna to get a ref of the tree array (instead to get its copy)
        // tree array
        $tree = &$this->tree;
        // tree node array
        $node = [];
        
        // View
        $alView = $this->alView;
        // get reference of view array
        $alView->assign('view', $this, 'view');
        // view array
        $view = &$this->view;
        
        // Replacing pattern
        $this->preparePattern();
                
        // Options
        list($is_descendants, $is_numbering, ) = $alTree->getOptionsArray();
        if ($is_numbering) {
            $nb = $alTree->options->numbering;
            $src_parent = $alTree->getSrcParent();
        }

        // Level
        $out_level = 0;         // wrapper level
        $level = $out_level;    // current level
        $prev_level = $level;   // previous level
        
        // First node
        $first_node = [];
        $first_id = $alTree->getFirstTopId();
        $first_node = &$tree[$first_id];
        $first_node['level'] = $level;
        $first_node['added'] = true; // mark a node as added to a tree, to help determine a closure
        if ($is_numbering) {
            $first_node['numbering'] = $nb->getNumbering( $first_node['level'], $first_node['childNumber'] );
        }
        
        // Current node
        $id = $first_id;
        $node = &$first_node;
        $prev_id = $id;
        
        // Buffer
        if ($this->is_buffered) {
            ob_end_clean();
            ob_start();
        }
        
        
        
        // OUTPUT
    
        // output a open tag of a wrapper
        $this->echoWrapperStart($first_node, $view);
        
        // if view settings for the current level will not be create, below in the 'get_elem_string' method PHP will throw a fatal
        $alView->addLevel($level);
        
        // first node
        // output a open tag of a block
        $this->echoBlockStart($out_level, $first_node, $view);
        // output a open tag of a item
        $this->echoItemStart($out_level, $first_node, $view);
        // output a tag of a content
        $this->echoContent($out_level, $first_node, $view);
        // [output a close tag of a item: if a node has no children]
        if ($node['children'] == 0) {
            $this->echoItemEnd($out_level, $first_node, $view); // $node is 
        }
        
        // The Tree Building & Outputting:
        do {
            
            if (! $alTree->getNextNode($id, $level)) {
                break;
            } else {
                $node = &$tree[$id];
            }
            
            
            // if a closure, stop the process
            if ($node['added']) {
                $message = 'Closure in the tree, a node (id: ' . $id . ') was added to the tree and is adding again.';
                throw new $this->exceptionClass($message);
            }
            
            // save a level of a node
            $node['level'] = $level;
            // mark a node as added
            $node['added'] = true;
            // numbering
            if ($is_numbering) {
                // the full numbering symbol of the parent node (something like: '1', '1.1.3', 'A.1', 'A.B', etc.)
                $parent_id = $node[$src_parent];
                $parentNumbering = $this->tree[$parent_id]['numbering'];
                $node['numbering'] = $nb->getNumbering($node['level'], $node['childNumber'], $parentNumbering);
            }
            
            // count tree levels
            if ($level > $alTree->getLevels()) {
                $alTree->setLevels($level);
            }
            
            // save a current node as a 'next' node in a previous node (the 'next' relation)
            $tree[$prev_id]['next'] = $id;
            // a current node becomes a previous node
            $prev_id = $id;
            
            

            // OUTPUT
            if ($level > $prev_level) {
                // create view settings for a new level
                $alView->addLevel($level);
                
                // output a open tag of a block
                $this->echoBlockStart($level, $node, $view);
            } elseif (($lc = $level - $prev_level) < 0) {
                // the current node level is less, than a previous node level: put one or few end tags for a view block and a view item
                $lc = abs($lc);
                for ($i = 1; $i <= $lc; $i++) {
                    // output a close tag of a block
                    $this->echoBlockEnd($prev_level - $i, $node, $view); //

                    // output a close tag of a item
                    $this->echoItemEnd($prev_level - $i, $node, $view); // 
                }
            }
            
            // output open tag of item
            $this->echoItemStart($level, $node, $view); // 

            // output tag of a content
            $this->echoContent($level, $node, $view); // 

            // [output close tag of item: if item has no children]
            if ($node['children'] == 0) {
                $this->echoItemEnd($level, $node, $view); // 
            }

            // save the level
            $prev_level = $level;
            
            // go to a 'next' node!...
        } while(1);

        // output a close tag of a block
        $this->echoBlockEnd($out_level, $first_node, $view);
        
        // output a close tag of a wrapper
        $this->echoWrapperEnd($first_node, $view);
        
        // Buffer
        if ($this->is_buffered) {
            $this->output = ob_get_contents();
            ob_end_clean();
        }
        

        // mark the tree as prepared to the output (has the 'next' relation)
        $alTree->setRelationNext(true);
    }
}
