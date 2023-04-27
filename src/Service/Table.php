<?php

namespace block_itpc\Service;

require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '/blocks/itpc/vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;
use Illuminate\Support\Collection;

use block_itpc\Service\Utils;

class Table
{
    public static function statements($course = 0){
        global $DB;

        $metrics = PrepareData::courseMetrics($course);

        $table = new \html_table();

        $table->head = [ 
            'Statement id',
            'Enunciado',
            'course id',
            'Quantidade de submissões',
            'Quantidade de usuários',
            'Média de submissões por usuários',
            'Mediana',
            'Máximo de submissões por um único usuário',
            'DEX médio',
            'MDES médio',
            'MTES Médio'
        ];

        $metrics_table = array_map(
            function($metric) {
                $submissions_url = new \moodle_url('/blocks/itpc/pages/submissions.php', [
                    'statementid' => $metric['statementid'],
                ]);
    
                $users_url = new \moodle_url('/blocks/itpc/pages/users.php', [
                    'statementid' => $metric['statementid'],
                ]);

                $metric['statementid'] = "{$metric['statementid']}<br><a href='{$submissions_url}'>Submissions Analysis</a> <br> <a href='{$users_url}'>Users Analysis</a>";

                $metric['avgsubmissionsbyuser'] = number_format($metric['avgsubmissionsbyuser'], 2, '.', '');
                $metric['median'] = number_format($metric['median'], 2, '.', '');
                $metric['dex_normalized_avg'] = number_format($metric['dex_normalized_avg'], 2, '.', '');
                $metric['mdes_normalized_avg'] = number_format($metric['mdes_normalized_avg'], 2, '.', '');
                $metric['mtes_normalized_avg'] = number_format($metric['mtes_normalized_avg'], 2, '.', '');
                return $metric;
            },$metrics);

        $table->data = $metrics_table;

        return \html_writer::table($table);
    }

    public static function statement($statementid){

        $data = PrepareData::statement($statementid);

        $table = new \html_table();

        $columns = [ 
            'submissions',
            'userid_link',
            'timecreated',
            'timecreated_next',
            'difftime',
            'grade',
            'grade_next',
            'answer',
            'answer_next',
            'diffanswer',
            'diffanswer_normalized',
            'difftime_normalized',
            'dt'
        ];

        $data_filtered = Utils::filterArrayByKeys($data, $columns);

        // this if is necessary because filterArrayByKeys changed the order of columns
        if(empty($data_filtered)){
            $table->head = $columns;
        } else {
            $table->head = array_keys($data_filtered[0]);
        }
        
        $table->data = $data_filtered; 
        //$table->align = ['left','left','right','right','right','right','right','right','right','right'];

        return \html_writer::table($table);
    }
 
    public static function user($userid){

        $data = PrepareData::user($userid);

        $table = new \html_table();

        $columns = [ 
            'submissions',
            'statement_title',
            'timecreated',
            'timecreated_next',
            'difftime',
            'grade',
            'grade_next',
            'answer',
            'answer_next',
            'diffanswer',
            'diffanswer_normalized',
            'difftime_normalized',
            'dt'
        ];

        $data_filtered = Utils::filterArrayByKeys($data, $columns);

        // this if is necessary because filterArrayByKeys changed the order of columns
        if(empty($data_filtered)){
            $table->head = $columns;
        } else {
            $table->head = array_keys($data_filtered[0]);
        }
        
        $table->data = $data_filtered; 
        //$table->align = ['left','left','right','right','right','right','right','right','right','right'];

        return \html_writer::table($table);
    }

    public static function statementAnalysis($statementid){

        $metrics = PrepareData::metrics($courseid = 0, $statementid);

        //echo "<pre>"; var_dump($metrics); die();

        $table = new \html_table();

        $table->head = [
            'Student',
            'Number of submissions',
           // 'Quantidade de usuários',
           // 'Média de submissões por usuários',
           // 'Mediana',
           // 'Máximo de submissões por um único usuário',
            'DEX',
            'MDES',
            'MTES'
        ];

        $metrics_table = array_map(
            function($metric) {
                global $DB;
                $user = $DB->get_record('user', ['id' => $metric['userid']]);

                $metric['userid'] = $user->id .' - '. $user->firstname .' '. $user->lastname;
                $metric['numberofsubmissions'] = $metric['numberofsubmissions'];

                $metric['dex_normalized'] = number_format($metric['dex_normalized'], 2, '.', '');
                $metric['mdes_normalized'] = number_format($metric['mdes_normalized'], 2, '.', '');
                $metric['mtes_normalized'] = number_format($metric['mtes_normalized'], 2, '.', '');

                //$metric['avgsubmissionsbyuser'] = number_format($metric['avgsubmissionsbyuser'], 2, '.', '');
                //$metric['median'] = number_format($metric['median'], 2, '.', '');
                //$metric['dex_normalized_avg'] = number_format($metric['dex_normalized_avg'], 2, '.', '');
                //$metric['mdes_normalized_avg'] = number_format($metric['mdes_normalized_avg'], 2, '.', '');
                //$metric['mtes_normalized_avg'] = number_format($metric['mtes_normalized_avg'], 2, '.', '');
                return $metric;
            }, $metrics);

        $table->data = $metrics_table;

        return \html_writer::table($table);
    }
}