<?php

require_once('../../../config.php');

$url = new moodle_url("/blocks/tasksummary/pages/tasksummary.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

require_login();

$title = get_string('pluginname', 'block_tasksummary');
$page_title = 'Aqui teremos muitas coisas legais de data science e educação';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

echo $OUTPUT->header();
echo $OUTPUT->footer();