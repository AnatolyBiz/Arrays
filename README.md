# Arrays

## Introduction

Now library is in development stage and not all its code is verified, tested and optimized, but at all a basic functionality is supported.

## Examples

Let's output some menu:

```php
require 'project/Arrays/autoloader.php';

use Arrays\D2\Tree\AdjacencyList\Tree as AlTree;
use Arrays\D2\Tree\OutputReplacer;

function echo_menu()
{
    // Get a raw tree array
    $source = (new PDO('mysql:host=localhost;dbname=test', 'root', ''))
            //->query('SELECT * from `tree`')
            ->query('SELECT * from `tree2` WHERE `id` < 300') //  
            ->fetchAll(PDO::FETCH_ASSOC);
    
    // Create a tree object, for build and output new tree
    $tree = new AlTree($source, 'id', 'parent_id');
    
	
    // Set a view settings for the tree dispaying
    $cview['wrapper'] =           '<nav>{{}}</nav>';
    $cview['level']['block'] =    '<ul data-level="{{level}}">{{}}</ul>';
    $cview['level']['item'] =     '<li>{{}}</li>';
    $cview['level']['content'] =  '<a class="{{%active%}}" href="{{link}}" data-id="{{id}}">{{%title%}}</a>';
    $cview[0]['block'] =          '<ul class="menu" data-level="{{level}}">{{}}</ul>';
    


    // Optional. Add few callbacks to handle a node output:
    // - a callback to mark active menu item ( using key is '%active%' ):
    $tree->addReplacer(
        '%active%',
        new OutputReplacer(
            function (&$node, &$result) : bool {
                if($node['link'] === $this->uri) {
                    $result = 'active';
                    return TRUE;
                }
                return FALSE;
            },
            ['uri' => $_SERVER['REQUEST_URI']]
        )
    );
    // - a callback to mark active menu item ( using key is '%class-active%' ):
    $tree->addReplacer(
        '%class-active%',
        new OutputReplacer(
            function (&$node, &$result) : bool {
                if($node['link'] === $this->uri) {
                    $result = 'class="active"';
                    return TRUE;
                }
                return FALSE;
            },
            ['uri' => $_SERVER['REQUEST_URI']]		// this prm 'uri' inside closure will be $this->uri
        )
    );
    // - a callback for prepare title of link:
    $tree->addReplacer(
        '%title%',
        new OutputReplacer(
            function (&$node, &$result) : bool {
                $result = $node['title'] . ' <span class="fa fa-icon"></span>';
                return TRUE;
            }
        )
    );
        
    // Build & Output (print) the new sorted tree
    $tree->quicklyOutput($cview);
    //$tree->output($cview); // bug exists
    
    // Clear memory
    unset($tree);
    
}

// Menu output
echo_menu();
```