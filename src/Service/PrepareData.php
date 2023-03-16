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

                $answer = strlen($submission['answer']);
                $answer_next = strlen($next['answer']);

                // TODO: find a better way to work with code difference
                //$diffanswer = $answer_next - $answer;
                //https://www.php.net/manual/en/function.similar-text.php
                $diffanswer = similar_text($submission['answer'],$next['answer']);

                $rows[] = [
                    'submissions'      => $submission['id'] . '-' . $next['id'],
                    'userid'           => $userid,
                    'userid_link'      => "<a href='{$url}'>{$userid}</a>",

                    'grade'            => number_format($submission['grade'], 2, ',', ''),
                    'grade_number'     => $submission['grade'],
                    'grade_next'       => number_format($next['grade'], 2, ',', ''),
                    'grade_next_number'=> $next['grade'],

                    'timecreated'      => $timecreated,
                    'timecreated_next' => $timecreated_next,
                    'difftime'         => $difftime,
                    'difftime_ln'      => Utils::scaleWithLn($difftime),

                    'answer'           => $answer,
                    'answer_next'      => $answer_next,
                    'diffanswer'       => $diffanswer,
                    'diffanswer_ln'      => Utils::scaleWithLn($diffanswer),
                ];
            }
        }
        return $rows;
    }   

    public static function user($userid){

        $statements = Iassign::statementsFromUser($userid);

        $rows = [];
        foreach($statements as $statement){

            $submissions = Iassign::allSubmissionsFromUserAndStatement($statement, $userid);
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
                
                // TODO: find a better way to work with code difference
                //$diffanswer = $answer_next - $answer;
                //https://www.php.net/manual/en/function.similar-text.php
                $diffanswer = similar_text($submission['answer'],$next['answer']);

                $rows[] = [
                    'submissions'      => $submission['id'] . '-' . $next['id'],
                    'userid'           => $userid,

                    'statement_title'  => Iassign::getStatementName($statement),

                    'grade'            => number_format($submission['grade'], 2, ',', ''),
                    'grade_number'     => $submission['grade'],
                    'grade_next'       => number_format($next['grade'], 2, ',', ''),
                    'grade_next_number'=> $next['grade'],

                    'timecreated'      => $timecreated,
                    'timecreated_next' => $timecreated_next,
                    'difftime'         => $difftime,
                    'difftime_ln'      => Utils::scaleWithLn($difftime),

                    'answer'           => $answer,
                    'answer_next'      => $answer_next,
                    'diffanswer'       => $diffanswer,
                    'diffanswer_ln'      => Utils::scaleWithLn($diffanswer),
                ];
            }
        }
        return $rows;
    }
}