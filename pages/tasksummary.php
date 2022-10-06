<?php

require_once('../../../config.php');
require_once('../classes/Query.php');

use block_tasksummary\Query;

$url = new moodle_url("/blocks/tasksummary/pages/tasksummary.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

require_login();

$iassign_module = Query::teste();
print_r($iassign_module);
die();

$title = get_string('pluginname', 'block_tasksummary');
$page_title = 'Visão geral dos dados coletados do IAssign:';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

echo $OUTPUT->header();
  $content = file_get_contents("../templates/tasksummary_page.php");
  echo $content;
echo $OUTPUT->footer();