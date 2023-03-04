<?php

namespace block_atpc\Service;

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
}
