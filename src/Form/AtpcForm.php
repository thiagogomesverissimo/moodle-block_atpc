<?php

namespace block_atpc\Form;

require_once("$CFG->libdir/formslib.php");

require_once('../src/Service/Iassign.php');
use block_atpc\Service\Iassign;

class AtpcForm extends \moodleform {

    public function definition() {

        $title = 'Select a course'; // TODO: internationalization
        $courses = Iassign::courses();
        $courses[0] = 'All Courses'; // TODO: internationalization

        $select = $this->_form->addElement('select', 'course', $title, $courses);
        $select->setSelected(0);

        $this->_form->addElement('submit', 'button', 'Send');
        
        $this->_form->addRule('course', null, 'required');
        

    }
}
