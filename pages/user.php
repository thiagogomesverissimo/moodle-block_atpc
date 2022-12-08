<?php

require_once($CFG->dirroot . '../vendor/autoload.php');

require_once('../../../config.php');
require_once('../classes/Query.php');
require_once('../classes/Utils.php');

use block_tasksummary\Query;
use block_tasksummary\Utils;
use Carbon\Carbon;

require_login();

$userid = required_param('userid', PARAM_INT);

$page_title = 'Usuário '. $userid;
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

$statements = Query::statementsFromUser($userid);



$table = [];
foreach($statements as $statement){

    $submissions = Query::allSubmissionsFromUserAndStatement($statement,$userid);

    foreach($submissions as $submission){
        $next = next($submissions);
        if(empty($next)) continue;

        $table[] = [
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

$content = file_get_contents("../templates/user_page.php");

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

    $url = new moodle_url('/blocks/tasksummary/pages/statement.php', [
        'statementid' => $row['statement'],
    ]);

    $trs .= "
        <tr>
            <td>{$row['submissions']}</td>
            <td><a href='$url'>{$row['statement']}</a></td>
            <td>{$row['enunciado']}</td>
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

$content = str_replace('{{trs}}',$trs, $content);
$content = str_replace('{{difftime}}',implode(',',$array_difftime), $content);
$content = str_replace('{{diffanswer}}',implode(',',$array_diffanswer), $content);
$content = str_replace('{{grade_next}}',implode(',',$array_grade), $content);
$content = str_replace('{{userid}}',$userid, $content);

echo $OUTPUT->header();
echo $content;
echo $OUTPUT->footer();
