<?php

require_once('../../../config.php');
require_once('../classes/Query.php');
require_once('../classes/Utils.php');

use block_tasksummary\Utils;
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

$table = new html_table();

$table->head = [ 
  'statement id',
  'Enunciado',
  'Quantidade de submissões',
  'Quantidade de usuários',
  'Média de submissões por usuários',
  'Mediana',
  'Máximo de submissões por um único usuário'
];

foreach($statements_with_submissions as $statement){

  $url = new moodle_url('/blocks/tasksummary/pages/statement.php', [
      'statementid' => $statement->id,
  ]);

  $submissions = Query::totalSubmissionsFromStatement($statement->id);

  // usuário com maior número de submissões
  $max = max(array_column($submissions, 'total'));
  $mediana = Utils::median(array_column($submissions, 'total'));

  $enunciado = Query::getStatementName($statement->id);

  $n = count($submissions);
  $media = number_format((float) $statement->total/$n, 2, ',', '');

  $table->data[] = [
    "<a href='{$url}'>{$statement->id}</a>",
    $enunciado,
    $statement->total,
    $n,
    $media,
    $mediana,
    $max
  ];
}
$table->align = ['left','left','right','right','right','right','right'];

$data = [
  'statements'  => $statements,
  'tasks'       => $tasks,
  'statements_with_submissions_total' => $statements_with_submissions_total,
  'table' => html_writer::table($table)
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_tasksummary/tasksummary', $data);
echo $OUTPUT->footer();