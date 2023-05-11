<?php

namespace block_peta\Service;

require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '/blocks/peta/vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;

use block_peta\Service\Utils;

class PrepareData
{
    public static function statement($statementid){

        $users = Iassign::usersFromStatement($statementid);

        $rows = [];
        foreach($users as $userid){

            $url = new \moodle_url('/blocks/peta/pages/user.php', [
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
                'userid' => $userid,
                'mtes'   => max(array_column($lines,'difftime')),  // Highest TES
                'mdes'   => max(array_column($lines,'diffanswer')),  // Highest DES
                'tms'    => $tms,
                'n'      => $n,
                'grade_average' => $grade_average
            ];
            
        }

        // inserting more data on rows[]

        // Extrating arrays to improve performance
        $mtes = array_column($rows,'mtes');
        $mdes = array_column($rows,'mdes');
        $tms = array_column($rows,'tms');
        $n = array_column($rows,'n');

        foreach($rows as $key=>$row){
            $rows[$key]['mtes_normalized'] = Utils::normalize($row['mtes'], $mtes);
            $rows[$key]['mdes_normalized'] = Utils::normalize($row['mdes'], $mdes);
            $rows[$key]['tms_normalized']  = Utils::normalize($row['tms'], $tms);
            $rows[$key]['n_normalized']  = Utils::normalize($row['n'], $n);

            $penalizing = $rows[$key]['n_normalized']+$rows[$key]['tms_normalized'];
            
            $rows[$key]['dex'] = $penalizing==0? $row['grade_average'] : $row['grade_average']/$penalizing;
        }

        // Extrating arrays to improve performance
        $dex = array_column($rows,'dex');

        foreach($rows as $key=>$row){
            $rows[$key]['dex_normalized'] = Utils::normalize($row['dex'], $dex);
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
                    FROM {block_peta_statement_metrics} ";

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
                    FROM {block_peta_statement_metrics} ";

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

    public static function get_dex_avg_normalized($statementid){
        global $DB;
        
        $query = "SELECT AVG(dex_normalized) AS dex_normalized_avg
                    FROM {block_peta_statement_metrics}
                    WHERE statementid = {$statementid}
                    GROUP BY statementid ";

        $result = $DB->get_record_sql($query);
        return $result->dex_normalized_avg;
    }

    public static function get_mdes_avg_normalized($statementid){
        global $DB;
        
        $query = "SELECT AVG(mdes_normalized) AS mdes_normalized_avg
                    FROM {block_peta_statement_metrics}
                    WHERE statementid = {$statementid}
                    GROUP BY statementid ";

        $result = $DB->get_record_sql($query);
        return $result->mdes_normalized_avg;
    }

    public static function get_mtes_avg_normalized($statementid){
        global $DB;
        
        $query = "SELECT AVG(mtes_normalized) AS mtes_normalized_avg
                    FROM {block_peta_statement_metrics}
                    WHERE statementid = {$statementid}
                    GROUP BY statementid ";

        $result = $DB->get_record_sql($query);
        return $result->mtes_normalized_avg;
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