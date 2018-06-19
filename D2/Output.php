<?php
/**
 * Arrays Output Class
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\D2;


/**
 * Implements helpful methods for output D2 arrays (D2 means two-dimensional).
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class Output
{
    /**
     * The pattern matches number values.
     */
    const PATTERN_NUMBER = '/^(([\d]+)|([\d]+[\.][\d]+([\,\d])*)|([\d]+[\,][\d]+([\.\d])*))$/U';
    
    /**
     * Data output row by row, and an each row has a same attribute list.
     */
    const OUTPUT_TYPE_PLAIN_EVEN = 'outputPlainEven';
    
    /**
     * Data output row by row, and an each row can has a different attribute list.
     */
    const OUTPUT_TYPE_PLAIN_UNEVEN = 'outputPlainUneven';
    
    /**
     * Data output row by row in a form of a table, and an each row has a same attribute list.
     */
    const OUTPUT_TYPE_TABLE_EVEN = 'outputTableEven';
    
    /**
     * Data output row by row in a form of a table, and an each row can has a different attribute list.
     */
    const OUTPUT_TYPE_TABLE_UNEVEN = 'outputTableUneven';
    
    /** 
     * A 2D source array. 
     */
    private $source;
    
    /** 
     * A List of attributes in $source array to be outputted. 
     */
    private $src_attrs;
    
    /**
     * A view settings.
     * 
     * Outputting table has next html structure:
     * <div class="table-container">
     *      <table class="table">
     *          <tr><th>...</th></tr>
     *          <tr><td>...</td></tr>
     *          <tr><td>...</td></tr>
     *          ...
     *      </table>
     * </div>
     * 
     * A $view['css'] property contains clean valid CSS code. So, You can specify Your
     * own view for classes and tags, and it will apply to all table.
     * 
     * A $view['class'] property contains a list of columns and/or patterns (as keys),
     * and class names (as values). Array2D class has few predefined classes, like
     * 'text-center', 'text-right', etc. So, You can use them. Or $view['css']
     * allows You to define Your own css classes and use their names here.
     * How it works: 
     * while row is outputted by cells:
     *  1) to every cell value is applying every pattern, and if matching is successful, then
     *     its class is adding to temporary variable with class expression.
     *  2) next is examining cell key (column name), and if for it exists settings, then
     *     their values is applying, and class will be added to all pattern classes variable.
     * 
     * A $view['style'] property, is similarly to $view['class'], only difference is
     * css code will written in 'style' attribute of html element.
     * 
     */
    private $view;
        
    
    /**
     * Initializes 2D object.
     * 
     * @param array $source A source two-dimensional array (it can be even or uneven).
     * @return Arrays\Array2D Returns Arrays\Array2D object for chaining support.
     */
    public function __construct(array &$source)
    {
        // Verify: is $source an array?
        if(!is_array($source)) {
            throw new \LogicException('Parameter must be an array.');
        }
        
        // Initialize 2D object
        $this->source = $source;
                
        // Chaining support
        return $this;
    }
    
    /**
     * Attributes of the source array, which will output.
     * 
     * @param array $src_attrs  An attributes array.
     * @return Arrays\Array2D   An Array2D object.
     */
    public function attributes(array &$src_attrs) : self
    {
        // prepares valid attributes/columns array $src_attrs. Array $src_attrs is creating only for outputting a table header.
        if(is_array($src_attrs) && count($src_attrs) > 0) {
            $this->src_attrs = &$src_attrs;
        }
            
        // chaining
        return $this;
    }
    
    /**
     * A wrapper for the 'attributes' method.
     * 
     * @param array $src_attrs  An attributes array.
     * @return Arrays\Array2D   An Array2D object.
     */
    public function attrs(array &$src_attrs) : self
    {
        return $this->attributes($src_attrs);
    }
    
    /**
     * Initializes the view object.
     * 
     * @param array $custom_view Custom view settings.
     */
    public function view(array &$custom_view = null)
    {
        // verify: is $custom_view an array?
        if(!is_null($custom_view) && !is_array($custom_view)) {
            throw new \LogicException('Parameter must be an array.');
        }
        
        // initialize a view
        $this->applyDefaultView($this->view);
        if(!is_null($custom_view) && count($custom_view) > 0) {
            $this->applyCustomView($custom_view, $this->view);
        }
        
        // chaining
        return $this;
    }
    
    /**
     * Applies default view settings.
     * 
     * @param array     $view           An object with view settings.
     */
    private function applyDefaultView(&$view)
    {
        // Default view settings
        $view['space'] = "\t"; // space between values in plain output
        $view['def-splitter'] = '{{}}'; // default splitter
        /*
        $view['splitter'], can be initialized by user, and it will replace default splitter
        $view['layout'], is initializing in its appropriate methods
         */
        $view['tr-header'] = '<tr>' . $view['def-splitter'] . '</tr>'; // for 'output_table_*'
        $view['th'] = '<th>' . $view['def-splitter'] . '</th>';
        $view['tr'] = '<tr>' . $view['def-splitter'] . '</tr>';
        $view['td'] = '<td>' . $view['def-splitter'] . '</td>';
        $view['css'] = '
            .plain-container {
                width: inherit;
                margin: 0 auto 0 auto;
            }
            .plain-container .plain {
                white-space:pre;
                word-break: keep-all;
            }
            .table-container {
                width: inherit;
                margin: 0 auto 0 auto;
              }
            .table-container .table {
                border-collapse: collapse;
                width: 100%;
                max-width: 100%;
                margin-bottom: 1rem;
                background-color: transparent;
              }
            .table-container .table th, .table-container .table td {
                padding: 0.2rem;
                vertical-align: center;
              }
            .table-container .table th {
                background-color: #67BCDB;
                color: white;
                font: 13px Calibri;
            }
            .table-container .table td {
                font: 12px Calibri;
                padding: 5px;
            }
            .table-container .table tr:nth-child(even){
                background-color: #f2f2f2;
            }
            .text-center {
                text-align: center;
            }
            .text-left {
                text-align: left;
            }
            .text-right {
                text-align: right;
            }
        ';
        $view['class'] = [];  /** means: add this class to any <td class="here"> tag, */
        $view['class-pattern'] = []; /** which is matching this pattern */
        $view['style'] = []; /** like 'class' */
        $view['style-pattern'] = [];
    }
    
    /**
     * Applies user view settings.
     * 
     * @param array     $custom_view    A user view array.
     * @param array     $view           An object with view settings.
     */
    private function applyCustomView(array &$custom_view, array &$view) : void
    {
        // replace space
        if(isset($custom_view['space'])) {
            $view['space'] = $custom_view['space'];
        }
        
        // replace splitter
        if(isset($custom_view['splitter'])) {
            $view['splitter'] = $custom_view['splitter'];
            $this->updateSplitter($custom_view['splitter'], $this->view);
        }
        
        // replace table layout (wrapper)
        if(isset($custom_view['layout'])) {
            $view['layout'] = $custom_view['layout'];
        }
        
        // replace header row layout
        if(isset($custom_view['tr-header'])) {
            $view['tr-header'] = $custom_view['tr-header'];
        }
        
        // replace layout of header cell
        if(isset($custom_view['th'])) {
            $view['th'] = $custom_view['th'];
        }
        
        // replace layout of table body row
        if(isset($custom_view['tr'])) {
            $view['tr'] = $custom_view['tr'];
        }
        
        // replace layout of header cell
        if(isset($custom_view['td'])) {
            $view['td'] = $custom_view['td'];
        }
        
        // add custom css code
        if(isset($custom_view['css'])) {
            $view['css'] .= $custom_view['css'];
        }
        
        // add custom css classes
        if(isset($custom_view['class'])) {
            $view['class'] = array_merge($view['class'], $custom_view['class']);
        }
        if(isset($custom_view['class-pattern'])) {
            $view['class-pattern'] = array_merge($view['class-pattern'], $custom_view['class-pattern']);
        }
        
        // add custom css styles
        if(isset($custom_view['style'])) {
            $view['style'] = array_merge($view['style'], $custom_view['style']);
        }
        if(isset($custom_view['style-pattern'])) {
            $view['style-pattern'] = array_merge($view['style-pattern'], $custom_view['style-pattern']);
        }
    }
    
    /**
     * Applies a user splitter value.
     * 
     * @param string    $splitter   A user splitter.
     * @param array     $view       An object with view settings.
     */
    private function updateSplitter(string &$splitter, array &$view)
    {
        // replace default splitter for custom user splitter in all places where it used
        str_replace($view['def-splitter'], $splitter, $view['layout']);
        str_replace($view['def-splitter'], $splitter, $view['tr-header']);
        str_replace($view['def-splitter'], $splitter, $view['th']);
        str_replace($view['def-splitter'], $splitter, $view['tr']);
        str_replace($view['def-splitter'], $splitter, $view['td']);
    }

    /**
     * Computes a value of the html class attribute.
     * 
     * @param array $row    The current row in the outputing loop.
     * @param mixed $col    A column key.
     * @return string       A string with name and value of the html style attribute.
     */
    private function classString(&$row, &$col) : string
    {
        // value of html element attribute 'class="..."'
        $classes = '';
        // 
        foreach ($this->view['class'] as $key => $class) {
            // if $key is pattern
            if(preg_match('/^\$.+\$$/U', $key)) {
                // Get pattern
                $pattern = $this->view['class-pattern'][$key];
                if(!is_null($pattern) && preg_match($pattern, $row[$col])) {
                    $classes .= ($class . ' ');
                }
                continue;
            }
            // if $key is column name
            if($key === $col) {
                $classes .= ($class . ' ');
            }
        }
        
        // wrap matched classes and remove last space
        if(! empty($classes)) {
            $classes = 'class="'.preg_replace('~\s$~', '', $classes).'"';
        }
        
        // return result
        return $classes;
    }
    
    /**
     * Computes a value of the html style attribute.
     * 
     * @param array $row    The current row in the outputing loop.
     * @param mixed $col    A column key.
     * @return string       A string with name and value of the html style attribute.
     */
    private function styleString(&$row, &$col) : string
    {
        // value of a html element attribute 'style="..."'
        $styles = '';
        // 
        foreach ($this->view['style'] as $key => $style) {
            // if $key is a pattern
            if(preg_match('/^\$.+\$$/U', $key)) {
                // get the pattern
                $pattern = $this->view['style-pattern'][$key];
                if(! is_null($pattern)  &&  preg_match($pattern, $row[$col])) {
                    $styles .= ($style . ' ');
                }
                continue;
            }
            // if $key is a column name
            if($key === $col) {
                $styles .= $style;
            }
        }
        
        // wrap a matched classes and remove a last space
        if(!empty($styles)) {
            $styles = 'style="'.$styles.'"';
        }
        
        // return the result
        return $styles;
    }
    
    /**
     * Returns a attribute list for even arrays.
     * 
     * @return array An attribute list.
     */
    private function getAttrsEven() : array
    {
        // get all keys of the first row of the source array
        foreach ($this->source as &$first_row) {
            return array_keys($first_row); 
        }
    }
    
    /**
     * Returns a attribute list for uneven arrays.
     * 
     * @return array An attribute list.
     */
    private function getAttrsUneven() : array
    {
        // Algorithm below does not save right structure of a source 
        // array: new attrs is added to tail of the array.
        
        // get attrs of the first row
        $first_row = current($this->source);
        $attrs = array_keys($first_row);
        
        // compare each attr of every source array row with first row attrs
        $current_row = next($this->source);
        while ($current_row !== false) {
            $keys = array_keys($current_row);
            foreach ($keys as &$key) {
                if(!in_array($key, $attrs)) {
                    array_push($attrs, $key);
                }
            }
            $current_row = next($this->source);
        }
        
        // return result
        return $attrs;
    }
    
    /**
     * Outputs 2D array in the appropriate form.
     * 
     * @param array     $cols       An array of columns which will be outputted.
     * @param string    $method     A method to use for output array.
     * 
     * @return          self        Returns self (chaining support).
     */
    public function output (string $method) : self
    { 
        // if source array is empty, nothing to output
        if (count($this->source) == 0) {
            return $this;
        }
        
        // verify method exists
        if(!method_exists($this, $method)) {
            throw new \LogicException('Method \'' . $method . '\' does not exists.');
        }
                
        // initialize a view, if it didn't specified
        if(is_null($this->view)) {
            $this->view();
        }
                
        // call an appropriate method
        call_user_func([$this, $method]);
                
        // chaining support
        return $this;
    }
    
    /**
     * General method for output data in a plain form.
     * 
     * @return void
     */
    private function outputPlain(array &$attrs) : void
    {
        // Prepare variables
        
        // splitter
        $splitter = isset($this->view['splitter']) ? $this->view['splitter'] : $this->view['def-splitter'];
        
        // layout
        if(!isset($this->view['layout'])) {
            $this->view['layout'] = '<div class="plain-container"><div class="plain">' . $splitter . '</div></div>';
        }
        list($layout_start, $layout_end) = explode($splitter, $this->view['layout']);
        
        
        // Output
        
        // plain styles ?>
        <!-- <?= __METHOD__.'(). Plain styles:' ?> -->
        <style><?= $this->view['css'] ?></style><?php
        
        // plain layout, start
        echo $layout_start;
                
        // header cells
        foreach ($attrs as $attr) {
            echo '<b>' . $attr . '</b>' . $this->view['space'];
        }
        
        // body
        echo PHP_EOL;
        foreach ($this->source as &$row) { 
            foreach ($attrs as &$attr) {
                echo $row[$attr] . $this->view['space']; // cell
            }
            echo PHP_EOL;
        }
                
        // plain layout, end
        echo $layout_end;
    }
    
    /**
     * General method for output data in a form of a table.
     * 
     * @return void
     */
    private function outputTable(array &$attrs) : void
    {
        // Prepare variables
        
        // splitter
        $splitter = isset($this->view['splitter']) ? $this->view['splitter'] : $this->view['def-splitter'];
        
        // layout
        if(!isset($this->view['layout'])) {
            $this->view['layout'] = '<div class="table-container"><table class="table">' . $splitter . '</table></div>';
        }
        list($layout_start, $layout_end) = explode($splitter, $this->view['layout']);
        
        // header row
        list($tr_header_start, $tr_header_end) = explode($splitter, $this->view['tr-header']);
        
        // header cell
        list($th_start, $th_end) = explode($splitter, $this->view['th']);
        
        // row
        list($tr_start, $tr_end) = explode($splitter, $this->view['tr']);
        
        // cell
        list($td_start, $td_end) = explode($splitter, $this->view['td']);
        
        
        // Output
        
        // table styles ?>
        <!-- <?= __METHOD__.'(). Table styles:' ?> -->
        <style><?= $this->view['css'] ?></style><?php
        
        // table layout, start
        echo $layout_start;
                
        // header row 
        echo $tr_header_start;
        foreach ($attrs as $attr) {
            echo $th_start . $attr . $th_end;
        }
        echo $tr_header_end;
                
        // table body
        foreach ($this->source as &$row) { ?>
            <tr><?php
                foreach ($attrs as &$attr) {
                    // output cell ?>
                    <td <?= $this->classString($row, $attr) . ' ' . $this->styleString($row, $attr) ?>>
                        <?= $row[$attr] ?>
                    </td><?php
                } ?>
            </tr><?php
        }
        
        // table layout, end
        echo $layout_end;
    }
    
    /**
     * Outputs data row by row, and an each row can has a same attribute list.
     * 
     * @return void
     */
    public function outputPlainEven() : void
    {
        // prepare attributes
        if(!is_null($this->src_attrs)) {
            $attrs = &$this->src_attrs;
        } else {
            $attrs = $this->getAttrsEven();
        }
        
        // output table
        $this->outputPlain($attrs);
    }
    
    /**
     * Outputs data row by row, and an each row can has a different attribute list.
     * 
     * @return void
     */
    public function outputPlainUneven() : void
    {
        // prepare attributes
        if(!is_null($this->src_attrs)) {
            $attrs = &$this->src_attrs;
        } else {
            $attrs = $this->getAttrsUneven();
        }
        // output table
        $this->outputPlain($attrs);
    }
    
    /**
     * Data output row by row in a form of a table, and an each row has a same attribute list.
     * 
     * @return void
     */
    public function outputTableEven() : void
    {
        // prepare attributes
        if(!is_null($this->src_attrs)) {
            $attrs = &$this->src_attrs;
        } else {
            $attrs = $this->getAttrsEven();
        }
        
        // output table
        $this->outputTable($attrs);
    }
    
    /**
     * Data output row by row in a form of a table, and an each row can has a different attribute list.
     * 
     * For example:
     * $row1 = ['id' => 1, 'title' => 'Bread']
     * $row2 = ['id' => 5, 'weight' => '0.4']
     * 
     * An output will have a next form:
     *  id   |   title   |   weight
     * -----------------------------
     *  1        Bread       
     *  5                    0.4
     * 
     * @return void
     */
    public function outputTableUneven() : void
    {
        // prepare attributes
        if(!is_null($this->src_attrs)) {
            $attrs = &$this->src_attrs;
        } else {
            $attrs = $this->getAttrsUneven();
        }
        
        // output table
        $this->outputTable($attrs);
    }
}
