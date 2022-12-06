<?php

require_once('../../../config.php');
require_once('../classes/Query.php');

use block_tasksummary\Query;

require_login();

$url = new moodle_url("/blocks/tasksummary/pages/statement.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

$page_title = 'Statement';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);


$statementid = required_param('statementid', PARAM_INT);

$users = Query::usersFromStatement($statementid);

$table = [];
foreach($users as $userid){
    $submissions = Query::allSubmissionsFromUserAndStatement($statementid,$userid);
    #var_dump(count($submissions)); die();
    foreach($submissions as &$submission){
        $next = current($submissions);
        if(empty($next)) continue;

        $table[] = [
            'userid'           => $userid,
            'timecreated'      => $submission['timecreated'],
            'timecreated_next' => $next['timecreated'],
            'grade'            => $submission['grade'],
            'grade_next'       => $next['grade'],
            'answer'           => strlen($submission['answer']),
            'answer_next'      => strlen($next['answer'])
        ];
    }
}

echo $OUTPUT->header();

    $content = 'teste'; 
    echo "<pre>"; var_dump($table); die();


  echo $content;
echo $OUTPUT->footer();