<?php

// protecting the page
require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_login();

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '/blocks/atpc/vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;

// plugin classes
require_once($CFG->dirroot . '/blocks/atpc/src/Service/Iassign.php');
require_once($CFG->dirroot . '/blocks/atpc/src/Service/Utils.php');
require_once($CFG->dirroot . '/blocks/atpc/src/Service/Table.php');
require_once($CFG->dirroot . '/blocks/atpc/src/Service/PrepareData.php');
use block_atpc\Service\Iassign;
use block_atpc\Service\Utils;
use block_atpc\Service\Table;
use block_atpc\Service\PrepareData;

// required params from request
$statementid = required_param('statementid', PARAM_INT);

// Metadata for moodle page
$url = new moodle_url("/blocks/atpc/pages/statement.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$page_title = 'Statement '. $statementid; // TODO: internationalization
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// x e y para regressão linear
$table = PrepareData::statement($statementid);
$xy = Utils::filterArrayByKeys($table, ['difftime_ln','diffanswer_ln']);
$x =  array_map(function ($array) { return [$array['difftime_ln']]; }, $xy);
$y = array_map(function ($array) { return $array['diffanswer_ln']; }, $xy);

// Regressão linear
$regression = new LeastSquares();
$regression->train($x, $y);
$intercept = $regression->getIntercept();
$coefficient = $regression->getCoefficients()[0];
die($intercept);

$data = [
//    'difftime'    => implode(',',$array_difftime),
    'diffanswer'  => implode(',',$y),
//    'grade_next'  => implode(',',$array_grade),
    'enunciado'   => Iassign::getStatementName($statementid),
    'table'       => Table::statement($statementid),
    'intercept'   => number_format($intercept,3),
    'coefficient' => number_format($coefficient,3),
//    'max'         => max($array_difftime)
  ];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_atpc/statement', $data);
echo $OUTPUT->footer();
