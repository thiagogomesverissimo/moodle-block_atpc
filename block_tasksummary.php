<?php

class block_tasksummary extends block_base {
    public function init() {
        $this->title = get_string('block_title', 'block_tasksummary');
    }

    public function get_content() {
        global $USER;
        
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