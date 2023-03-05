<?php

require_once($CFG->dirroot . '/blocks/atpc/src/Form/AtpcForm.php');
use block_atpc\Form\AtpcForm;

class block_atpc extends block_base {
    public function init() {
        $this->title = get_string('block_title','block_atpc');
    }

    public function get_content() {
        global $USER;

        $this->title = $this->title = get_string('block_name','block_atpc');
        $this->content = new stdClass;
        
        $form = new AtpcForm(new moodle_url('/blocks/atpc/pages/atpc.php'));

        $this->content->text = 'Select a course: ';
        $this->content->text .= $form->render();

        return $this->content;
    }

}