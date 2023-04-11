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
            'MDEX médio',
            'TES Médio'
        ];

        $metrics_table = array_map(
            function($metric) {
                $url1 = new \moodle_url('/blocks/itpc/pages/statement.php', [
                    'statementid' => $metric['statementid'],
                ]);
    
                $url2 = new \moodle_url('/blocks/itpc/pages/statement_analysis.php', [
                    'statementid' => $metric['statementid'],
                ]);

                $metric['statementid'] = "{$metric['statementid']} <a href='{$url1}'>Analysis 1</a> <br> <a href='{$url2}'>Analysis 2</a>";

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
        die('arrumar');
        $metrics = PrepareData::courseMetrics($courseid = 0, $statementid = 5649);

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
            'MDEX médio',
            'TES Médio'
        ];

        $metrics_table = array_map(
            function($metric) {
                $url1 = new \moodle_url('/blocks/itpc/pages/statement.php', [
                    'statementid' => $metric['statementid'],
                ]);
    
                $url2 = new \moodle_url('/blocks/itpc/pages/statement_analysis.php', [
                    'statementid' => $metric['statementid'],
                ]);

                $metric['statementid'] = "{$metric['statementid']} <a href='{$url1}'>Analysis 1</a> <br> <a href='{$url2}'>Analysis 2</a>";

                $metric['avgsubmissionsbyuser'] = number_format($metric['avgsubmissionsbyuser'], 2, '.', '');
                $metric['median'] = number_format($metric['median'], 2, '.', '');
                $metric['dex_normalized_avg'] = number_format($metric['dex_normalized_avg'], 2, '.', '');
                $metric['mdes_normalized_avg'] = number_format($metric['mdes_normalized_avg'], 2, '.', '');
                $metric['mtes_normalized_avg'] = number_format($metric['mtes_normalized_avg'], 2, '.', '');
                return $metric;
            },$metrics);

        $table->data = $metrics_table;

        /*
        $data = PrepareData::statementAnalysis($statementid);

        $columns = [ 
            'userid',
            'mtes',
            'mdes',
            'dex',
            'mtes_normalized',
            'mdes_normalized',
            'dex_normalized',
        ];

        $table = new \html_table();
        $table->head = $columns;
        $table->data = $data;
        */
        return \html_writer::table($table);
    }
}