<?php

namespace block_tasksummary;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/course/lib.php');

/*$query = "SELECT * FROM {iassign_allsubmissions} GROUP BY iassign_statementid AND userid";
$statements = $DB->get_records_sql($query);
return $statements;

die();

$str_query = "SELECT id, name, visible FROM {modules} WHERE upper(name) = 'IASSIGN'";
return $DB->get_records_sql($str_query);
*/

class Query
{

    public static function tasks(){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT count(*) AS total FROM {iassign}";
        $result = $DB->get_record_sql($query);
        return $result->total;
    }

    public static function statements(){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT count(*) AS total FROM {iassign_statement}";
        $result = $DB->get_record_sql($query);
        return $result->total;
    }


    public static function totalSubmissionsFromStatment($statementid){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT userid,
                         COUNT(*) AS total 
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid} 
                         GROUP BY userid";
        $results = json_encode($DB->get_records_sql($query));
        return json_decode($results, true);
        # echo "<pre>";
        # var_dump(array_sum(array_column($results,'total'))); die();
    }

    public static function statementsWithSubmissions(){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT iassign_statementid AS id,
                  COUNT(*) as total 
                  FROM {iassign_allsubmissions} 
                  GROUP by iassign_statementid
                  ORDER BY total DESC";
        
        $results = $DB->get_records_sql($query);
        return $results;
    }

    // https://www.folkstalk.com/2022/09/php-median-with-code-examples.html
    public static function median($a) { 
        sort($a);
        $c = count($a);
        $m = floor(($c-1)/2);
        return ($c % 2) ? $a[$m] : (($a[$m]+$a[$m+1])/2);
    }
    
    
}