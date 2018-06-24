# Arrays

## Introduction

Now library is in development stage and not all its code is verified, tested and optimized, but at all a basic functionality is supported.

## Examples

Let's output some menu:

```php
require 'project/Arrays/autoloader.php';

use Arrays\D2\Tree\AdjacencyList\Tree as AlTree;
use Arrays\D2\Tree\AdjacencyList\Option as AlTreeOption;
use Arrays\D2\Tree\View\Replacer;

function addTemplate($view)
{
    // Here is use static template (but it can be loaded from DB, memory, disk, etc.)
    $template = [
        'splitter' => [
            'start' => '{{', 
            'end'   => '}}'
        ],
        'wrapper' => '<div>{{}}</div>',
        'level' => [
            'block'     => '<ul>{{}}</ul>',
            'item'      => '<li>{{}}</li>',
            'content'   => '<span {{%active%}} data-id="{{id}}" data-parentid="{{parent_id}}" numbering="{{numbering}}">{{%title%}}</span>'
        ]
    ];
    
    // Note: 'set' method requires the whole template and overrides view template, but 'add' 
    // method overrides only passed template items (other items is taken from 
    // '/Config/d2.tree.config.php' file 'view' key)
    $view->set($template);
}
function addReplacers($view)
{
    // Replacer. Detects item with active link and mark it with 'class="active"' attribute
    $replacer = function (&$node_arr, &$result) : bool {
        if($node_arr['link'] === $this->uri) {
            $result = 'class="active"';
            return true;
        }
        return false;
    };
    $data = ['uri' => $_SERVER['REQUEST_URI']];
    $view->addReplacer('%class-active%', new Replacer($replacer, $data));
    
    // Replacer. Handles every %title% in a node content and adds to its content some Font Awesome icon
    $replacer = function (&$node_arr, &$result) : bool {
        $result = $node_arr['title'] . ' <span class="fa fa-icon"></span>';
        return true;
    };
    $view->addReplacer('%title%', new Replacer($replacer));
    
    // So will be replaced add {{%class-active%}} and {{%title%}} expressions. But default output 
    // handler is 'Column', therefore allow handle not only column names.
    $view->setOutputHandler('Replacer');
}

// Outputs menu
function echo_menu()
{
    // `tree` (`id`, `parent_id`, `title`, `hint`, `link`)
    $source = (new PDO('mysql:host=localhost;dbname=test', 'root', ''))
        ->query('SELECT * from `tree`')
        ->fetchAll(PDO::FETCH_ASSOC);
    
    // Create a tree object, for build and output new tree
    $tree = new ALTree($source, 'id', 'parent_id');

    $options = $tree->options;
    $options->set(ALTreeOption::NUMBERING);
    
    $view = $tree->options->view;
    addTemplate($view);
    addReplacers($view);
    
    $tree->outputFast();
}

// Menu output
echo_menu();
```