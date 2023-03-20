<?php

require_once($CFG->dirroot . '/blocks/itpc/src/Form/ItpcForm.php');
use block_itpc\Form\ItpcForm;

class block_itpc extends block_base {
    public function init() {
        $this->title = get_string('block_title','block_itpc');
    }

    public function get_content() {
        global $USER;

        $this->title = $this->title = get_string('block_name','block_itpc');
        $this->content = new stdClass;
        
        $form = new ItpcForm(new moodle_url('/blocks/itpc/pages/itpc.php'));

        $this->content->text = 'Select a course: ';
        $this->content->text .= $form->render();

        return $this->content;
    }

}