<?php
/**
 * Splitters: '{{' and '}}' (in fact, they can be any else You will set, for example '[[' & ']]', or '\\' & '//').
 * If start and end separator written together - '{{}}', it means they 
 * are a splitter of element (wrapper, block, item) for start and end an element part.
 * If they written around one indivisible word, it means they are a replacement and
 * word among them is a column name of $tree array (while tree will be outputting
 * that expression will replace with column value) or a computing expression.
 */
return [
    'replacing_pattern' => '[a-zA-Z0-9%_][a-zA-Z0-9%_]*', // [allowed first symbol][and next allowed symbols]*
];
