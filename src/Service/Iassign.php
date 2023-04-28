<?php

namespace block_itpc\Service;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/course/lib.php');

use block_itpc\Service\Utils;

class Iassign
{

    public static function numberOfTasks($course = 0){
        global $DB;

        $query = "SELECT count(*) AS total FROM {iassign}";

        // filter by course
        if($course != 0) {
            $query .= " WHERE course = {$course}";
        }

        $result = $DB->get_record_sql($query);
        return $result->total;
    }

    public static function numberOfStatements($course = 0){
        global $DB;

        $query = "SELECT count(*) AS total FROM {iassign_statement}";

        // filter by course
        if($course != 0) {
            $query .= " INNER JOIN {iassign} 
                          ON {iassign_statement}.iassignid = {iassign}.id
                        WHERE {iassign}.course = {$course}";
        }

        $result = $DB->get_record_sql($query);
        return $result->total;
    }

    /**
     *  return a array of arrays like:
     * [
     *   [ "userid" => 9693, "total" =>  2],
     *   [ "userid" => 9694, "total" =>  8],
     * ]
     **/
    public static function numberOfSubmissionsGroupedByUser($statementid){
        global $DB;

        $query = "SELECT userid,
                         COUNT(*) AS total 
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid} 
                         GROUP BY userid";
        $results = json_encode($DB->get_records_sql($query));
        $array = json_decode($results, true);
        return $array;
    }

    public static function numberOfSubmissionsByUser($statementid, $userid){
        global $DB;

        $query = "SELECT COUNT(*) AS total 
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid} 
                           AND userid = {$userid}";
        $obj = $DB->get_record_sql($query);
        if($obj) return $obj->total;
        return 0;
    }

    // só retorna exercícios com ao menos 10 submissões
    public static function statementsWithSubmissions($course = 0){
        global $DB;

        $query = "SELECT iassign_statementid AS id,
                  COUNT(*) as total 
                  FROM {iassign_allsubmissions}";
        
        // filter by course
        if($course != 0) {
            $query .= " INNER JOIN {iassign_statement} 
                            ON {iassign_allsubmissions}.iassign_statementid = {iassign_statement}.id 
                        INNER JOIN {iassign} 
                            ON {iassign_statement}.iassignid = {iassign}.id
                        WHERE {iassign}.course = {$course}";
        }

        $query .= " GROUP by {iassign_allsubmissions}.iassign_statementid
                    HAVING total > 10
                    ORDER BY id ASC";

        $results = $DB->get_records_sql($query);
        return $results;
    }

    public static function getStatementName($id){
        global $DB;

        $query = "SELECT name
                    FROM {iassign_statement}
                    WHERE id = {$id}";
        $result = $DB->get_record_sql($query);
        if($result) return $result->name;
        return '';
    }

    public static function getStatementProposition($id){
        global $DB;

        $query = "SELECT proposition
                    FROM {iassign_statement}
                    WHERE id = {$id}";
        $result = $DB->get_record_sql($query);
        if($result) return $result->proposition;
        return '';
    }

    public static function getStatementCourse($id){
        global $DB;

        $query = "SELECT {iassign}.course
                    FROM {iassign_statement}
                    INNER JOIN {iassign} ON {iassign_statement}.iassignid = {iassign}.id
                    WHERE {iassign_statement}.id = {$id}";
        $result = $DB->get_record_sql($query);
        if($result) return $result->course;
    }

    public static function courses(){
        global $DB;

        // filtering only statements (and courses) with at least 10 submissions
        $statement_ids = "SELECT iassign_statementid
                    FROM {iassign_allsubmissions} 
                    GROUP BY iassign_statementid 
                    HAVING COUNT(*) > 10";

        $iassign_ids = "SELECT iassignid FROM {iassign_statement} WHERE id IN ({$statement_ids})";

        $query = "SELECT UNIQUE({iassign}.course), 
                         {course}.shortname, 
                         {course}.fullname 
                    FROM {iassign} 
                    INNER JOIN {course} ON {iassign}.course = {course}.id
                    WHERE {iassign}.id IN ({$iassign_ids})
                    ORDER BY {iassign}.course";

        $results = json_encode($DB->get_records_sql($query));
        $array = json_decode($results, true);

        $courses = [];

        foreach($array as $item){
            $courses[$item['course']] = "({$item['course']}) {$item['shortname']} - {$item['fullname']}";
        }

        return $courses;
    }
    
    ######### falta conferir

    public static function allSubmissionsFromUserAndStatement($statementid, $userid){
        global $DB;

        $query = "SELECT *
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid}
                         AND grade >= 0
                         AND userid  = {$userid}";
        $results = json_encode($DB->get_records_sql($query));
        return json_decode($results, true);
    }

    public static function usersFromStatement($statementid){
        global $DB;

        $query = "SELECT UNIQUE(userid)
                         FROM {iassign_allsubmissions}
                         WHERE iassign_statementid = {$statementid}
                         AND grade >= 0
                         GROUP BY userid";
        $results = json_encode($DB->get_records_sql($query));
        $array = json_decode($results, true);
        return array_column($array,'userid');
    }

    public static function statementsFromUser($userid){
        global $DB;

        $query = "SELECT UNIQUE(iassign_statementid)
                         FROM {iassign_allsubmissions}
                         WHERE userid = {$userid}
                         GROUP BY iassign_statementid";
        $results = json_encode($DB->get_records_sql($query));
        $array = json_decode($results, true);
        return array_column($array,'iassign_statementid');
    }



    public static function getUserName($userid){
        global $DB;

        $user = $DB->get_record("user", ["id" => $userid]);
        return "{$user->firstname} {$user->lastname}";
    }
}
