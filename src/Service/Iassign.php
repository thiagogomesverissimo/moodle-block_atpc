<?php

namespace block_atpc\Service;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/course/lib.php');

use block_atpc\Service\Utils;

class Iassign
{

    public static function numberOfTasks($course = 0){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT count(*) AS total FROM {iassign}";

        // filter by course
        if($course != 0) {
            $query .= " WHERE course = {$course}";
        }

        $result = $DB->get_record_sql($query);
        return $result->total;
    }

    public static function numberOfStatements($course = 0){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT count(*) AS total FROM {iassign_statement}";

        // filter by course
        if($course != 0) {
            $query .= " INNER JOIN {iassign} ON {iassign_statement}.iassignid = {iassign}.id
                        WHERE {iassign}.course = {$course}";
        }

        $result = $DB->get_record_sql($query);
        return $result->total;
    }

    // era totalSubmissionsFromStatement
    public static function numberOfSubmissionsFromStatement($statementid){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT userid,
                         COUNT(*) AS total 
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid} 
                         GROUP BY userid";
        $results = json_encode($DB->get_records_sql($query));
        return json_decode($results, true);
    }

    public static function statementsWithSubmissions($course = 0){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT iassign_statementid AS id,
                  COUNT(*) as total 
                  FROM {iassign_allsubmissions}";
        
        // filter by course
        if($course != 0) {
            $query .= " INNER JOIN {iassign_statement} ON {iassign_allsubmissions}.iassign_statementid = {iassign_statement}.id 
                        INNER JOIN {iassign} ON {iassign_statement}.iassignid = {iassign}.id
                        WHERE {iassign}.course = {$course}";
        }

        $query .= " GROUP by {iassign_allsubmissions}.iassign_statementid
                    ORDER BY id ASC";

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

    public static function getStatementCourse($id){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT {iassign}.course
                    FROM {iassign_statement}
                    INNER JOIN {iassign} ON {iassign_statement}.iassignid = {iassign}.id
                    WHERE {iassign_statement}.id = {$id}";
        $result = $DB->get_record_sql($query);
        if($result) return $result->course;
    }

    ######### falta conferir

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

    public static function courses(){
        global $DB, $CFG, $OUTPUT;

        $query = "SELECT UNIQUE(a.course), b.shortname, b.fullname 
                    FROM {iassign} AS a 
                    INNER JOIN {course} AS b 
                    ON a.course = b.id
                    ORDER BY a.course";


        $results = json_encode($DB->get_records_sql($query));
        $array = json_decode($results, true);

        $courses = [];

        foreach($array as $item){
            $courses[$item['course']] = "({$item['course']}) {$item['shortname']} - {$item['fullname']}";
        }

        return $courses;
    }
}
