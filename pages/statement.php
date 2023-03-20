<?php

// protecting the page
require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_login();

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '/blocks/itpc/vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;

// plugin classes
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Iassign.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Utils.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/Table.php');
require_once($CFG->dirroot . '/blocks/itpc/src/Service/PrepareData.php');
use block_itpc\Service\Iassign;
use block_itpc\Service\Utils;
use block_itpc\Service\Table;
use block_itpc\Service\PrepareData;

// required params from request
$statementid = required_param('statementid', PARAM_INT);

//Table::statementDex($statementid);

// Metadata for moodle page
$url = new moodle_url("/blocks/itpc/pages/statement.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$page_title = 'Statement '. $statementid; // TODO: internationalization
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// preparando x e y para regressão linear
$data = PrepareData::statement($statementid);
$xy = Utils::filterArrayByKeys($data, ['difftime_ln','diffanswer_ln']);
$x =  array_map(function ($array) { return [$array['difftime_ln']]; }, $xy);
$y = array_map(function ($array) { return $array['diffanswer_ln']; }, $xy);

// Regressão linear
$regression = new LeastSquares();
$regression->train($x, $y);
$intercept = $regression->getIntercept();
$coefficient = $regression->getCoefficients()[0];

$data = [
    'x' => implode(',',array_column($x,0)),
    'y' => implode(',',$y),
    'grade_next_number'  => implode(',',array_column($data, 'grade_next_number')),
    'enunciado'   => Iassign::getStatementName($statementid),
    'table'       => Table::statement($statementid),
    'intercept'   => number_format($intercept,3),
    'coefficient' => number_format($coefficient,3),
    'max'         => max($y)
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_itpc/statement', $data);
echo $OUTPUT->footer();
