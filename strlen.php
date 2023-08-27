<?php
/**
 * Temporary strlen for PHP 8.2 before and after
 *
 * Replace wuth U::strlen() after 8.2 has been the norm for a while.
 */
function U__strlen($string) {
    if ( !isset($string) ) return 0;
    if ( $string === true ) return 0; // Depart from PHP on this one
    if ( $string === false ) return 0;
    if ( is_numeric($string) ) $string = $string . '';
    if ( $string instanceof Stringable ) $string = $string . '';
    if ( !is_string($string) ) return 0;
    return strlen($string);
}


