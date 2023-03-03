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

        $users = Iassign::usersFromStatement($statementid);

        $lines = [];
        foreach($users as $userid){
            $submissions = Iassign::allSubmissionsFromUserAndStatement($statementid, $userid);
            foreach($submissions as $submission){
                $next = next($submissions);
                if(empty($next)) continue;

                $lines[] = [
                    'submissions'      => $submission['id'] . '-' . $next['id'],
                    'userid'           => $userid,
                    'timecreated'      => Carbon::createFromTimestamp($submission['timecreated']),
                    'timecreated_next' => Carbon::createFromTimestamp($next['timecreated']),
                    'grade'            => $submission['grade'],
                    'grade_next'       => $next['grade'],
                    'answer'           => strlen($submission['answer']),
                    'answer_next'      => strlen($next['answer'])
                ];
            }
        }

        $table = new \html_table();

        $table->head = [ 
            'submissions',
            'userid',
            'timecreated',
            'timecreated_next',
            'grade',
            'grade_next',
            'answer',
            'answer_next',
            'diff sec',
            'diff answer'
        ];

        $array_difftime = [];
        $array_diffanswer = [];
        $array_grade = [];

        foreach($lines as $row){
            $difftime = $row['timecreated']->diffInSeconds($row['timecreated_next']);
            $diffanswer =  $row['answer_next']-$row['answer'];

            $array_difftime[] = Utils::scaleWithLn($difftime);
            $array_diffanswer[] = Utils::scaleWithLn($diffanswer);
            $array_grade[] = $row['grade_next'];

            $url = new moodle_url('/blocks/atpc/pages/user.php', [
                'userid' => $row['userid'],
            ]);

            $table->data[] = [
                $row['submissions'],
                "<a href='{$url}'>{$row['userid']}</a>",
                $row['timecreated'],
                $row['timecreated_next'],
                number_format($row['grade'], 2, ',', ''),
                number_format($row['grade_next'], 2, ',', ''),
                $row['answer'],
                $row['answer_next'],
                $difftime,
                $diffanswer
            ];
        }
        $table->align = ['left','left','right','right','right','right','right','right','right','right'];

        return \html_writer::table($table);
    }
}