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
 * Event observers used in Elms Export.
 *
 * @package    report_elmsexp
 * @copyright  2015 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../../config.php');
require_once(dirname(__FILE__) . '/../lib.php');
/**
 * Event observer for pretest.
 */
class report_elmsexp_observer {

    /**
     * Triggered via core\event\user_graded event.
     *
     * @param core\event\user_graded $event
     */
    public static function user_graded(core\event\user_graded $event) {
        global $CFG,$DB,$USER;
        //echo '<h1>Another user graded event</h1>';
        //print_object($event);
        $record = $event->get_record_snapshot($event->objecttable, $event->objectid);
        error_log('Record found: '.$record->itemid);
        if($elmsrecord = $DB->get_record('report_elmsexp_itemid', array('gradeitemid'=>$record->itemid))){
            error_log(print_r($record,true));
            $user = $DB->get_record('user',array('id'=>$record->userid));
            if($elms_course = $DB->get_record('report_elmsexp_courseid',array('courseid'=>$elmsrecord->courseid))){
              $elms_course_id = $elms_course->elmscourseid;
            } else{
              error_log('<h3>Elms course not specified</h3>');
              return null;
            }
            $elms_uid = $user->alternatename;
            $elms_itemid = $elmsrecord->elmsid;

            $grade = round($record->finalgrade);
            $grademax = $record->rawgrademax;

            $out = new stdClass;
            $out->grademax = $grademax;
            $out->grade = $grade;
            $out->elms_itemid = $elms_itemid;
            $out->elms_uid = $elms_uid;
            $out->elms_course_id = $elms_course_id;

            $r = report_elmsexp_export_grade($grademax,$grade,$elms_itemid,$elms_uid,$elms_course_id);
            error_log(print_r($r->body,true));
        }
    }
}
