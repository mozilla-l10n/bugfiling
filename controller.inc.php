<?php
include_once 'langconfig.php';

// Switch for the bugzilla installation URL
// Without this one the script ain't going to work
// we fetch config.cgi based on this one
// also, we use it to create bugs on referring installation
//~ $bugzilla_url = 'https://landfill.bugzilla.org/bugzilla-3.6-branch/';
//~ $bugzilla_url = 'https://landfill.bugzilla.org/bugzilla-4.0-branch/';
$bugzilla_url = 'https://bugzilla.mozilla.org/';

// This function explodes a string for a given separator
// and removes whitespaces from created array elements
function clean_explode($input, $separator) {
    $input = trim(preg_replace('|\\s*(?:' . preg_quote($separator) . ')\\s*|', $separator, $input));
    return explode($separator, $input);
}
