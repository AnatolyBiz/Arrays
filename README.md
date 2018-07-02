# Arrays

## Introduction

Now library is in development stage and not all its code is verified, tested and optimized, but at all a basic functionality is supported.

## Examples

Let's output some menu:

```php
require 'project/Arrays/autoloader.php';

use Arrays\D2\Tree\AdjacencyList\Tree as AlTree;
use Arrays\D2\Tree\AdjacencyList\Option as AlTreeOption;
use Arrays\D2\Tree\View\Handler;
use Arrays\D2\Tree\View\Replacer;

function getTemplate()
{
    // Here is used static template (but it can be loaded from DB, memory, disk, etc.)
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
function echo_menu()
{
    // `tree` table can be found in \data\tree.sql file
    $source = (new PDO('mysql:host=localhost;dbname=test', 'root', ''))
        ->query('SELECT id, parent_id, title, link from `tree`')
        ->fetchAll(PDO::FETCH_ASSOC);
    
    
    // Create a tree object, for build and output new tree
    $tree = new ALTree($source, 'id', 'parent_id');
        
    // Tree options
    $options = $tree->options; // or $options = $tree->getOptions();
        
    // descendants
    $options->set(AlTreeOption::COUNT_DESCENDANTS);
    // numbering
    $options->set(ALTreeOption::NUMBERING);
    // turn on debug mode
    $options->set(ALTreeOption::DEBUG_MODE);
        
    // view
    $view = $options->view;
    
    // set or add a view template
    $view->set( getTemplate() ); // or $view->add( getTemplate() );
        
    // add view replacers
    foreach ( getReplacers() as $key => $replacer ) {
        $view->addOutputReplacer($key, $replacer);
    }
    
    // So will be replaced {{%class-active%}} and {{%title%}} expressions. But
    // default output handler is 'Column' (recognizes only column names), therefore
    // allow to handle another expressions (like %title%).
    $view->setOutputHandler(Handler::REPLACER);
    
    // Output
    $tree->output(); // or $tree->outputFast();
}

// Menu output
echo_menu();

```