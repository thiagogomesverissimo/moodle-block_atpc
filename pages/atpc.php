<?php

// default moodle head
require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_login();

// plugin classes
require_once('../src/Form/AtpcForm.php');
require_once('../src/Service/Iassign.php');
require_once('../src/Service/Utils.php');
require_once('../src/Service/Table.php');
use block_atpc\Form\AtpcForm;
use block_atpc\Service\Utils;
use block_atpc\Service\Iassign;
use block_atpc\Service\Table;

// Metadata for moodle page
$url = new moodle_url("/blocks/atpc/pages/atpc.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$page_title = 'Plugin de Análise de Dados do Itarefas'; // TODO: internationalization
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// statements data from tables
$statements = Iassign::statements();
$statements_with_submissions = Iassign::statementsWithSubmissions();

// form 
$form = new AtpcForm(['users', 'courses']);

// array data sent to template
$data = [
  'statements'  => $statements,
  'tasks'       => Iassign::tasks(),
  'statements_with_submissions_total' => count($statements_with_submissions),
  'table' => Table::statements(),
  'form'  => $form->render()
];

// rendering template
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_atpc/atpc', $data);
echo $OUTPUT->footer();