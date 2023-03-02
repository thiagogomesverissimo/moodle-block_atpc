<?php

namespace block_atpc\Service;

require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die();

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
}