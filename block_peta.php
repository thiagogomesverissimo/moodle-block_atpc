<?php

require_once($CFG->dirroot . '/blocks/peta/src/Form/PetaForm.php');
use block_peta\Form\petaForm;

class block_peta extends block_base {
    public function init() {
        $this->title = get_string('block_title','block_peta');
    }

    public function get_content() {
        global $USER;

        $this->title = $this->title = get_string('block_name','block_peta');
        $this->content = new stdClass;
        
        $form = new PetaForm(new moodle_url('/blocks/peta/pages/peta.php'));

        $this->content->text = 'Select a course: ';
        $this->content->text .= $form->render();

        return $this->content;
    }

}