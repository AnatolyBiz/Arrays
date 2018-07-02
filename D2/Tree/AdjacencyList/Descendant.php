<?php
/**
 * Arrays Descendant Class
 *
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
namespace Arrays\D2\Tree\AdjacencyList;

use Arrays\D2\Tree\AdjacencyList\Exception\DescendantCountingException;

/**
 * The Option class contains constants and another objects for Tree class.
 * 
 * @package Arrays
 * @link    https://github.com/AnatolyKlochko/Arrays
 * @author  Anatoly Klochko <anatoly.klochko@gmail.com>
 */
class Descendant
{
    /**
     * Arrays\D2\Tree\AdjacencyList\Tree
     */
    private $alTree;
    
    /**
     * A column name (or index), which contains values of parents of nodes.
     * 
     * Required.
     * @access private
     * @var mixed
     */
    private $src_parent;
        
    /**
     * A value to determine a top node.
     * 
     * Optional. Default value is 0 (zero).
     * @access private
     * @var mixed
     */
    private $top_ident;
    
    /**
     * Array with current tree.
     */
    private $tree;

    /**
     * 
     */
    private $is_debug_mode;



    public function __construct(Tree $alTree, array &$tree, &$is_debug_mode = false)
    {
        // Tree object
        $this->alTree = $alTree;
        
        // src_parent
        $this->src_parent = $alTree->getSrcParent();
        
        // top ident
        $this->top_ident = $alTree->getTopIdent();
        
        // Array of tree
        $this->tree = &$tree;
        
        // Options
        $this->is_debug_mode = &$is_debug_mode;
    }
    
    /**
     * Increments the children count for all ancestors of a passed node (node is
     * determined with id).
     * 
     * @param mixed     $id         An id of a node to increment the children count of its ancestors.
     * @param mixed     $parent_id  A parent id of the passed node.
     * @return void
     */
    public function count(&$id, &$parent_id) : void
    {
        // debug (to determine closure, if exists)
        if ($this->is_debug_mode) 
            $debug_dc_rbranch = "[$id]";

        // a reversed branch (to null the 'loop' field)
        $rbranch = [$id];

        // mark node as used in loop (to detect closure, if exists)
        $this->tree[$id]['loop'] = true;

        $prev_dcparent_id = $id;
        $dcparent_id = $parent_id;

        // the descendant counting loop:
        do {
            // verify: did a node output?
            if ($this->tree[$dcparent_id]['loop']) {
                $thisExClass = DescendantCountingException::class;
                
                $exClass = $this->alTree->getExceptionClass($thisExClass);
                
                $msg = 'Descendant counting (in '.__METHOD__.'): probably the ' .
                       'current node (id: '.$prev_dcparent_id.') contains wrong ' .
                        '\'parent\' or its parent (id: '.$dcparent_id.') has wrong \'parent\'. ' .
                        'Node (id: '.$dcparent_id.') ' .
                        'again in loop, it is the closure. Reversed branch: ' . $debug_dc_rbranch;
                
                throw new $exClass($msg);    
            }
            
            // debug (to determine closure, if exists)
            if ($this->is_debug_mode) {
                $debug_dc_rbranch .= ".[$dcparent_id]";
            }

            // increase the descendants count of the parent node
            $this->tree[$dcparent_id]['descendants']++;

            // create a reversed branch (to null the 'loop' field of ancestors)
            $rbranch[] = $dcparent_id;

            // mark the node as used in loop (to determine closure, if exists)
            $this->tree[$dcparent_id]['loop'] = true;

            // save the current parent of the node as a prev parent (ancestor)
            $prev_dcparent_id = $dcparent_id;

            // get a next parent (ancestor) in the branch
            $dcparent_id = $this->tree[$dcparent_id][$this->src_parent];

            // check if a parent is a top node
            if ($dcparent_id == $this->top_ident) {
                // null the 'loop' field
                $this->nullLoop($rbranch);

                // go out from the descendant counting
                break;
            }
        } while(1);
    }
    
    /**
     * Nulls the 'loop' field of a node.
     
     * @param   array $rev_branch An array is containing a branch from current node to its parents (reversed branch).
     * @return  void
     */
    private function nullLoop(array &$rev_branch) : void
    {   
        // REVersed BRANCH
        foreach ($rev_branch as $id) {
            $this->tree[$id]['loop'] = false;
        }
    }
}
