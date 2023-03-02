<?php

namespace block_atpc\Form;

require_once("$CFG->libdir/formslib.php");

require_once('../src/Service/Iassign.php');
use block_atpc\Service\Iassign;

class AtpcForm extends \moodleform {

    public function definition() {

        $courses = Iassign::courses();

        print_r($courses); die();

        $title = 'Selecionar curso'; // TODO: internationalization

        $this->_form->addElement('select', 'course', $title, $courses);
        $this->_form->addElement('submit', 'button', 'Enviar');
        $this->_form->addRule('course', null, 'required');

    }
}
