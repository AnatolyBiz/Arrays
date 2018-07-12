# Arrays

## Introduction

Now library is in development stage and not all its code is verified, tested and optimized, but at all a basic functionality is supported.

## Examples

Let's output some menu:

```php
ini_set('error_reporting', E_ALL & ~E_NOTICE);

require './../project/Arrays/autoloader.php';

use Arrays\D2\Tree\AdjacencyList\Tree as Tree;      // or AlTree
use Arrays\D2\Tree\AdjacencyList\Option as Option;  // or AlTreeOption
use Arrays\D2\Tree\AdjacencyList\Output\Handler;    
use Arrays\D2\Tree\AdjacencyList\Output\Replacer;
use Arrays\D2\D2Array;

// Returns a template for menu
function getTemplate()
{
    // Here is used static template (but it can be loaded from anywhere)
    $template = [
        'splitter' => [
            'start' => '{{',
            'end'   => '}}'
        ],
        'wrapper' => '<nav>{{}}</nav>',
        'level' => [
            'block'     => '<ul>{{}}</ul>',
            'item'      => '<li>{{}}</li>',
            'content'   => '<a href="{{link}}"><span {{%active%}} data-id="{{id}}" data-parentid="{{parent_id}}" descendants="{{descendants}}" numbering="{{numbering}}">{{%title%}}</span></a>'
        ],
        0 => [
            'item'      => '<li class="item-0">{{}}</li>',
        ],
    ];
    
    return $template;
}

// Returns few replacers to automatically replace some values
function getReplacers()
{
    // Replacers
    $replacers = [];
        
    // Replacer. Detects item with active link and mark it with 'class="active"' attribute
    $replacer = function (&$node_arr, &$result) : bool {
        if($node_arr['link'] === $this->uri) {
            $result = 'class="active"';
            return true;
        }
        return false;
    };
    $data = ['uri' => $_SERVER['REQUEST_URI']];
    // Add replacer
    $replacers['%class-active%'] = new Replacer($replacer, $data);
        
    // Replacer. Handles every %title% in a node content and adds to its content some Font Awesome icon
    $replacer = function (&$node_arr, &$result) : bool {
        $result = $node_arr['title'] . ' <span class="fa fa-icon"></span>';
        return true;
    };
    // Add replacer
    $replacers['%title%'] = new Replacer($replacer);
        
    return $replacers;
}

// Outputs a menu
function echo_menu()
{
    // `tree` table can be found in \data\tree.sql file
    $source = (new PDO('mysql:host=localhost;dbname=test', 'root', ''))
        ->query('SELECT `id`, `parent_id`, `title`, `link` from `tree`')
        ->fetchAll(PDO::FETCH_ASSOC);
    
    
    // Create a tree object, for build and output new tree
    $tree = new Tree($source, 'id', 'parent_id');
        
    // Options
    $options = $tree->options; // or $options = $tree->getOptions();
    // descendants
    $options->set(Option::COUNT_DESCENDANTS);
    // numbering
    $options->set(Option::NUMBERING);
    // turn on debug mode
    $options->set(Option::DEBUG_MODE);
        
    // View
    $options->view->set( getTemplate() ); //$options->view->add( getTemplate() );
    
    // Tree output
    $tree->outputFast( getReplacers(), Handler::REPLACER );
    
    
    // Or use an Output object:
    //$output = $options->output;
    // add replacers
    //foreach ( getReplacers() as $key => $replacer ) {
    //    $output->addReplacer($key, $replacer);
    //}
    // expressions handler. So will be replaced {{%class-active%}} and {{%title%}} expressions. But default output handler is 'Column' (recognizes only column names), therefore allow to handle another expressions (like %title%).
    //$output->setHandler(Handler::REPLACER);
    // output, performs output of tree
    //$output->write();
    //$output->writeFast();
        
    // or save output to a variable:
    //$output->isBuffered(true);
    //$output->write(); //$output->writeFast();
    //$output_str = $output->get(); //or: //$output->saveTo($output_str);
}

echo_menu();

```