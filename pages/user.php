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
$userid = required_param('userid', PARAM_INT);

// Metadata for moodle page
$url = new moodle_url("/blocks/itpc/pages/user.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$page_title = Iassign::getUserName($userid);
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// preparando x e y para regressão linear
$data = PrepareData::user($userid);
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
    'grade_next'  => implode(',',array_column($data, 'grade_next')),
    'table'       => Table::user($userid),
    'intercept'   => number_format($intercept,3),
    'coefficient' => number_format($coefficient,3),
    'max'         => max($y)
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_itpc/user', $data);
echo $OUTPUT->footer();
