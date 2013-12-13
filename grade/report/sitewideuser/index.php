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
 * The gradebook sitewide user report
 * @package   gradereport_sitewideuser
 * @copyright 2012 onwards Barry Oosthuizen http://elearningstudio.co.uk
 * @author    Barry Oosthuizen
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/sitewideuser/lib.php';
require_once $CFG->libdir . '/coursecatlib.php';
require_once $CFG->dirroot . '/grade/report/sitewideuser/categorylib.php';

// check box tree insert

$PAGE->requires->js('/grade/report/sitewideuser/checkboxtree/js/jquery-latest.js', true);
$PAGE->requires->js('/grade/report/sitewideuser/checkboxtree/js/jquery-ui.min.js', true);
$PAGE->requires->js('/grade/report/sitewideuser/checkboxtree/js/jquery.checkboxtree.js', true);

$PAGE->requires->css('/grade/report/sitewideuser/checkboxtree/css/themes/base/ui.all.css');
$PAGE->requires->css('/grade/report/sitewideuser/checkboxtree/css/checkboxtree.css');

// end of insert

$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

$formsubmitted = optional_param('formsubmitted', 0, PARAM_TEXT);

$PAGE->set_url(new moodle_url('/grade/report/sitewideuser/index.php', array('id' => $courseid)));

// basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$PAGE->set_pagelayout('report');

$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('gradereport/sitewideuser:view', $context);

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);
} else {
    if (!$DB->get_record('user', array('id' => $userid, 'deleted' => 0)) or isguestuser($userid)) {
        print_error('invaliduser');
    }
}

$access = false;
if (has_capability('moodle/grade:viewall', $context)) {
    //ok - can view all course grades
    $access = true;
} else if ($userid == $USER->id and has_capability('moodle/grade:view', $context) and $course->showgrades) {
    //ok - can view own grades
    $access = true;
} else if (has_capability('moodle/grade:viewall', get_context_instance(CONTEXT_USER, $userid)) and $course->showgrades) {
    // ok - can view grades of this user- parent most probably
    $access = true;
}

if (!$access) {
    // no access to grades!
    print_error('nopermissiontoviewgrades', 'error', $CFG->wwwroot . '/course/view.php?id=' . $courseid);
}

// return tracking object
$gpr = new grade_plugin_return(array('type' => 'report', 'plugin' => 'sitewideuser', 'courseid' => $courseid, 'userid' => $userid));

// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'sitewideuser';

//first make sure we have proper final grades - this must be done before constructing of the grade tree
grade_regrade_final_grades($courseid);


$reportname = get_string('modulename', 'gradereport_sitewideuser');

print_grade_page_head($COURSE->id, 'report', 'sitewideuser', $reportname, false);
?>
<script type="text/javascript">

    jQuery(document).ready(function(){
        jQuery("#docheckchildren").checkboxTree({
            collapsedarrow: "checkboxtree/images/checkboxtree/img-arrow-collapsed.gif",
            expandedarrow: "checkboxtree/images/checkboxtree/img-arrow-expanded.gif",
            blankarrow: "checkboxtree/images/checkboxtree/img-arrow-blank.gif",
            checkchildren: true,
            checkparents: false
        });

    });

</script>

<?php

echo '<br/><br/>';

echo '<form method="post" action="index.php">';
echo '<div id="categorylist">';
echo '<ul class="unorderedlisttree" id="docheckchildren">';
gradereport_sitewideuser_print_category();

echo '</ul>';
echo '<div><input type="hidden" name="id" value="' . $courseid . '"/></div>';
echo '<div><input type="hidden" name="userid" value="' . $USER->id . '"/></div>';
echo '<div><input type="hidden" name="formsubmitted" value="Yes"/></div>';
echo '<div><input type="hidden" name="sesskey" value="' . sesskey() . '"/></div>';

echo '<div><input type="submit" name="submitquery" value="' . get_string("submit") . '"/></div>';
echo '</div>';
echo '</form>';
echo '<br/><br/>';


