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

    public static function totalSubmissionsFromStatement($statementid){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT userid,
                         COUNT(*) AS total 
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid} 
                         GROUP BY userid";
        $results = json_encode($DB->get_records_sql($query));
        return json_decode($results, true);
    }

    public static function allSubmissionsFromUserAndStatement($statementid, $userid){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT *
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid}
                         AND userid  = {$userid}";
        $results = json_encode($DB->get_records_sql($query));
        return json_decode($results, true);
    }

    public static function usersFromStatement($statementid){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT UNIQUE(userid)
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid}
                         GROUP BY userid";
        $results = json_encode($DB->get_records_sql($query));
        $array = json_decode($results, true);
        return array_column($array,'userid');
    }

    public static function statementsFromUser($userid){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT UNIQUE(iassign_statementid)
                         FROM {iassign_allsubmissions}
                         WHERE userid = {$userid}
                         GROUP BY iassign_statementid";
        $results = json_encode($DB->get_records_sql($query));
        $array = json_decode($results, true);
        return array_column($array,'iassign_statementid');
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

    public static function getStatementName($id){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT name
                    FROM {iassign_statement}
                    WHERE id = {$id}";
        $result = $DB->get_record_sql($query);
        if($result) return $result->name;
    }
}