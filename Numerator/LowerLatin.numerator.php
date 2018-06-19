<?php
/**
 * NUMBERINGS.
 *
 * For initialize the numbering types array. It is a one of available a numbering types.
 * 
 * Nubmering type "LowerLatin".
 *
 * Example: 
 * a, b, c, ..., y, az, aa, ab, ...
 *
 * Is identical to "UpperLatin" numbering.
*/
return function (int &$offset) : string {
    // Latin alphabet contains 26 charackters
    $en_latin_chars = 26;

    $converted = base_convert($offset, 10, $en_latin_chars);
    $c = strlen($converted);
    $numbering = '';
    for ($i = 0; $i < $c; $i++) {
        // '0' -> 'z'
        if (ord($converted{$i}) == 48) { // ord('0') = 48
            $numbering .= chr(122); // // ord('z') = 122
        // '1'-'9' -> 'a'-'i'
        } elseif (ord($converted{$i}) >= 49 && ord($converted{$i}) <= 57) { // ord('1') = 49 AND ord('a') = 97, ord('9') = 57
            $numbering .= chr(ord($converted{$i}) + 48); // 48 is offset between code of '1' and code of 'a', '2' and 'b', ...
        // 'a'-'p' -> 'j'-'y'
        } elseif (ord($converted{$i}) >= 97 && ord($converted{$i}) <= 112) { // ord('a') = 97 AND ord('j'), ord('p') = 112
            $numbering .= chr(ord($converted{$i}) + 9); // 23 is a negative offset between code of 'a' and code of 'j', 'b' and 'k', ...
        }
    }

    return $numbering; // return symbol
};
