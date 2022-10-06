<?php

namespace block_tasksummary;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/course/lib.php');

class Query
{
    public static function teste(){
        global $DB, $CFG, $OUTPUT;

        // Pegando todas atividades criadas no itarefas
        $query = "SELECT id FROM {iassign_statement}";
        $statements = $DB->get_records_sql($query);


        $query = "SELECT * FROM {iassign_allsubmissions} GROUP BY iassign_statementid AND userid";
        $statements = $DB->get_records_sql($query);
        return $statements;

        die();

        $str_query = "SELECT id, name, visible FROM {modules} WHERE upper(name) = 'IASSIGN'";
        return $DB->get_records_sql($str_query);
    }
}