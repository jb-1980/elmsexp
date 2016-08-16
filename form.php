<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once(dirname(__FILE__) . '/lib.php');


/**
 * The form for selecting the khan import options.
 *
 * @copyright 2015 Joseph Gilgen
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_elmsexp_form extends moodleform {

    public function definition() {
        global $CFG, $COURSE, $DB;
        $mform = $this->_form;
        // Context instance of the course.
        $coursecontext = context_course::instance($COURSE->id);
        // Check if user has capability to upgrade/manage grades.
        $readonlygrades = !has_capability('moodle/grade:manage', $coursecontext);

        $directions = get_string('directions','report_elmsexp');
        $mform->addElement('html', "<h4>{$directions}</h4>");

        $elmscourseid = $DB->get_record('report_elmsexp_courseid',array('courseid'=>$COURSE->id));
        $mform->addElement('text',"elms_course_id",'ELMS Course ID',array('style'=>'size:20px;'));
        $mform->setType("elms_course_id", PARAM_INT);
        $default = $elmscourseid ? $elmscourseid->elmscourseid : null;
        $mform->setDefault("elms_course_id", $default);

        $gradeitems = grade_item::fetch_all(array('courseid' => $COURSE->id));
        print_object($COURSE->id);
        $elms_ids = $DB->get_records_menu('report_elmsexp_itemid',array('courseid'=>$COURSE->id),'','gradeitemid,elmsid');
        print_object($elms_ids);
        // Course module will be always fetched,
        // so length will always be 1 if no grade item is fetched.
        if (is_array($gradeitems) && (count($gradeitems) >1)) {
            usort($gradeitems, 'report_elmsexp_sort_array_by_sortorder');

            // Section to display quiz activities.
            $mform->addElement('header', 'coursegradeitems',
                    get_string('gradeitems', 'report_elmsexp'));


            $mform->setExpanded('coursegradeitems', True);
            // Looping through all grade items.
            foreach ($gradeitems as $gradeitem) {
                // Skip course and category grade items.
                if ($gradeitem->itemtype == "course" or $gradeitem->itemtype == "category") {
                    continue;
                }
                $mform->addElement(
                    'text',
                    "elms_ids[{$gradeitem->id}]",
                    $gradeitem->itemname,
                    array('style'=>'size:20px;')
                );
                $mform->setType("elms_ids[{$gradeitem->id}]", PARAM_INT);
                $default = isset($elms_ids[$gradeitem->id]) ? $elms_ids[$gradeitem->id] : null;
                $mform->setDefault("elms_ids[{$gradeitem->id}]", $default);
                // $mform->setDefault("gradeitem[{$gradeitem->idnumber}][elms_id]", 10);
                //$mform->addGroup($gradeitem_array,"gradeitem[{$gradeitem->idnumber}]",$gradeitem->itemname);
                // $mform->setType("gradeitem[{$gradeitem->idnumber}][elms_id]", PARAM_INT);
                // $mform->setDefault("gradeitem[{$gradeitem->idnumber}][elms_id]", 10);

                //$mform->addElement('advcheckbox', "gradeitem[{$gradeitem->idnumber}]", $gradeitem->itemname  , null, array('group' => 1));

            }
        }



        $this->add_action_buttons(True,get_string('submit','report_elmsexp'));

    }

    function data_preprocessing(&$default_values){
        global $DB;
        if(!empty($this->_instance)){
            print_object($this);
            $default_values['attempts'] = $this->current->attempts;

            if(($elms_id = $DB->get_records('report_elmsexp',
              array('elmscourseid'=>$this->_instance), 'position'))){

                foreach($skills as $key => $value){
                    $default_values['skillname['.$value->position.']'] = $value->skillname;
                    $default_values['skillid['.$value->position.']'] = $value->id;
                }

            }

        }
    }
}
