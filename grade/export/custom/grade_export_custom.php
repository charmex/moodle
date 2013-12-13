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

require_once($CFG->dirroot . '/grade/export/lib.php');

class grade_export_custom extends grade_export {

    public $plugin = 'custom';
    protected $DEBUG = false;
    protected $DEBUG_custom = false;
    
    

    /**
     * To be implemented by child classes
     */
    
    
    public function print_grades() {
        global $CFG;
        global $DB;
        require_once($CFG->dirroot . '/lib/excellib.class.php');
        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

        // Calculate file name
        $filename = $strgrades;
        if (count($this->courses) == 1) {
            $filename = format_string(reset($this->courses)->shortname, true, array('context' => context_course::instance(reset($this->courses)->id)));
            $filename .= " {$strgrades}";
        }
        $downloadfilename = clean_filename("{$filename}.custom");
        // Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers
        $workbook->send($downloadfilename);
        // Adding the worksheet
        $mycustom = $workbook->add_worksheet($strgrades);

        // Print names of all the fields
        $courseids = array_map(create_function('$course', 'return $course->id;'), $this->courses);
        $profilefields = grade_helper::get_user_profile_fields($courseids, $this->usercustomfields);
        foreach ($profilefields as $id => $field) {
            $mycustom->write_string(0, $id, $field->fullname);
        }
        $pos = count($profilefields);
        if (!$this->onlyactive) {
            $mycustom->write_string(0, $pos++, get_string("suspended"));
        }
        $mycustom->write_string(0, $pos++, get_string("institution"));

        foreach ($this->columns as $grade_item) {
            $courseC = $DB->get_record('course', array('id' => $grade_item->courseid));
            //print_object($grade_item);
//            $mycustom->write_string(0, $pos++, $this->format_column_name($grade_item));
//            $mycustom->write_string(0, $pos++, $courseC->fullname);
            $mycustom->write_custom_string(0, $pos++, $courseC->fullname);
            $mycustom->write_string(0, $pos++, "Fecha");
            // Add a column_feedback column
            if ($this->export_feedback) {
                $mycustom->write_string(0, $pos++, $this->format_column_name($grade_item, true));
            }
        }

        // Print all the lines of data.
        $i = 0;
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->courses, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->allow_user_custom_fields($this->usercustomfields);
        $gui->init();

        if ($this->DEBUG) {
            echo "Initiating debug process: Starting with the userdata" . "<br/>";
        }

        
        while ($userdata = $gui->next_user()) {
            if (!is_null($this->userlist) && $this->userlist != '' && $this->userlist != $userdata->user->id) {
                continue;
            }
            if ($this->DEBUG) {
                echo "==========================" . "<br/>";
                echo "userdata" . "<br/>";
                print_object($userdata);
            }
            $i++;
            $user = $userdata->user;

            foreach ($profilefields as $id => $field) {
                $fieldvalue = grade_helper::get_user_field_value($user, $field);
                $mycustom->write_string($i, $id, $fieldvalue);
            }
            $j = count($profilefields);
            if (!$this->onlyactive) {
                $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                $mycustom->write_string($i, $j++, $issuspended);
            }
//                print_object($user);
            $puesto = $user->institution;
            $mycustom->write_string($i, $j++, $puesto);
            //print_object($userdata);
            foreach ($userdata->grades as $itemid => $grade) {
                if ($export_tracking) {
                    $status = $geub->track($grade);
                }

                $gradestr = $this->format_grade($grade);
                if (is_numeric($gradestr)) {
                    $mycustom->write_number($i, $j++, $gradestr);
                } else {
                    $mycustom->write_string($i, $j++, $gradestr);
                }
                if ($this->DEBUG) {
                    echo "grade object information" . "<br/>";
                    print_object($grade);
                    echo "Username" . "<br/>";
                    echo $user->username . "<br/>";
                    echo "grade item information" . "<br/>";
                    print_object($grade_item);
                }

                $_record_sql = $DB->get_record_sql('select cc.userid,gg.itemid,cc.timecompleted,cc.course
                    FROM mdl_course_completions cc
                    LEFT JOIN mdl_grade_grades gg ON cc.userid = gg.userid
                    LEFT JOIN mdl_grade_items gi ON gg.itemid = gi.id
                    WHERE gi.id = ?
                    AND cc.userid = ?
                    AND gi.itemtype = ?
                    AND cc.course = gi.courseid
                    ', array($grade->itemid, $user->id, "course"));

                //$mycustom->write_date($i, $j++, $grade->timemodified);
                if (!is_bool($_record_sql) && !is_null($_record_sql->timecompleted)) {
                    if ($this->DEBUG || $this->DEBUG_custom) {
                        echo "custom sql result" . "<br/>";
                        print_object($_record_sql);
                    }

                    $mycustom->write_date($i, $j++, $_record_sql->timecompleted);
                } else {
                    if ($this->DEBUG_custom) {
                        echo "skip because doesn't have a result" . "<br/>";
                        if ($user->username == "dvigueros" || $user->username == "amar") {
                            print_object($user);
                        }
                    }

//                    if (is_null($_record_sql->course)) {
//                        $enrolledUser = false;
                    $enrolledUser = $this->check_enrollment($user->username, $_record_sql->course, $grade->itemid);
                    if ($this->DEBUG) {
                        echo "Enrolled user?";
                        print_object("$enrolledUser");
                    }
                    if (!is_null($enrolledUser) && is_bool($enrolledUser)) {
                        if ($enrolledUser) {
                            $mycustom->write_string($i, $j++, "-");
                        } else {
                            $mycustom->write_string($i, $j++, "N/A");
                        }
                    }
                }
// writing feedback if requested
            }
            if ($this->export_feedback) {
                $mycustom->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid]));
            }
        }
        if ($this->DEBUG || $this->DEBUG_custom) {
            die();
        }
        $gui->close();
        $geub->close();

        /// Close the workbook
        $workbook->close();

        exit;
    }

    function check_enrollment($username, $course, $gid) {
        global $DB;
//        $sql = "SELECT count(*)
//            FROM mdl_user_enrolments a,
//            mdl_enrol b,
//            mdl_user c
//
//            WHERE c.username='$username'
//            AND a.userid=c.id
//            AND b.courseid=$course
//            AND a.enrolid=b.id";
        $sql = "SELECT count(*)
	FROM mdl_course_completions cc
	LEFT JOIN mdl_user u ON cc.userid = u.id
        LEFT JOIN mdl_grade_items gi ON gi.courseid = cc.course
	WHERE u.username = '$username'
        AND gi.id = $gid;";
        $n = $DB->count_records_sql($sql);
        if ($n == 0) {
            //user not enrolled
            return False;
        } elseif ($n == 1) {
            //user already enrolled
            return True;
        } else {
            //, bad data ie<Data sanity not maintained>
            add_to_log($course, 'ERROR: check-enrollment', 'Entered into mordor code block');
            return False;
        }
    }

}

