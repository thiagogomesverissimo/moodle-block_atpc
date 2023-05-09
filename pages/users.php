<?php

// protecting the page
require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_login();

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '/blocks/peta/vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;

// plugin classes
require_once($CFG->dirroot . '/blocks/peta/src/Service/Iassign.php');
require_once($CFG->dirroot . '/blocks/peta/src/Service/Utils.php');
require_once($CFG->dirroot . '/blocks/peta/src/Service/Table.php');
require_once($CFG->dirroot . '/blocks/peta/src/Service/PrepareData.php');
use block_peta\Service\Iassign;
use block_peta\Service\Utils;
use block_peta\Service\Table;
use block_peta\Service\PrepareData;

// required params from request
$statementid = required_param('statementid', PARAM_INT);

// Metadata for moodle page
$url = new moodle_url("/blocks/peta/pages/users.php");
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$page_title = 'Statement '. $statementid; // TODO: internationalization
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

$data = [
    'table' => Table::statementUsers($statementid),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_peta/users', $data);
echo $OUTPUT->footer();