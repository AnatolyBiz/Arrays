<?php
/**
 * Arrays Library Autoloader
 * 
 * This file contains function 'autoloader' to make easy a work with this library,
 * because all what need to do, is place library folder within project and 
 * connect this autoloader.php file in any place before using library classes.
 * 
 * Developed with support PSR-4 standard (http://www.php-fig.org/psr/psr-4/).
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays;


/**
 * Loads "class" (a requested item: class, interface, trait) to the PHP code space.
 * 
 * @param string $class Contains requested item name, something like 'Arrays\Array2D' or 'Arrays\Tree'
 * @return bool Returns TRUE if requested "class" was found and successfully loaded.
 */
function autoloader($class)
{
    // library namespace prefix
    $prefix = 'Arrays\\';

    // does the class use the library namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return false;
    }
    
    /**
     * Loading indicator
     * 
     * Main goal: when will be loaded all library files, the loader remove 
     * itself from PHP autoloader's stack.
     * 
     * Library list:
     * 1a) D2\D2Array.php
     * 1b) D2\Output.php
     * 2a) D2\Tree\AjacencyList\Tree.php
     * 3b) D2\Tree\OutputReplacer.php
     * x) D2\Tree\OutputInserter.php, not implemented
     * x) DM\Array.php, not implemented
     
     * total: 4 files.
     */
    static $total = 4;
    static $loaded = 0;
        
    // get the relative class name
    $relative_class = substr($class, $len);
    
    // create a path to a file
    $path = __DIR__ . '/' . $relative_class . '.php';
         
    // if the file exists, require it
    if(file_exists($path)) {
        require $path;
               
        // if was loaded all library files, removes itseft from PHP autoloader's stack
        if ($total == ++$loaded) {
           spl_autoload_unregister(__FUNCTION__);
        }
                
        return true;       
    }
    return false;
}

// add function to PHP autoloader's stack
spl_autoload_register(__NAMESPACE__ . '\\autoloader');