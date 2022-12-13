<?php

require_once($CFG->dirroot . '../vendor/autoload.php');

require_once('../../../config.php');
require_once('../classes/Query.php');
require_once('../classes/Utils.php');

use block_tasksummary\Query;
use block_tasksummary\Utils;
use Carbon\Carbon;

require_login();

# não é necessário?
#$url = new moodle_url("/blocks/tasksummary/pages/statement.php");
#$PAGE->set_url($url);
#$PAGE->set_context(context_system::instance());
#$PAGE->set_pagelayout('admin');

$statementid = required_param('statementid', PARAM_INT);

$page_title = 'Statement '. $statementid;
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

$users = Query::usersFromStatement($statementid);

$table = [];
foreach($users as $userid){
    $submissions = Query::allSubmissionsFromUserAndStatement($statementid,$userid);
    #var_dump(count($submissions)); die();
    foreach($submissions as $submission){
        $next = next($submissions);
        if(empty($next)) continue;

        $table[] = [
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

$content = file_get_contents("../templates/statement_page.php");

$trs = '';
$array_difftime = [];
$array_diffanswer = [];
$array_grade = [];

foreach($table as $row){
    $difftime = $row['timecreated']->diffInSeconds($row['timecreated_next']);
    $diffanswer =  $row['answer_next']-$row['answer'];

    $array_difftime[] = Utils::scaleWithLn($difftime);
    $array_diffanswer[] = Utils::scaleWithLn($diffanswer);
    $array_grade[] = $row['grade_next'];

    $url = new moodle_url('/blocks/tasksummary/pages/user.php', [
        'userid' => $row['userid'],
    ]);

    $trs .= "
        <tr>
            <td>{$row['submissions']}</td>
            <td><a href='{$url}'>{$row['userid']}</a></td>
            <td>{$row['timecreated']}</td>
            <td>{$row['timecreated_next']}</td>
            <td>{$row['grade']}</td>
            <td>{$row['grade_next']}</td>
            <td>{$row['answer']}</td>
            <td>{$row['answer_next']}</td>
            <td>{$difftime}</td>
            <td>{$diffanswer}</td>
        </tr>
    ";
}

$enunciado = Query::getStatementName($statementid);

$array_grade2 = array_fill(0,count($array_grade),100);

$content = str_replace('{{trs}}',$trs, $content);
$content = str_replace('{{difftime}}',implode(',',$array_difftime), $content);
$content = str_replace('{{diffanswer}}',implode(',',$array_diffanswer), $content);
$content = str_replace('{{grade_next}}',implode(',',$array_grade), $content);
$content = str_replace('{{enunciado}}',$enunciado, $content);

echo $OUTPUT->header();
echo $content;
echo $OUTPUT->footer();
