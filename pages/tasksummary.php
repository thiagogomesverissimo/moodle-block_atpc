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

$statements_with_submissions = Query::statementsWithSubmissions();
$statements_with_submissions_total = count($statements_with_submissions);

echo $OUTPUT->header();
  $content = file_get_contents("../templates/tasksummary_page.php");

  $content = str_replace('{{statements}}',$statements, $content);
  $content = str_replace('{{tasks}}',$tasks, $content);
  $content = str_replace('{{statements_with_submissions_total}}',$statements_with_submissions_total, $content);

  $trs = '';
  foreach($statements_with_submissions as $statement){

    $url = new moodle_url('/blocks/tasksummary/pages/statement.php', [
        'statementid' => $statement->id,
    ]);

    $submissions = Query::totalSubmissionsFromStatement($statement->id);

    // usuário com maior número de submissões
    $max = max(array_column($submissions, 'total'));
    $mediana = Query::median(array_column($submissions, 'total'));

    $n = count($submissions);
    $media = number_format((float) $statement->total/$n, 2, ',', '');
    $trs .= "<tr>
    <td><a href='{$url}'>{$statement->id}</a></td>
    <td>{$statement->total}</td>
    <td>{$n}</td>
    <td>{$media}</td>
    <td>{$mediana}</td>
    <td>{$max}</td>
    <td></td>
    </tr>
    ";
  }
  $content = str_replace('{{trs}}',$trs, $content);
  
  echo $content;
echo $OUTPUT->footer();