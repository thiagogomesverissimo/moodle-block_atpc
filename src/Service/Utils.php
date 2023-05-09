<?php

namespace block_peta\Service;

require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();

class Utils
{
    public static function scaleWithLn($x) { 
        if($x == 0) return $x;

        if($x < 0) return log(abs($x));
        
        return log($x);
    }

    // https://www.folkstalk.com/2022/09/php-median-with-code-examples.html
    public static function median($a) { 
        sort($a);
        $c = count($a);
        $m = floor(($c-1)/2);
        return ($c % 2) ? $a[$m] : (($a[$m]+$a[$m+1])/2);
    }

    // https://stackoverflow.com/questions/55811947/get-multi-column-from-array-php-alternate-of-array-column
    public static function filterArrayByKeys(array $input, array $column_keys)
    {
        $result      = array();
        $column_keys = array_flip($column_keys); // getting keys as values
        foreach ($input as $key => $val) {
            // getting only those key value pairs, which matches $column_keys
            $result[$key] = array_intersect_key($val, $column_keys);
        }
        return $result;
    }  
}
