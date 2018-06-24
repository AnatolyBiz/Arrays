<?php
/**
 * 
 */
namespace Arrays\D2\Tree\View;

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
     * 
     */
    private $levels = 0;
    
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
    private $output_handler = 'Column';

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
     * An array of objects to inserting of node/nodes while an output.
     * 
     * An every object has a callback. Every callback will call, when a node will output. A callback receives 2 
     * parameters: #1. $node_arr, a current row, #2. $result, to write the result of the computing.
     * 
     * @access private
     * @var array
     */
    private $inserter = [];
    
    
    
    public function __construct($src_index, $src_parent)
    {
        // get config
        $cnf = require __DIR__ . '/../../../Config/d2.tree.view.config.php';
        
        // get raw replacing pattern
        $this->raw_replacing_pattern = $cnf['replacing_pattern'];
        
        // get default setting for view
        $view = &$cnf['view'];
                
        // apply settings
        $this->add($view);
        
    }
    
    /**
     * 
     */
    public function setLevels($levels)
    {
        $this->levels = $levels;
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
        $this->view = &$view;
    }
        
    /**
     * Applies a custom view settings and default setting.
     * 
     * @param   array   $cview  An array with a custom view settings.
     * @return  void
     */
    public function add(array &$cview) : void
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
        if (isset($cview['wrapper']) && is_string($cview['wrapper'])) {
            list($this->view['wrapper']['start'], $this->view['wrapper']['end']) = 
                explode($splitter_start.$splitter_end, $cview['wrapper']);
        } else {
            list($this->view['wrapper']['start'], $this->view['wrapper']['end']) = 
                explode($splitter_start.$splitter_end, $default_wrapper);
        }

        // default level
        // block
        $this->view['level']['block'] = [];
        if (isset($cview['level'])  &&  isset($cview['level']['block']) && is_string($cview['level']['block'])) {
            $this->default_block = $cview['level']['block'];
            list($this->view['level']['block']['start'], $this->view['level']['block']['end']) = 
                explode($splitter_start.$splitter_end, $cview['level']['block']);
        } else {
            list($this->view['level']['block']['start'], $this->view['level']['block']['end']) = 
                explode($splitter_start.$splitter_end, $this->default_block);
        }
        // item
        $this->view['level']['item'] = [];
        if (isset($cview['level']) &&  isset($cview['level']['item']) && is_string($cview['level']['item'])) {
            $this->default_item = $cview['level']['item'];
            list($this->view['level']['item']['start'], $this->view['level']['item']['end']) = 
                explode($splitter_start.$splitter_end, $cview['level']['item']);
        } else {
            list($this->view['level']['item']['start'], $this->view['level']['item']['end']) = 
                explode($splitter_start.$splitter_end, $this->default_item);
        }
        // content
        if (isset($cview['level']) &&  isset($cview['level']['content']) && is_string($cview['level']['content'])) {
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
                    if (isset($level['block']) && is_string($level['block'])) {
                        list($this->view[$i]['block']['start'], $this->view[$i]['block']['end']) = 
                            explode($splitter_start.$splitter_end, $level['block']);
                    }
                    if (isset($level['item']) && is_string($level['item'])) {
                        list($this->view[$i]['item']['start'], $this->view[$i]['item']['end']) = 
                            explode($splitter_start.$splitter_end, $level['item']);
                    }
                    if (isset($level['content']) && is_string($level['content'])) {
                        $this->view[$i]['content'] = $level['content'];
                    }
                }
            }
        }

        // fill the view object with default values for all other levels. Is sence only if 'is_relation_next', when levels count is known
        $this->addLevels($this->levels);
    }
    
    public function addLevels($levels)
    {
        // for more explicit syntax, save separators in short named variables
        $splitter_start = $this->view['splitter']['start'];
        $splitter_end = $this->view['splitter']['end'];
        
        // fill the view object with default values for all other levels. Is sence only if 'is_relation_next', when levels count is known
        for ($i = 0; $i <= $levels; $i++) {
            // block
            if (! isset($this->view[$i]['block'])) {
                list($this->view[$i]['block']['start'], $this->view[$i]['block']['end']) = 
                    explode($splitter_start.$splitter_end, $this->default_block);
            }
            // item
            if (! isset($this->view[$i]['item'])) {
                list($this->view[$i]['item']['start'], $this->view[$i]['item']['end']) = 
                    explode($splitter_start.$splitter_end, $this->default_item);
            }
            // content
            if (! isset($this->view[$i]['content'])) {
                $this->view[$i]['content'] = $this->view['level']['content'];
            }
        }
    }
    
    /**
     * 
     */
    public function setOutputHandler($name)
    {
        $this->output_handler = $name;
    }
    
    /**
     * Applies a default settings for a passed level. In fact, creates new level
     * in $this->view array with default setting.
     * 
     * Principal: to use level view settings, while node is outputting, this view
     * settings must be exist.
     * 
     * Used only in quicklyOutput() method, which is used to speed up a 
     * building and outputting process.
     * 
     * @param   int     $level      The number of a tree level. Range: [0; infinity].
     * @return
     */
    public function addLevel($level)
    {
        // for more explicit syntax save separators in short named variables
        $splitter_start = $this->view['splitter']['start'];
        $splitter_end = $this->view['splitter']['end'];
        
        
        // block
        if (! isset($this->view[$level]['block'])) {
            list($this->view[$level]['block']['start'], $this->view[$level]['block']['end']) = 
                explode($splitter_start.$splitter_end, $this->default_block);
        }
        // item
        if (! isset($this->view[$level]['item'])) {
            list($this->view[$level]['item']['start'], $this->view[$level]['item']['end']) = 
                explode($splitter_start.$splitter_end, $this->default_item);
        }
        // content
        if (! isset($this->view[$level]['content'])) {
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
        return '/'.$start.'('.$this->raw_replacing_pattern.')'.$end.'/U';
    }
    
    public function getWrapperStart($node_arr)
    {
        return $this->getItemString($this->view['wrapper']['start'], $node_arr);
    }
    public function getWrapperEnd($node_arr)
    {
        return $this->getItemString($this->view['wrapper']['end'], $node_arr);
    }
    public function getBlockStart($level, $node_arr)
    {
        return $this->getItemString($this->view[$level]['block']['start'], $node_arr);
    }
    public function getBlockEnd($level, $node_arr)
    {
        return $this->getItemString($this->view[$level]['block']['end'], $node_arr);
    }
    public function getItemStart($level, $node_arr)
    {
        return $this->getItemString($this->view[$level]['item']['start'], $node_arr);
    }
    public function getItemEnd($level, $node_arr)
    {
        return $this->getItemString($this->view[$level]['item']['end'], $node_arr);
    }
    public function getContent($level, $node_arr)
    {
        return $this->getItemString($this->view[$level]['content'], $node_arr);
    }
    
    /**
     * Routes to appropriate output handler method, with replaces an item expression
     * with a computed value. Returns computed value.
     * 
     * @param array $node_arr       An array of the current node.
     * @param string $elem_expr     An element expression, ex.: '%class-aitem%', 'link', etc.
     * @return string               Returns computed element string. For {{%class-aitem%}} it will be 'class="active"' etc.
     */
    private function getItemString(string &$item_expression, array &$node_arr) : string
    {
        return $this->{'outputHandler'.$this->output_handler}($item_expression, $node_arr);
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
    private function outputHandlerColumn(string &$item_expression, array &$node_arr) : string
    {
        // use 'preg_replace' with callback
        $item_string = preg_replace_callback(    // MAIN FUNCTION
            $this->replacing_pattern,               // usually it is: "/\{\{([a-zA-Z%0-9][a-zA-Z0-9%]*)\}\}/U" or more clearly "/{{([a-zA-Z%0-9][a-zA-Z0-9%]*)}}/U". Examples, "{{title}}", "{{description}}", "{{%class-active%}}", "{{%active%}}", etc.
            function ($matches) use (&$node_arr) {      // callback: will run for every occurence pattern {{...}} in $elem_expression
                // pattern matches a replacing part and passes matches to this function
                $replacing_part = &$matches[1];     // here the $replacing_part will be: 'title', 'description' or another column name etc.
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
    private function outputHandlerReplacer(string &$item_expression, array &$node_arr) : string
    {
        // use 'preg_replace' with callback
        $item_string = preg_replace_callback(    // MAIN FUNCTION
            $this->replacing_pattern,               // usually it is: "/\{\{([a-zA-Z%0-9][a-zA-Z0-9%]*)\}\}/U" or more clearly "/{{([a-zA-Z%0-9][a-zA-Z0-9%]*)}}/U". Examples, "{{title}}", "{{description}}", "{{%class-active%}}", "{{%active%}}", etc.
            function ($matches) use (&$node_arr) {      // callback: will run for every occurence pattern {{...}} in $elem_expression
                // pattern matches a replacing part and passes matches to this function
                $replacing_part = &$matches[1];     // here the $replacing_part will be: 'title', 'description', '%class-active%', '%active%', etc.
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
    private function outputHandlerInserter()
    {
        
    }
}
