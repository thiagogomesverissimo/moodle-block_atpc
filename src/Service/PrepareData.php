<?php

namespace block_atpc\Service;

require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '/blocks/atpc/vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;

class PrepareData
{
    public static function statement($statementid){

        $users = Iassign::usersFromStatement($statementid);

        $rows = [];
        foreach($users as $userid){

            $url = new \moodle_url('/blocks/atpc/pages/user.php', [
                'userid' => $userid,
            ]);

            $submissions = Iassign::allSubmissionsFromUserAndStatement($statementid, $userid);
            foreach($submissions as $submission){

                $next = next($submissions);
                if(empty($next)) continue;

                // working in dates with carbon
                $timecreated = Carbon::createFromTimestamp($submission['timecreated']);
                $timecreated_next = Carbon::createFromTimestamp($next['timecreated']);
                $difftime = $timecreated->diffInSeconds($timecreated_next);

                // TODO: find a better way to work with code difference
                $answer = strlen($submission['answer']);
                $answer_next = strlen($next['answer']);
                $diffanswer = $answer_next - $answer;


                $rows[] = [
                    'submissions'      => $submission['id'] . '-' . $next['id'],
                    'userid'           => $userid,
                    'userid_link'      => "<a href='{$url}'>{$userid}</a>",

                    'grade'            => $submission['grade'],
                    'grade_next'       => $next['grade'],

                    'timecreated'      => $timecreated,
                    'timecreated_next' => $timecreated_next,
                    'difftime'         => $difftime,

                    'answer'           => $answer,
                    'answer_next'      => $answer_next,
                    'diffanswer'       => $diffanswer,
                ];
            }
        }
        return $rows;
    }   
}