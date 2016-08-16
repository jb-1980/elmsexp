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

/**
 * Script to let users import grades for grade items throughout a course.
 *
 * @package   local_flexdates_mod_duration
 * @copyright 2014 Joseph Gilgen
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(dirname(__FILE__) . '/form.php');

$id = required_param('id', PARAM_INT); // Course id.

// Should be a valid course id.
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_login($course);

// Setup page.
$PAGE->set_url('/report/elmsexp/index.php', array('id'=>$id));
$PAGE->set_pagelayout('admin');

// Check permissions.
$coursecontext = context_course::instance($course->id);
require_capability('report/elmsexp:view', $coursecontext);

// Creating form instance, passed course id as parameter to action url.
$baseurl = new moodle_url('/report/elmsexp/index.php', array('id' => $id));
$mform = new report_elmsexp_form($baseurl);

$returnurl = new moodle_url('/course/view.php', array('id' => $id));
if ($mform->is_cancelled()) {
    // Redirect to course view page if form is cancelled.
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    print_object($data);
    $eid = $data->elms_course_id;
    $elms_ids = $data->elms_ids;

    $transaction = $DB->start_delegated_transaction();

    // Update elms course id in database if necessary
    if($data->elms_course_id != 0){
        if(!$elmscourse = $DB->get_record('report_elmsexp_courseid',array('courseid'=>$course->id))){
            $dataobject = new stdClass;
            $dataobject->courseid = $course->id;
            $dataobject->elmscourseid = $data->elms_course_id;
            $DB->insert_record('report_elmsexp_courseid',$dataobject);
        } else{
            $elmscourse->elmscourseid = $data->elms_course_id;
            $DB->update_record('report_elmsexp_courseid', $elmscourse);
        }
    } elseif($elmscourse = $DB->get_record('report_elmsexp_courseid', array('courseid'=>$course->id))){
        $DB->delete_records('report_elmsexp_courseid',array('courseid'=>$course->id));
    }

    // Update elms item ids in database
    foreach($elms_ids as $k=>$v){

        if($v!=0){
            if(!$record = $DB->get_record('report_elmsexp_itemid',array('gradeitemid'=>$k))){
                $dataobject = new stdClass;
                $dataobject->courseid = $course->id;
                $dataobject->gradeitemid = $k;
                $dataobject->elmsid = $v;
                $DB->insert_record('report_elmsexp_itemid',$dataobject);
            } else{
                $record->elmsid = $v;
                $DB->update_record('report_elmsexp_itemid', $record);
            }
        } elseif($record = $DB->get_record('report_elmsexp_itemid',array('gradeitemid'=>$k))){
            $DB->delete_records('report_elmsexp_itemid',array('gradeitemid'=>$k));
        }
    }
    $transaction->allow_commit();
    rebuild_course_cache($course->id);
    redirect($returnurl);
} else {
    $PAGE->set_title($course->shortname .': '. get_string('elmsexport', 'report_elmsexp'));
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($course->fullname));
    $mform->display();
    echo $OUTPUT->footer();
}
