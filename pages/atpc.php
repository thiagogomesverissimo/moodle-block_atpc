<?php

// default moodle head
require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_login();

// plugin classes
require_once($CFG->dirroot . '/blocks/atpc/src/Form/AtpcForm.php');
require_once($CFG->dirroot . '/blocks/atpc/src/Service/Iassign.php');
require_once($CFG->dirroot . '/blocks/atpc/src/Service/Utils.php');
require_once($CFG->dirroot . '/blocks/atpc/src/Service/Table.php');
use block_atpc\Form\AtpcForm;
use block_atpc\Service\Iassign;
use block_atpc\Service\Utils;
use block_atpc\Service\Table;

// Metadata for moodle page
$url = new moodle_url("/blocks/atpc/pages/atpc.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$page_title = 'Plugin de Análise de Dados do Itarefas'; // TODO: internationalization
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// form 
$form = new AtpcForm();

// data from form
$request = $form->get_data();
if(!empty($request) and !is_null($request)){
  $course = $request->course_id;
} else {
  // zero means all courses
  $course = 0;
}

// array data sent to template
$data = [
  'number_of_statements'  => Iassign::numberOfStatements($course),
  'number_of_tasks'       => Iassign::numberOfTasks($course),
  'statements_with_submissions_total' => count(Iassign::statementsWithSubmissions($course)),
  'table' => Table::statements($course),
  'form'  => $form->render()
];

// rendering template
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_atpc/atpc', $data);
echo $OUTPUT->footer();