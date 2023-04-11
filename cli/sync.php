<?php

define('CLI_SCRIPT', true);
require(__DIR__.'/../../../config.php');
defined('MOODLE_INTERNAL') || die();

// plugin classes
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Iassign.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Utils.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Table.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/PrepareData.php');
use block_itpc\Service\Iassign;
use block_itpc\Service\Utils;
use block_itpc\Service\Table;
use block_itpc\Service\PrepareData;

global $DB;

$courses = Iassign::courses();
foreach($courses as $course=>$courseinfo){

    $statements = Iassign::statementsWithSubmissions($course);

    foreach($statements as $statement){

        $metrics = PrepareData::statementAnalysis($statement->id);
        
        $metrics_as_objects = array_map(
            function($metric) use ($statement, $course) {

                $statementtext = Iassign::getStatementName($statement->id);
                $submissionsbyuser = Iassign::numberOfSubmissionsByUser($statement->id);
                $numberofusers = count($submissionsbyuser);
                $numberofsubmissions = $statement->total;
                $avgsubmissionsbyuser = (float) $numberofsubmissions/$numberofusers;

                $max = max(array_column($submissionsbyuser, 'total'));
                $median = Utils::median(array_column($submissionsbyuser, 'total'));

                $obj = new stdClass;
                $obj->courseid = $course;
                $obj->statementid = $statement->id;
                $obj->statement = $statementtext;
                $obj->numberofusers = $numberofusers;
                $obj->numberofsubmissions = $numberofsubmissions;
                $obj->avgsubmissionsbyuser = $avgsubmissionsbyuser;
                $obj->max = $max;
                $obj->median = $median;
                $obj->userid = $metric['userid'];
                $obj->mtes = $metric['mtes'];
                $obj->mdes = $metric['mdes'];
                $obj->dex  = $metric['dex'];
                $obj->mtes_normalized = $metric['mtes_normalized'];
                $obj->mdes_normalized = $metric['mdes_normalized'];
                $obj->dex_normalized  = $metric['dex_normalized'];
                return $obj;
            }, 
            $metrics
        );

        $DB->delete_records('block_itpc_statement_metrics',[ 'statementid' => $statement->id, 'courseid' => $course ]);
        $DB->insert_records('block_itpc_statement_metrics', $metrics_as_objects);
    }
}





