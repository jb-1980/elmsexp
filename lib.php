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
 * Elms Export.
 *
 * @package   report_elmsexp
 * @author    Joseph Gilgen <gilgenlabs@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/grade/grade_item.php');
include_once(dirname(__FILE__).'/Requests/library/Requests.php');
Requests::register_autoloader();

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_elmsexp_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/elmsexp:view', $context)) {
        $url = new moodle_url('/report/elmsexp/index.php', array('id' => $course->id));
        $navigation->add(get_string( 'elmsexport', 'report_elmsexp' ),
                $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}


function report_elmsexp_export_grade($grademax,$grade,$elmsid,$elmsuid,$elmscourseid){
    // Create an authenticated session
    $username = get_config('report_elmsexp')->key;
    $password = get_config('report_elmsexp')->secret;
    $creds = array('username'=>$username,'password'=>$password);
    $url = 'http://myedkey.org';
    $session = new Requests_Session($url,array(),$data=$creds);
    $authentication_url = 'http://myedkey.org/signin/';
    $session->post($authentication_url);

    // Export the grade
    $data = array(
        $elmsid => array(
            "grademax"=> $grademax,
            "type"=>"content",
            "studentgrades"=> array(
                array(
                    "score"=>$grade,
                    "id"=>$elmsuid
                )
            )
        )
    );
    $headers = array('Content-Type'=>'application/json');
    $url = "http://myedkey.org/classes/{$elmscourseid}/grades/import";
    $r= $session->post($url,$headers,json_encode($data));
    return $r;
}

function report_elmsexp_sort_array_by_sortorder($item1, $item2) {
    if (!$item1->sortorder || !$item2->sortorder) {
        return 0;
    }
    if ($item1->sortorder == $item2->sortorder) {
        return 0;
    }
    return ($item1->sortorder > $item2->sortorder) ? 1 : -1;
}
