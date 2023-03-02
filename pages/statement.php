<?php

require_once($CFG->dirroot . '../vendor/autoload.php');

require_once('../../../config.php');
require_once('../classes/Iassign.php');
require_once('../classes/Utils.php');

use Phpml\Regression\LeastSquares;
use block_atpc\Iassign;
use block_atpc\Utils;
use Carbon\Carbon;

require_login();

$url = new moodle_url("/blocks/atpc/pages/statement.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$statementid = required_param('statementid', PARAM_INT);

$page_title = 'Statement '. $statementid;
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

$users = Iassign::usersFromStatement($statementid);

$lines = [];
foreach($users as $userid){
    $submissions = Iassign::allSubmissionsFromUserAndStatement($statementid,$userid);
    foreach($submissions as $submission){
        $next = next($submissions);
        if(empty($next)) continue;

        $lines[] = [
            'submissions'      => $submission['id'] . '-' . $next['id'],
            'userid'           => $userid,
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
  'userid',
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

    $url = new moodle_url('/blocks/atpc/pages/user.php', [
        'userid' => $row['userid'],
    ]);

    $table->data[] = [
        $row['submissions'],
        "<a href='{$url}'>{$row['userid']}</a>",
        $row['timecreated'],
        $row['timecreated_next'],
        number_format($row['grade'], 2, ',', ''),
        number_format($row['grade_next'], 2, ',', ''),
        $row['answer'],
        $row['answer_next'],
        $difftime,
        $diffanswer
      ];
}
$table->align = ['left','left','right','right','right','right','right','right','right','right'];

// Regressão linear
$x = array_map(function ($x) { return [$x]; }, $array_difftime); $array_difftime;
$y = $array_diffanswer;

$regression = new LeastSquares();
$regression->train($x, $y);
$intercept = $regression->getIntercept();
$coefficient = $regression->getCoefficients()[0];

$data = [
    'difftime'    => implode(',',$array_difftime),
    'diffanswer'  => implode(',',$array_diffanswer),
    'grade_next'  => implode(',',$array_grade),
    'enunciado'   => Iassign::getStatementName($statementid),
    'table'       => html_writer::table($table),
    'intercept'   => number_format($intercept,3),
    'coefficient' => number_format($coefficient,3),
    'max'         => max($array_difftime)
  ];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_atpc/statement', $data);
echo $OUTPUT->footer();
