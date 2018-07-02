<?php
/**
 * Arrays Numbering Class
 *
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\Numbering;

/**
 * Helps create numbering.
 *
 */
class Numbering
{
    /**
     * An array which contains settings of numbering for a default or another particular
     * level. Any level can has it own a numbering type.
     * Example: 1.2.1, 1.A.a, A.1, etc.
     * 
     * @access private
     * @var array
     */
    private $level_numbering;
    
    /**
     * A delimiter used to split a symbols while numbering of tree 
     * nodes. Usualy it is '.'
     * 
     * @access private
     * @var string
     */
    private $symbol_delimiter;
        
    /**
     * Numerator array.
     * 
     * Every key of this array is one of 'NUMBERING_*' consts, and every value is a
     * closure, which is used to generate a current numbering symbol, based on the children
     * number of a node (offset).
     * Example:
     * [NUMBERING_LOWERLATIN] = function ($offset) {...};
     * 
     * @access private
     * @var array
     */
    private $numerator;
    
    
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
    
    
    
    public function __construct(array $level_numbering = ['default' => 'Decimal'], string $delimiter = '.')
    {
        // initialize the numbering
        $this->level_numbering = &$level_numbering;
        
        // a symbol delimiter
        $this->symbol_delimiter = $delimiter;
        
        // load a numerators
        $this->numerator = [];
        foreach ($level_numbering as &$name) {
            if (! isset($this->numerator[$name])) {
                $this->numerator[$name] = $this->getNumerator($name);
            }
        }
        
        // chaining
        return $this;
    }
    
    /**
     * Sets (rewrites) 
     */
    public function set(array $level_numbering, string $delimiter = '.') : self
    {
        // initialize the numbering
        $this->level_numbering = &$level_numbering;
        
        // a symbol delimiter
        $this->symbol_delimiter = $delimiter;
        
        // load a numerators
        foreach ($level_numbering as &$name) {
            if (! isset($this->numerator[$name])) {
                $this->numerator[$name] = $this->getNumerator($name);
            }
        }
        
        // chaining
        return $this;
    }
    
    /**
     * Sets a custom exception class.
     */
    public function setExceptionClass($exceptionClass)
    {
        $this->exceptionClass = $exceptionClass;
    }
    
    /**
     * 
     */
    private function getNumerator(string &$name) : \Closure
    {
        // a numerator file
        $path = __DIR__ . '/Numerator/' . $name . '.php';
        
        // load the numerator
        $numerator = include($path);
        
        // has a problems while a loading?
        if (false === $numerator)
        {
            throw new $this->exceptionClass('A numerator file \'' . $path . '\' cannot be loaded.');
        }
        
        // verify the numerator
        if(false === $this->isNumerator($numerator))
        {
            throw new $this->exceptionClass('The numerator has errors (review the code of the numerator \''.$path.'\').');
        }
        
        return $numerator;
    }
    
    /**
     * 
     */
    private function isNumerator($numerator) : bool
    {
        if (is_callable($numerator)) {
            // get a reflection object to verify the callable
            $refl = new \ReflectionFunction($numerator);
            
            // closure parameters
            $total_params = 1; // int &$offset
            $num_of_rparams = $refl->getNumberOfRequiredParameters();
            if ($num_of_rparams != $total_params) {
                throw new $this->exceptionClass('The numerator method has wrong signature: is passed '.$num_of_rparams.' required parameters, but need - '.$total_params.'.');
            }
            // parameters types
            $error_message = 'The return type of the numerator or a parameter of the numerator method is wrong (it has not an allowed name or is passed not by reference).';
            $params = $refl->getParameters();
            $pi = -1;
            // 'int &$offset' parameter
            $prm = $params[++$pi];
            if (!($prm->getName() === 'offset' && $prm->isPassedByReference() && $prm->hasType() && $prm->getType()->getName() == 'int')) {
                throw new $this->exceptionClass($error_message);
            }
            
            // closure
            if (!($refl->hasReturnType() && $refl->getReturnType()->getName() == 'string')) {
                throw new $this->exceptionClass($error_message);
            }
            
            // all a numerator parameters is valid
            return true;
        }
        
        // glaring mistake: $numerator is not a function
        throw new $this->exceptionClass('The passed numerator is not a function.');
    }
        
    /**
     * Sets an appropriate numbering symbol for a given node.
     * 
     * @param   array   A node row.
     * @return  void
     */
    private function getSymbol(int $level, int $childNumber)
    {        
        // get a name of numerator (DECIMAL, UPPERLATIN, LOWERLATIN, etc.) for a given level
        $name = $this->level_numbering[$level];
        if (empty($name))
            $name = $this->level_numbering['default'];
        
        // get a numerator for the numbering
        $numerator = $this->numerator[$name];
                
        // create a full node numbering symbol
        $symbol = $numerator($childNumber); // '1', 'A', 'a', etc
        
        // result
        return $symbol;
    }
    
    /**
     * Sets an appropriate numbering symbol for a given node.
     * 
     * @param   array   A node row.
     * @return  void
     */
    public function getNumbering(int $level, int $childNumber, string $parentNumbering = null)
    {        
        // gets numbering symbol for current node
        $currentSymbol = $this->getSymbol($level, $childNumber);
        
        // add a delimiter
        if (! is_null($parentNumbering))
            $numbering = $parentNumbering . $this->symbol_delimiter; // '1.1.'
        
        // create a full node numbering symbol
        $numbering .= $currentSymbol; // '1.1.' . '3'
        
        // result
        return $numbering;
    }
}
