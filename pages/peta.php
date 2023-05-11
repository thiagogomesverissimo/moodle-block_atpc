<?php

// default moodle head
require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_login();

// plugin classes
require_once($CFG->dirroot . '/blocks/peta/src/Form/PetaForm.php');
require_once($CFG->dirroot . '/blocks/peta/src/Service/Iassign.php');
require_once($CFG->dirroot . '/blocks/peta/src/Service/Utils.php');
require_once($CFG->dirroot . '/blocks/peta/src/Service/Table.php');
require_once($CFG->dirroot . '/blocks/peta/src/Service/PrepareData.php');
use block_peta\Form\PetaForm;
use block_peta\Service\Iassign;
use block_peta\Service\Utils;
use block_peta\Service\Table;
use block_peta\Service\PrepareData;

// Metadata for moodle page
$url = new moodle_url("/blocks/peta/pages/peta.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$page_title = get_string('pluginname','block_peta');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// form 
$form = new PetaForm();

// data from form
$request = $form->get_data();
if(!empty($request) and !is_null($request)){
  $course = $request->course_id;
} else {
  // zero means all courses
  $course = 0;
}

// statements from Database to DEX
$metrics = PrepareData::courseMetrics($course);
$statementsid = array_column($metrics,'statementid');
$statements = array_map(
  function($statementid) {
    $statementname = Iassign::getStatementName($statementid);
    $course = Iassign::getStatementCourse($statementid);
    return "Course {$course} {$statementname}";
  },$statementsid
);
$statements_dex = implode("', '", $statements);
$dex_normalized_avg = array_column($metrics,'dex_normalized_avg');

$statements = array_map(
  function($statementid) {
    $obj = new StdClass();
    $obj->statementid = $statementid;
    $obj->name = Iassign::getStatementName($statementid);
    $obj->course = Iassign::getStatementCourse($statementid);
    $obj->proposition = Iassign::getStatementProposition($statementid);
    //$obj->value = 
    return $obj;
  },$statementsid
);

$statements_dex_first3 = count($statements) > 6 ? array_slice($statements,0,3): ['','',''];
$statements_dex_last3 = count($statements) > 6 ? array_reverse(array_slice($statements,-3)): ['','',''];

// statements from Database to MTES
$metrics = PrepareData::courseMetrics($course, 0, 'mtes_normalized_avg');
$statementsid = array_column($metrics,'statementid');
$statements = array_map(
  function($statementid) {
    $statementname = Iassign::getStatementName($statementid);
    $course = Iassign::getStatementCourse($statementid);
    return "Course {$course} {$statementname}";
  },$statementsid
);
$statements_mtes = implode("', '", $statements);
$mtes_normalized_avg = array_column($metrics,'mtes_normalized_avg');
$statements = array_map(
  function($statementid) {
    $obj = new StdClass();
    $obj->statementid = $statementid;
    $obj->name = Iassign::getStatementName($statementid);
    $obj->course = Iassign::getStatementCourse($statementid);
    $obj->proposition = Iassign::getStatementProposition($statementid);
    return $obj;
  },$statementsid
);
$statements_mtes_first3 = count($statements) > 6 ? array_slice($statements,0,3): ['','',''];
$statements_mtes_last3 = count($statements) > 6 ? array_reverse(array_slice($statements,-3)): ['','',''];

// statements from Database to MDES
$metrics = PrepareData::courseMetrics($course, 0, 'mdes_normalized_avg');
$statementsid = array_column($metrics,'statementid');
$statements = array_map(
  function($statementid) {
    $statementname = Iassign::getStatementName($statementid);
    $course = Iassign::getStatementCourse($statementid);
    return "Course {$course} {$statementname}";
  },$statementsid
);
$statements_mdes = implode("', '", $statements);
$mdes_normalized_avg = array_column($metrics,'mdes_normalized_avg');
$statements = array_map(
  function($statementid) {
    $obj = new StdClass();
    $obj->statementid = $statementid;
    $obj->name = Iassign::getStatementName($statementid);
    $obj->course = Iassign::getStatementCourse($statementid);
    $obj->proposition = Iassign::getStatementProposition($statementid);
    return $obj;
  },$statementsid
);
$statements_mdes_first3 = count($statements) > 6 ? array_slice($statements,0,3): ['','',''];
$statements_mdes_last3 = count($statements) > 6 ? array_reverse(array_slice($statements,-3)): ['','',''];

// array data sent to template
$data = [
  'statements_dex' => "'$statements_dex'",
  'statements_mdes' => "'$statements_mdes'",
  'statements_mtes' => "'$statements_mtes'",

  'statements_dex_first3' => $statements_dex_first3,
  'statements_dex_last3' => $statements_dex_last3,
  'statements_mtes_first3' => $statements_mtes_first3,
  'statements_mtes_last3' => $statements_mtes_last3,
  'statements_mdes_first3' => $statements_mdes_first3,
  'statements_mdes_last3' => $statements_mdes_last3,

  'dex_normalized_avg' => implode(', ', $dex_normalized_avg),
  'mdes_normalized_avg' => implode(', ', $mdes_normalized_avg),
  'mtes_normalized_avg' => implode(', ', $mtes_normalized_avg),
  'number_of_statements'  => Iassign::numberOfStatements($course),
  'number_of_tasks'       => Iassign::numberOfTasks($course),
  'statements_with_submissions_total' => count(Iassign::statementsWithSubmissions($course)),
  'table' => Table::statements($course),
  'form'  => $form->render()
];

// rendering template
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_peta/peta', $data);
echo $OUTPUT->footer();