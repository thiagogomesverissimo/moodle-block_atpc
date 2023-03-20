<?php

namespace block_itpc\Form;

//require_once($CFG->libdir . "/formslib.php");

require_once($CFG->dirroot . '/blocks/itpc/src/Service/Iassign.php');
use block_itpc\Service\Iassign;

class ItpcForm extends \moodleform {

    public function definition() {

        $courses = Iassign::courses();
        $courses[0] = 'All Courses'; // TODO: internationalization

        $select = $this->_form->addElement('select', 'course_id', '', $courses);
        $select->setSelected(0);

        $this->_form->addElement('submit', 'button', 'Send');
        
        $this->_form->addRule('course_id', null, 'required');
        

    }
}
