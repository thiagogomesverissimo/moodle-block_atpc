<?php

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '../vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;

// protecting the page
require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_login();

// plugin classes
require_once('../src/Service/Iassign.php');
require_once('../src/Service/Utils.php');
require_once('../src/Service/Table.php');
require_once('../src/Service/PrepareData.php');
use block_atpc\Service\Iassign;
use block_atpc\Service\Utils;
use block_atpc\Service\Table;
use block_atpc\Service\PrepareData;

// params received
$statementid = required_param('statementid', PARAM_INT);

// Metadata for moodle page
$url = new moodle_url("/blocks/atpc/pages/statement.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$page_title = 'Statement '. $statementid; // TODO: internationalization
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);



// Regressão linear
//$x = array_map(function ($x) { return [$x]; }, $array_difftime); $array_difftime;
//$y = $array_diffanswer;

/*
$regression = new LeastSquares();
$regression->train($x, $y);
$intercept = $regression->getIntercept();
$coefficient = $regression->getCoefficients()[0];
*/

$data = [
//    'difftime'    => implode(',',$array_difftime),
//    'diffanswer'  => implode(',',$array_diffanswer),
//    'grade_next'  => implode(',',$array_grade),
    'enunciado'   => Iassign::getStatementName($statementid),
    'table'       => Table::statement($statementid),
    'intercept'   => 1, #number_format($intercept,3),
    'coefficient' => 1, #number_format($coefficient,3),
//    'max'         => max($array_difftime)
  ];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_atpc/statement', $data);
echo $OUTPUT->footer();
