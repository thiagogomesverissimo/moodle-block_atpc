<?php

require_once('../../../config.php');
require_once('../classes/Query.php');

use block_tasksummary\Query;

$url = new moodle_url("/blocks/tasksummary/pages/tasksummary.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

$page_title = 'Plugin de Análise de Dados do Itarefas';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

require_login();

$tasks = Query::tasks();
$statements = Query::statements();

$allsubmissions = Query::allsubmissions();

//echo "<pre>";
//var_dump($allsubmissions); die();


echo $OUTPUT->header();
  $content = file_get_contents("../templates/tasksummary_page.php");

  $content = str_replace('{{statements}}',$statements, $content);
  $content = str_replace('{{tasks}}',$tasks, $content);

  echo $content;
echo $OUTPUT->footer();