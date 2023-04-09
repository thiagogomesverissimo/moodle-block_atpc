<?php

namespace block_itpc\Service;

require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '/blocks/itpc/vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;

use block_itpc\Service\Utils;

class PrepareData
{
    public static function statement($statementid){

        $users = Iassign::usersFromStatement($statementid);

        $rows = [];
        foreach($users as $userid){

            $url = new \moodle_url('/blocks/itpc/pages/user.php', [
                'userid' => $userid,
            ]);

            $submissions = Iassign::allSubmissionsFromUserAndStatement($statementid, $userid);

            foreach($submissions as $submission){              

                $next = next($submissions);
                if(empty($next)) continue;

                // working in dates with carbon
                //$timecreated = Carbon::createFromTimestamp($submission['timecreated']);
                //$timecreated_next = Carbon::createFromTimestamp($next['timecreated']);
                //$difftime = $timecreated->diffInSeconds($timecreated_next);
                $difftime = $next['timecreated']-$submission['timecreated'];

                // Evitar divisão por zero no cálculo do DT
                $difftime = $difftime == 0 ? 1:$difftime ;

                $answer = strlen($submission['answer']);
                $answer_next = strlen($next['answer']);
                $diffanswer = abs($answer_next - $answer);

                // TODO: find a better way to work with similarity between two strings
                //https://www.php.net/manual/en/function.similar-text.php - MUITO LENTO
                //$diffanswer = similar_text($submission['answer'],$next['answer']);

                $rows[] = [
                    'submissions'      => $submission['id'] . '-' . $next['id'],
                    'userid'           => $userid,
                    'userid_link'      => "<a href='{$url}'>{$userid}</a>",

                    //'grade'            => number_format($submission['grade'], 2, ',', ''),
                    'grade'      => $submission['grade'],
                    //'grade_next'       => number_format($next['grade'], 2, ',', ''),
                    'grade_next' => $next['grade'],

                    'timecreated'      => $submission['timecreated'],
                    //'timecreated_next' => $timecreated_next,
                    'difftime'         => $difftime,
                    'difftime_ln'      => Utils::scaleWithLn($difftime),

                    //'answer'           => $answer,
                    //'answer_next'      => $answer_next,
                    'diffanswer'       => $diffanswer,
                    'diffanswer_ln'    => Utils::scaleWithLn($diffanswer),

                    // Number of changes in the submission code per seconds
                    'dt'               => $diffanswer/$difftime
                ]; 
            }
        }
        // inserting more data on rows[]

        // get min and max from diffanswer
        $diffanswer = Utils::filterArrayByKeys($rows, ['diffanswer']);
        $diffanswer = array_column($diffanswer,'diffanswer');
        $diffanswer_min = min($diffanswer);
        $diffanswer_max = max($diffanswer);

        // get min and max from difftime
        $difftime = Utils::filterArrayByKeys($rows, ['difftime']);
        $difftime = array_column($difftime,'difftime');
        $difftime_min = min($difftime);
        $difftime_max = max($difftime);

        foreach($rows as $key=>$row){
            $rows[$key]['diffanswer_normalized'] = ($row['diffanswer'] - $diffanswer_min)/($diffanswer_max-$diffanswer_min);
            $rows[$key]['difftime_normalized'] = ($row['difftime'] - $difftime_min)/($difftime_max-$difftime_min);

            // removendo casos que demoraram muito para submeter
            if($rows[$key]['dt'] <= 0.05) unset($rows[$key]);
        }

        return $rows;
    }

