<?php
/**
  * View (default settings).
  * 
  * Example of a struct elements: 'wrapper', 'block', 'item' and 'content':
  *      <nav>                                    'wrapper'          a tag that is wrapper for all tree
  *          <ul>                                 'block'            (can contains 'item')
  *              <li>                             'item'             (can contains 'content' and another 'block', or can be just only wrapper for 'content')
  *                  <a href="#"></a>             'content'          (only in 'item')
  *                  <ul>                         'block' in 'item'  (only after 'content')
  *                      <li>                     ...
  *                          <a href=""></a>
  *                      </li>
  *                  </ul>
  *              </li>
  *          </ul>
  *      </nav>
  * Splitters: '{{' and '}}' (in fact, they can be any else You will set, for example '[[' & ']]', or '\\' & '//').
  * If start and end separator written together - '{{}}', it means they 
  * are a splitter of element (wrapper, block, item) for start and end an element part.
  * If they written around one indivisible word, it means they are a replacement and
  * word among them is a column name of $tree array (while tree will be outputting
  * that expression will replace with column value) or a computing expression.
  */
return [
    'view' => [
        'splitter' => [
            'start' => '{{', 
            'end'   => '}}'
        ],
        'wrapper' => '<div>{{}}</div>',
        'level' => [
            'block'     => '<ul data-level="{{level}}">{{}}</ul>',
            'item'      => '<li data-next="{{next}}">{{}}</li>',
            'content'   => '<span>{{}}</span>'
        ]
    ]
];
