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
 * ELMS export settings.
 *
 * @package    report_elmsexp
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('report_elmsexp_header',
                                         get_string('headerconfig', 'report_elmsexp'),
                                         get_string('descconfig', 'report_elmsexp')));

$settings->add(new admin_setting_configtext('report_elmsexp/key',
                                                get_string('username', 'report_elmsexp'),
                                                get_string('descusername', 'report_elmsexp'),
                                                null));
$settings->add(new admin_setting_configtext('report_elmsexp/secret',
                                                get_string('secret', 'report_elmsexp'),
                                                get_string('descsecret', 'report_elmsexp'),
                                                null));
