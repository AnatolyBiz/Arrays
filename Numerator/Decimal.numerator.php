<?php
/**
 * NUMBERINGS.
 *
 * For initialize the numbering types array. It is a one of available a numbering types.
 * 
 * Nubmering type "Decimal".
 *
 * Example: 
 * 1, 2, 3, ..., 'PHP_INT_MAX'
*/
return function (int &$offset) : string {
    // the decimal numbering is used as is (no any computing is required)
    return "$offset"; // return symbol
};