    public static function statementAnalysis($statementid){
        $users = Iassign::usersFromStatement($statementid);

        $data = self::statement($statementid);
        
        $rows = [];
        foreach($users as $userid){

            //$submissions = Iassign::allSubmissionsFromUserAndStatement($statementid, $userid);

            // Filtrando linhas do referido do usuário em questão
            $lines = array_filter($data, function($line) use ($userid) {
                if ($line['userid'] == $userid) 
                    return $line;
                else
                    return null;
            });
            if(empty($lines)) continue;

            // grade average
            $grades = array_column($lines,'grade');
            $n = count($lines);
            $grade_average = array_sum($grades)/$n;

            $times = array_column($lines,'timecreated');
            $tms = (max($times) - min($times))/2;

            $rows[] = [
                'userid'        => $userid,
                'mtes'          => max(array_column($lines,'difftime')),  // Highest TES
                'mdes'          => max(array_column($lines,'diffanswer')),  // Highest DES
                'dex'           => $grade_average/($tms+$n)
            ];
        }

        // inserting more data on rows[]

        // get min and max for mtes
        $mtes = Utils::filterArrayByKeys($rows, ['mtes']);
        $mtes = array_column($mtes,'mtes');
        $mtes_min = min($mtes);
        $mtes_max = max($mtes);

        // get min and max for mtes
        $mdes = Utils::filterArrayByKeys($rows, ['mdes']);
        $mdes = array_column($mdes,'mdes');
        $mdes_min = min($mdes);
        $mdes_max = max($mdes);

        // get min and max for dex
        $dex = Utils::filterArrayByKeys($rows, ['dex']);
        $dex = array_column($dex,'dex');
        $dex_min = min($dex);
        $dex_max = max($dex);

        foreach($rows as $key=>$row){
            $rows[$key]['mtes_normalized'] = ($row['mtes'] - $mtes_min)/($mtes_max-$mtes_min);
            $rows[$key]['mdes_normalized'] = ($row['mdes'] - $mdes_min)/($mdes_max-$mdes_min);
            $rows[$key]['dex_normalized'] = ($row['dex'] - $dex_min)/($dex_max-$dex_min);
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

                // time window for the submission
                //$timecreated = Carbon::createFromTimestamp($submission['timecreated']);
                //$timecreated_next = Carbon::createFromTimestamp($next['timecreated']);
                //$difftime = $timecreated->diffInSeconds($timecreated_next);
                $difftime = $next['timecreated']-$submission['timecreated'];

                // Evitar divisão por zero no cálculo do DT
                $difftime = $difftime == 0 ? 1:$difftime ;

                // TODO: find a better way to work with code difference
                $answer = strlen($submission['answer']);
                $answer_next = strlen($next['answer']);
                $diffanswer = abs($answer_next - $answer);

                // TODO: find a better way to work with code difference
                //$diffanswer = $answer_next - $answer;
                //https://www.php.net/manual/en/function.similar-text.php
                //$diffanswer = similar_text($submission['answer'],$next['answer']);

                $rows[] = [
                    'submissions'      => $submission['id'] . '-' . $next['id'],
                    'userid'           => $userid,

                    'statement_title'  => Iassign::getStatementName($statement),

                    //'grade'            => number_format($submission['grade'], 2, ',', ''),
                    'grade'     => $submission['grade'],
                    //'grade_next'       => number_format($next['grade'], 2, ',', ''),
                    'grade_next'=> $next['grade'],

                    //'timecreated'      => $timecreated,
                    //'timecreated_next' => $timecreated_next,
                    'difftime'         => $difftime,
                    'difftime_ln'      => Utils::scaleWithLn($difftime),

                    //'answer'           => $answer,
                    //'answer_next'      => $answer_next,
                    'diffanswer'         => $diffanswer,
                    'diffanswer_ln'      => Utils::scaleWithLn($diffanswer),

                    // Number of changes in the submission code per seconds
                    'dt'               => $diffanswer/$difftime,
                ];
            }
        }

        // inserting more data on rows[]

        // get min and max from diffanswer
        $diffanswer = Utils::filterArrayByKeys($rows, ['diffanswer']);
        $diffanswer = array_column($diffanswer,'diffanswer');
        $diffanswer_min = min($diffanswer);
        $diffanswer_max = max($diffanswer);

        // get min and max from difftime
        $difftime = Utils::filterArrayByKeys($rows, ['difftime']);
        $difftime = array_column($difftime,'difftime');
        $difftime_min = min($difftime);
        $difftime_max = max($difftime);

        foreach($rows as $key=>$row){
            $rows[$key]['diffanswer_normalized'] = ($row['diffanswer'] - $diffanswer_min)/($diffanswer_max-$diffanswer_min);
            $rows[$key]['difftime_normalized'] = ($row['difftime'] - $difftime_min)/($difftime_max-$difftime_min);

            //if($rows[$key]['dt'] < 0.05) unset($rows[$key]);
        }

        return $rows;
    }
}