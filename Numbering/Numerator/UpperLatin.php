<?php
/**
 * NUMBERINGS.
 * For initialize the numbering types array. It is a one of available a numbering types.
 * 
 * Nubmering type "UpperLatin".
 * Example: A, B, C, .., Y, AZ, AA, AB, .. 
 * Let's imagine that it is new number system, which has base of 26. Then
 * 'A' is 1, 'B' is 2, ..., 'Y' is 25 and 'Z' is 0 (zero). So, it is 
 * possible to use native PHP function base_convert to get right
 * representation of every decimal number in this new number system.
 * The only thing what need to do is implement few offsets to get 
 * appropriate digits/symbols for new system:
 * '0' -> 'Z'
 * '1' -> 'A'
 * '2' -> 'B'
 * ...
 * 'a' -> 'J'
 * 'b' -> 'K'
 * ...
 * 'p' -> 'Y'
 */
return function (int &$offset) : string {
    // Latin alphabet contains 26 characters
    $en_latin_chars = 26;

    $converted = base_convert($offset, 10, $en_latin_chars);
    $c = strlen($converted);
    $numbering = '';
    for ($i = 0; $i < $c; $i++) {
        // '0' -> 'Z'
        if (ord($converted{$i}) == 48) {
            $numbering .= chr(90); // 'Z'
        // '1'-'9' -> 'A'-'I'
        } elseif (ord($converted{$i}) >= 49 && ord($converted{$i}) <= 57) { // ord('1') = 49, ord('9') = 57
            $numbering .= chr(ord($converted{$i}) + 16); // 16 is offset between code of '1' and code of 'A', '2' and 'B', ...
        // 'a'-'p' -> 'J'-'Y'
        } elseif (ord($converted{$i}) >= 97 && ord($converted{$i}) <= 112) { // ord('a') = 97, ord('p') = 112
            $numbering .= chr(ord($converted{$i}) - 23); // 23 is a negative offset between code of 'a' and code of 'J', 'b' and 'K', ...
        }
    }

    return $numbering; // return symbol
};
