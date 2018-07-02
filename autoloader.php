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
        // sorry I am not Your al..., move to the next registered autoloader
        return false;
    }
            
    // get the relative class name
    $relative_class = substr($class, $len);
    
    // create a path to a file
    $path = __DIR__ . '/' . $relative_class . '.php';
         
    // if the file exists, require it
    if(file_exists($path)) {
        require $path;               
        return true;       
    }
    return false;
}

// add function to PHP autoloader's stack
spl_autoload_register(__NAMESPACE__ . '\\autoloader');