<?php

require_once($CFG->dirroot . '../vendor/autoload.php');

require_once('../../../config.php');
require_once('../classes/Query.php');
require_once('../classes/Utils.php');

use block_tasksummary\Query;
use block_tasksummary\Utils;
use Carbon\Carbon;

require_login();

$url = new moodle_url("/blocks/tasksummary/pages/user.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$userid = required_param('userid', PARAM_INT);

$page_title = 'Usuário '. $userid;
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

$statements = Query::statementsFromUser($userid);

$lines = [];
foreach($statements as $statement){

    $submissions = Query::allSubmissionsFromUserAndStatement($statement,$userid);

    foreach($submissions as $submission){
        $next = next($submissions);
        if(empty($next)) continue;

        $lines[] = [
            'submissions'      => $submission['id'] . '-' . $next['id'],
            'statement'        => $statement,
            'enunciado'        => Query::getStatementName($statement),
            'timecreated'      => Carbon::createFromTimestamp($submission['timecreated']),
            'timecreated_next' => Carbon::createFromTimestamp($next['timecreated']),
            'grade'            => $submission['grade'],
            'grade_next'       => $next['grade'],
            'answer'           => strlen($submission['answer']),
            'answer_next'      => strlen($next['answer'])
        ];
    }
}

$table = new html_table();

$table->head = [ 
  'submissions',
  'statement',
  'enunciado',
  'timecreated',
  'timecreated_next',
  'grade',
  'grade_next',
  'answer',
  'answer_next',
  'diff sec',
  'diff answer'
];

$array_difftime = [];
$array_diffanswer = [];
$array_grade = [];

foreach($lines as $row){
    $difftime = $row['timecreated']->diffInSeconds($row['timecreated_next']);
    $diffanswer =  $row['answer_next']-$row['answer'];
   
    $array_difftime[] = Utils::scaleWithLn($difftime);
    $array_diffanswer[] = Utils::scaleWithLn($diffanswer);
    $array_grade[] = $row['grade_next'];

    $url = new moodle_url('/blocks/tasksummary/pages/statement.php', [
        'statementid' => $row['statement'],
    ]);

    $table->data[] = [
        $row['submissions'],
        "<a href='$url'>{$row['statement']}</a>",
        $row['enunciado'],
        $row['timecreated'],
        $row['timecreated_next'],
        $row['grade'],
        $row['grade_next'],
        $row['answer'],
        $row['answer_next'],
        $difftime,
        $diffanswer
      ];
}


$data = [
    'difftime'   => implode(',',$array_difftime),
    'diffanswer' => implode(',',$array_diffanswer),
    'grade_next' => implode(',',$array_grade),
    'userid'     => $userid,
    'table'      => html_writer::table($table)
  ];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_tasksummary/statement', $data);
echo $OUTPUT->footer();
