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

// statementFromDatabase
$metrics = PrepareData::courseMetrics(472);

$statementsid = array_column($metrics,'statementid');

$statements = array_map(
  function($statementid) {
    return Iassign::getStatementName($statementid);
  },$statementsid
);

$dex_normalized_avg = array_column($metrics,'dex_normalized_avg');
$mdes_normalized_avg = array_column($metrics,'mdes_normalized_avg');
$mtes_normalized_avg = array_column($metrics,'mtes_normalized_avg');

//var_dump(implode("', '", $statements));die();
//$diffanswer = Utils::filterArrayByKeys($rows, ['diffanswer']);
//$diffanswer = array_column($diffanswer,'diffanswer');

// array data sent to template
$data = [
  'statements' => implode("', '", $statements),
  'dex_normalized_avg' => implode(', ', $dex_normalized_avg),
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