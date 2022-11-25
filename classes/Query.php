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

    public static function allsubmissions(){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT id,iassign_statementid, userid, count(*) FROM {iassign_allsubmissions} group by iassign_statementid, userid";
        $results = $DB->get_records_sql($query);


        // montando uma estrutura assim:
        /*
        [
            'iassign_statementid' => 22,
            'media' => 22,
        ]
        */

        /*foreach($results as $result){
            echo "<pre>";
            //var_dump($result); die();
        }*/

        return $results;
    }
}