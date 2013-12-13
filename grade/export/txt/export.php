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

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/export/lib.php';
require_once 'grade_export_txt.php';

$id                = required_param('id', PARAM_SEQUENCE); // course ids
$groupid           = optional_param('groupid', 0, PARAM_INT);
$itemids           = required_param('itemids', PARAM_RAW);
$export_feedback   = optional_param('export_feedback', 0, PARAM_BOOL);
$separator         = optional_param('separator', 'comma', PARAM_ALPHA);
$updatedgradesonly = optional_param('updatedgradesonly', false, PARAM_BOOL);
$displaytype       = optional_param('displaytype', $CFG->grade_export_displaytype, PARAM_INT);
$decimalpoints     = optional_param('decimalpoints', $CFG->grade_export_decimalpoints, PARAM_INT);
$onlyactive        = optional_param('export_onlyactive', 0, PARAM_BOOL);

list($sqlin, $sqlparams) = $DB->get_in_or_equal(explode(',', $id));
if (!$courses = $DB->get_records_select('course', "id {$sqlin}", $sqlparams)) {
    print_error('nocourseid');
}

foreach ($courses as $course) {
    require_login($course);
    $context = context_course::instance($course->id);

    require_capability('moodle/grade:export', $context);
    require_capability('gradeexport/txt:view', $context);

    if (groups_get_course_groupmode($course) == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
        if (!groups_is_member($groupid, $USER->id)) {
            print_error('cannotaccessgroup', 'grades');
        }
    }
}

// print all the exported data here
$export = new grade_export_txt($courses, $groupid, $itemids, $export_feedback, $updatedgradesonly, $displaytype, $decimalpoints, $separator, $onlyactive, true);
$export->print_grades();