if($formsubmitted === "Yes") {
    
    $coursebox = optional_param_array('coursebox', 0, PARAM_RAW);

    $selectedcourses = array();

    foreach ($coursebox as $id => $value) {
        $selectedcourses[] = $value;
    }

    if (!empty($selectedcourses)) {
        list($courselist, $params) = $DB->get_in_or_equal($selectedcourses, SQL_PARAMS_NAMED, 'm');
        $sql = "select * FROM {course} WHERE id $courselist ORDER BY shortname";
        $courses = $DB->get_records_sql($sql, $params);

        foreach ($courses as $thiscourse) {

            $courseid = $thiscourse->id;

            // return tracking object
            $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'sitewideuser', 'courseid'=>$courseid, 'userid'=>$userid));
            //first make sure we have proper final grades - this must be done before constructing of the grade tree
            /// basic access checks
            if (!$course = $DB->get_record('course', array('id' => $courseid))) {
                print_error('nocourseid');
            }
            $context = get_context_instance(CONTEXT_COURSE, $thiscourse->id);
            grade_regrade_final_grades($courseid);

            if (has_capability('moodle/grade:viewall', $context)) { //Teachers will see all student reports
                if (has_capability('gradereport/sitewideuser:view', $context)) {
                    // Print graded user selector at the top

                    if (empty($userid)) {
                        $gui = new graded_users_iterator($thiscourse, null, $currentgroup);
                        $gui->init();
                        // Add tabs

                        while ($userdata = $gui->next_user()) {
                            $user = $userdata->user;
                            $report = new grade_report_sitewideuser($courseid, $gpr, $context, $user->id);
                            //print_heading('<a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'">'.$thiscourse->shortname.'</a> - '.get_string('modulename', 'gradereport_sitewideuser').'- <a href="'.$CFG->wwwroot.'/grade/report/sitewideuser/index.php?id='.$thiscourse->id.'&amp;userid='.$user->id.'">'.fullname($report->user).'</a>');
                            echo('<a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'">'.$thiscourse->shortname.'</a> - '.get_string('pluginname', 'gradereport_user').'- <a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'&amp;userid='.$user->id.'">'.fullname($report->user).'</a>');

                            if ($report->fill_table()) {
                                echo '<br />'.$report->print_table(true);
                            }
                            echo "<p style = 'page-break-after: always;'></p>";
                        }
                        $gui->close();
                    } else { // Only show one user's report

                        if ($userid == $USER->id) {

                            $gui = new graded_users_iterator($thiscourse, null);
                            $gui->init();
                            // Add tabs

                            while ($userdata = $gui->next_user()) {
                                $user = $userdata->user;
                                $report = new grade_report_sitewideuser($courseid, $gpr, $context, $user->id);
                                //print_heading('<a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'">'.$thiscourse->shortname.'</a> - '.get_string('modulename', 'gradereport_sitewideuser').'- <a href="'.$CFG->wwwroot.'/grade/report/sitewideuser/index.php?id='.$thiscourse->id.'&amp;userid='.$user->id.'">'.fullname($report->user).'</a>');
                                echo('<a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'">'.$thiscourse->shortname.'</a> - '.get_string('pluginname', 'gradereport_user').'- <a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'&amp;userid='.$user->id.'">'.fullname($report->user).'</a>');

                                if ($report->fill_table()) {
                                    echo '<br />'.$report->print_table(true);
                                }
                                echo "<p style = 'page-break-after: always;'></p>";
                            }
                            $gui->close();
                        } else {


                            $report = new grade_report_sitewideuser($courseid, $gpr, $context, $userid);

                            //print_heading('<a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'">'.$thiscourse->shortname.'</a> - '.get_string('modulename', 'gradereport_sitewideuser').'- <a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'&amp;userid='.$userid.'">'.fullname($report->user).'</a>');
                            echo('<a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'">'.$thiscourse->shortname.'</a> - '.get_string('pluginname', 'gradereport_user').'- <a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'&amp;userid='.$userid.'">'.fullname($report->user).'</a>');

                            if ($report->fill_table()) {
                                echo '<br />'.$report->print_table(true);
                            }
                            echo "<p style = 'page-break-after: always;'></p>";

                        }
                    }
                  } else {
                      echo 'You do not have permission to use this report';
                  }
            } else { //Students will see just their own report
                if (has_capability('gradereport/sitewideuser:view', $context)) {
                    // Create a report instance
                    $report = new grade_report_sitewideuser($courseid, $gpr, $context, $USER->id);

                    // print the page
                    //print_heading('<a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'">'.$thiscourse->shortname.'</a> - '.get_string('modulename', 'gradereport_sitewideuser').'- <a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'&amp;userid='.$USER->id.'">'.fullname($report->user).'</a>');
                    echo('<a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'">'.$thiscourse->shortname.'</a> - '.get_string('pluginname', 'gradereport_user').'- <a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$thiscourse->id.'&amp;userid='.$USER->id.'">'.fullname($report->user).'</a>');

                    if ($report->fill_table()) {
                        echo '<br />'.$report->print_table(true);
                    }
                    echo "<p style = 'page-break-after: always;'></p>";
                }
            }
        }
    }
} else {
    echo 'No data to display';
}
echo $OUTPUT->footer();
