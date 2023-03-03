<?php

namespace block_atpc\Service;

require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();

// loading external libraries installed inside of the plugin with composer
require_once($CFG->dirroot . '../../vendor/autoload.php');
use Phpml\Regression\LeastSquares;
use Carbon\Carbon;

class Table
{
    public static function statements($course = 0){

        $table = new \html_table();

        $table->head = [ 
            'statement id',
            'course id',
            'Enunciado',
            'Quantidade de submissões',
            'Quantidade de usuários',
            'Média de submissões por usuários',
            'Mediana',
            'Máximo de submissões por um único usuário'
        ];

        foreach(Iassign::statementsWithSubmissions($course) as $statement){

            $url = new \moodle_url('/blocks/atpc/pages/statement.php', [
                'statementid' => $statement->id,
            ]);
          
            $submissions = Iassign::numberOfSubmissionsFromStatement($statement->id);
          
            // usuário com maior número de submissões
            $max = max(array_column($submissions, 'total'));
            $mediana = Utils::median(array_column($submissions, 'total'));
          
            $n = count($submissions);
            $media = number_format((float) $statement->total/$n, 2, ',', '');
          
            $table->data[] = [
              "<a href='{$url}'>{$statement->id}</a>",
              Iassign::getStatementCourse($statement->id),
              Iassign::getStatementName($statement->id),
              $statement->total,
              $n,
              $media,
              $mediana,
              $max
            ];
          }
          $table->align = ['left','left','right','right','right','right','right'];

          return \html_writer::table($table);
    }

    public static function statement($statementid){

        $data = PrepareData::statement($statementid);
/*
        $array_difftime = [];
        $array_diffanswer = [];
        $array_grade = [];

        number_format($row['grade'], 2, ',', ''),
        number_format($row['grade_next'], 2, ',', ''),
        $array_difftime[] = Utils::scaleWithLn($difftime);
        $array_diffanswer[] = Utils::scaleWithLn($diffanswer);
        $array_grade[] = $row['grade_next'];
*/

        
        $table = new \html_table();

        $columns = [ 
            'submissions',
            'userid',
           /*'timecreated',
            'timecreated_next',
            'difftime',
            'grade',
            'grade_next',
            'answer',
            'answer_next',
            'diffanswer'*/
        ];

        //$table->head = $columns;

        //echo "<pre>"; var_dump(array_column($rows,'submissions','userid')); die();

        $table->data = $data;
        //$table->align = ['left','left','right','right','right','right','right','right','right','right'];

        return \html_writer::table($table);
    }
}