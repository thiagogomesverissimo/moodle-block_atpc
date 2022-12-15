<?php

class block_tasksummary extends block_base {
    public function init() {
        $this->title = 'ss';
    }

    public function get_content() {
        global $USER;

        $this->title = 'Mineração dados Itarefas';
        
        $this->content =  new stdClass;

        $this->content->text = 'Exemplo Bloco Educação e Dados';

        $url = new moodle_url('/blocks/tasksummary/pages/tasksummary.php', [
            #'var1' => $var1,
        ]);

        $attr = [
            'class'=>'btn btn-xs btn-success'
        ];

        $this->content->text .= html_writer::link($url, 'Ver resumo completo', $attr) . '<br><br>';

        return $this->content;
    }

}