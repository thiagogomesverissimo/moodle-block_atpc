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
// it is better to update
$DB->delete_records('block_itpc_statement_metrics');

$courses = Iassign::courses();
foreach($courses as $course){
    $statements = Iassign::statementsWithSubmissions($course);

    foreach($statements as $statement){

        $statementid = $statement->id;
        $metrics = PrepareData::statementAnalysis($statementid);
        $metrics_as_objects = array_map(
            function($metric) use ($statementid) {
                $obj = new stdClass;
                $obj->statementid = $statementid;
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
        $DB->insert_records('block_itpc_statement_metrics', $metrics_as_objects);
    
    }
}





