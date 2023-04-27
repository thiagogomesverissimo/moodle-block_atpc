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
        //$diffanswer = Utils::filterArrayByKeys($rows, ['diffanswer']);
        //$diffanswer = array_column($diffanswer,'diffanswer');
        $diffanswer = array_column($rows,'diffanswer');
        $diffanswer_min = min($diffanswer);
        $diffanswer_max = max($diffanswer);

        // get min and max from difftime
        //$difftime = Utils::filterArrayByKeys($rows, ['difftime']);
        //$difftime = array_column($difftime,'difftime');
        $difftime = array_column($rows,'difftime');
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
        $mtes = array_column($rows,'mtes');
        $mtes_min = min($mtes);
        $mtes_diff = max($mtes) - min($mtes);

        // get min and max for mtes
        $mdes = array_column($rows,'mdes');
        $mdes_min = min($mdes);
        $mdes_diff = max($mdes) - min($mdes);

        // get min and max for dex
        $dex = array_column($rows,'dex');
        $dex_min = min($dex);
        $dex_diff = max($dex) - min($dex);

        foreach($rows as $key=>$row){
            $rows[$key]['mtes_normalized'] = $mtes_diff==0 ? 0: ($row['mtes'] - $mtes_min)/$mtes_diff;
            $rows[$key]['mdes_normalized'] = $mdes_diff==0 ? 0: ($row['mdes'] - $mdes_min)/$mdes_diff;
            $rows[$key]['dex_normalized']  = $dex_diff ==0 ? 0: ($row['dex'] - $dex_min)/$dex_diff;
        }
        return $rows;
    }

    public static function statementUsers($courseid = 0, $statementid = 0, $orderby = 'dex_normalized'){
        global $DB;
        
        $query = "SELECT userid,
                         submissionsbyuser,
                         dex_normalized,
                         mdes_normalized,
                         mtes_normalized
      /*                   numberofusers,
                         avgsubmissionsbyuser,
                         median,
                         max*/
                    FROM {block_itpc_statement_metrics} ";

        if( $courseid != 0 and $statementid != 0 ) {
            $query .= " WHERE courseid = {$courseid} AND statementid = {$statementid} ";
        } elseif( $courseid != 0 and $statementid == 0 ) {
            $query .= " WHERE courseid = {$courseid} ";
        } elseif( $courseid == 0 and $statementid != 0 ) {
            $query .= " WHERE statementid = {$statementid} ";
        }

        $query .= " ORDER BY {$orderby} DESC";
        
        $results = json_encode($DB->get_records_sql($query));
        $array = json_decode($results, true);
        return $array;
    }

    public static function courseMetrics($courseid = 0, $statementid = 0, $orderby = 'dex_normalized_avg'){
        global $DB;
        
        $query = "SELECT statementid, 
                         statement, 
                         courseid,
                         numberofsubmissions,
                         numberofusers,
                         avgsubmissionsbyuser,
                         median,
                         max, 
                    AVG(dex_normalized) AS dex_normalized_avg,
                    AVG(mdes_normalized) AS mdes_normalized_avg,
                    AVG(mtes_normalized) AS mtes_normalized_avg
                    FROM {block_itpc_statement_metrics} ";

        if( $courseid != 0 and $statementid != 0 ) {
            
            $query .= " WHERE courseid = {$courseid} AND statementid = {$statementid} ";
        } elseif( $courseid != 0 and $statementid == 0 ) {
            $query .= " WHERE courseid = {$courseid} ";
        } elseif( $courseid == 0 and $statementid != 0 ) {
            $query .= " WHERE statementid = {$statementid} ";
        }

        $query .= " GROUP BY statementid";
        $query .= " ORDER BY {$orderby} DESC";
        
        $results = json_encode($DB->get_records_sql($query));
        return json_decode($results, true);
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
        //$diffanswer = Utils::filterArrayByKeys($rows, ['diffanswer']);
        //$diffanswer = array_column($diffanswer,'diffanswer');
        $diffanswer = array_column($rows,'diffanswer');
        $diffanswer_min = min($diffanswer);
        $diffanswer_max = max($diffanswer);

        // get min and max from difftime
        //$difftime = Utils::filterArrayByKeys($rows, ['difftime']);
        //$difftime = array_column($difftime,'difftime');
        $difftime = array_column($rows,'difftime');
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