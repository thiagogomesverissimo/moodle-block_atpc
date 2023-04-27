<?php

// default moodle head
require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_login();

// plugin classes
require_once($CFG->dirroot . '/blocks/itpc/src/Form/ItpcForm.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Iassign.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Utils.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Table.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/PrepareData.php');
use block_itpc\Form\ItpcForm;
use block_itpc\Service\Iassign;
use block_itpc\Service\Utils;
use block_itpc\Service\Table;
use block_itpc\Service\PrepareData;

// Metadata for moodle page
$url = new moodle_url("/blocks/itpc/pages/itpc.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$page_title = 'Intelligent tutor'; // TODO: internationalization
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// form 
$form = new ItpcForm();

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
$statements_dex_first3 = count($statements) > 6 ? array_slice($statements,0,3): ['','',''];
$statements_dex_last3 = count($statements) > 6 ? array_slice($statements,-3): ['','',''];

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

// array data sent to template
$data = [
  'statements_dex' => "'$statements_dex'",
  'statements_mdes' => "'$statements_mdes'",
  'statements_mtes' => "'$statements_mtes'",
  'statements_dex_first3' => $statements_dex_first3,
  'statements_dex_last3' => $statements_dex_last3,
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
echo $OUTPUT->render_from_template('block_itpc/itpc', $data);
echo $OUTPUT->footer();