<?php

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/grade/lib.php');

class yearc_export_form extends moodleform {

    public $ycr = array();
    public $u = array();
    public $rows = array();
    public $DEBUG = false;
    public $filter = "";
    public $courseC = array();
    public $courseNC = array();
    public $userC = array();
    public $userNC = array();
    public $courseref = array();
    public $deptUserCompleted = array();
    public $deptUserNotCompleted = array();
    public $gradinginfo;

    function definition() {
        global $DB;


        $mform = & $this->_form;
        $mform->addElement('header', 'options', get_string('options', 'grades'));

        $mform->addElement('advcheckbox', 'timeCheck', 'Por fecha', '', array('group' => 1), array(0, 1));
        $mform->addElement('date_selector', 'timeStart', get_string('from'));
        $mform->addElement('date_selector', 'timeEnd', get_string('to'));

        $courses = get_courses();
        $courselist = array();
        foreach ($courses as $course) {
            if ($course->id != 1) {
                $courselist[$course->id] = $course->fullname;
            }
        }

        $mform->addElement('advcheckbox', 'courseCheck', 'Por curso', '', array('group' => 2), array(0, 1));
        //$mform->addElement('advcheckbox', 'coursefaltante', 'Faltantes', '', array('group' => 2), array(0, 1));
        $mform->addElement('select', 'courseList', 'Nombre de curso', $courselist);

        $users = get_users_listing();
        $userlist = array();
        foreach ($users as $user) {
            $userlist[$user->id] = $user->username;
        }
        $mform->addElement('advcheckbox', 'userCheck', 'Por usuario', '', array('group' => 3), array(0, 1));
        $mform->addElement('select', 'userList', 'Nombre de usuario', $userlist);


        $sql = "SELECT distinct(department)
                FROM mdl_user
                WHERE deleted <> 1 AND id <> 1";
        $departments = $DB->get_records_sql($sql);
        $deptlist = array();
        foreach ($departments as $d) {
            if ($d->department != '0' && $d->department != '') {
                $deptlist[$d->department] = $d->department;
            }
        }
        $mform->addElement('advcheckbox', 'deptCheck', 'Por departamento', '', array('group' => 4), array(0, 1));
        $mform->addElement('select', 'deptList', 'Nombre de departamento', $deptlist);

        $this->add_action_buttons(false, get_string('submit'));
    }

    /*
     * $u[] Will contain user data as array, it will be handled as the user table to show.
     * $ycr[] Will contain courses datas as array, it will be handled as the course table to show.
     */

    function createYearlyReport($params) {
        if ($params->timeCheck == 1) {
            $this->yearlyReport($params);
            return 0;
        } else if ($params->courseCheck == 1 && $params->deptCheck == 0) {
            $this->courseCompetency($params);
            return 0;
        } else if ($params->deptCheck == 1) {
            $this->departmentCompetency($params);
            return 0;
        } else if ($params->userCheck == 1) {
            $this->userCompetency($params);
            return 0;
        }
    }

    function getRecordsFromSql($qry) {
        global $DB;
        $res = $DB->get_records_sql($qry);
        if ($this->DEBUG) {
            echo "Results from qry: " . "<br/>";
            print_object($res);
        }
        foreach ($res as $row) {
            $r = new stdClass;
            $r->uid = $row->userid;
            $r->url = $row->url;
            $r->idnumber = $row->idnumber;
            $r->course = $row->course;
            $r->coursename = $row->fullname;
            $r->ano = $row->ano;
            $r->mes = $row->mes;
            $r->timestamp = $row->timecompleted;
            $r->objective = $row->objective;
            $r->hours = $row->hours;
            $this->rows[] = $r;
        }
    }

    //Course completion results, including who hasn't completed the course. This is a global report
    function courseCompetency($params) {
        global $DB;

        $_course_participants = get_course_participants($params->courseList);
        $participants = array();
        foreach ($_course_participants as $cp) {
            $participants[$cp->id] = $cp;
        }

        //Set in database variable @item the id of the quiz
        $sql = "SELECT @item := id FROM moodle.mdl_grade_items where courseid = $params->courseList and itemtype = \"mod\" and itemmodule = \"quiz\"";
        $res = $DB->get_record_sql($sql);

        $sql = "SELECT cc.userid, u.firstname, u.lastname, u.idnumber, cc.timecompleted, u.department, u.institution, gg.finalgrade, gi.id itemid,
		qa.uniqueid qattempt, cc.course courseid, gi.iteminstance
                FROM mdl_course_completions cc
                LEFT JOIN mdl_user u ON u.id = cc.userid
		LEFT JOIN mdl_grade_items gi ON gi.courseid = cc.course AND gi.itemtype=\"mod\" AND gi.itemmodule=\"quiz\"
		LEFT JOIN mdl_grade_grades gg ON gg.itemid = @item AND cc.userid = gg.userid
		LEFT JOIN mdl_quiz_attempts qa ON qa.userid = u.id AND gi.iteminstance = qa.quiz
                WHERE cc.course = $params->courseList
                AND timecompleted > 0;";
        $res = $DB->get_records_sql($sql);

        $completed = array();
        foreach ($res as $r) {
            $completed[$r->userid] = $r->userid;
            $attempt = $res[$r->userid]->qattempt;
            $linkpar['attempt'] = $attempt;
            $link = "";
            if (isset($attempt)) {
                $link = "<a href=\"";
                $link .= new moodle_url('/mod/quiz/review.php', $linkpar);
                $link .= "\">";
                $link .= "<img src=\"..\..\pix\c\site.png\">";
                $link .= "</a>";
            }
            $user = new stdClass;
            $user->idnumber = $r->idnumber;
            $user->firstname = $r->firstname;
            $user->lastname = $r->lastname;
            $user->enddate = date('M-Y', $r->timecompleted);
            $user->department = $r->department;
            $user->institution = $r->institution;
            $user->link = $link;
            $this->userC[] = $user;
        }
        if ($this->DEBUG) {
            print_object($completed);
            print_object($this->userC);
        }
        foreach ($completed as $c) {
            unset($participants[$c]);
        }
        if ($this->DEBUG) {
            print_object($participants);
        }
        //Participant now contains the ones that haven't completed the course
        //Now we have to transfer to the $u
        $sql = "SELECT cc.userid, u.firstname, u.lastname, u.idnumber, cc.timecompleted, u.department, u.institution, gg.finalgrade, gi.id itemid,
		qa.uniqueid qattempt, cc.course courseid, gi.iteminstance
                FROM mdl_course_completions cc
                LEFT JOIN mdl_user u ON u.id = cc.userid
		LEFT JOIN mdl_grade_items gi ON gi.courseid = cc.course AND gi.itemtype=\"mod\" AND gi.itemmodule=\"quiz\"
		LEFT JOIN mdl_grade_grades gg ON gg.itemid = @item AND cc.userid = gg.userid
		LEFT JOIN mdl_quiz_attempts qa ON qa.userid = u.id AND gi.iteminstance = qa.quiz
                WHERE cc.course = $params->courseList";
        $res = $DB->get_records_sql($sql);
        foreach ($participants as $p) {
            $attempt = $res[$p->id]->qattempt;
            $itemid = $res[$p->id]->itemid;
            $courseid = $res[$p->id]->courseid;
            $linkpar['attempt'] = $attempt;
            $link = "";
            if (isset($attempt)) {
                $link = "<a href=\"";
                $link .= new moodle_url('/mod/quiz/review.php', $linkpar);
                $link .= "\">";
                $link .= "<img src=\"..\..\pix\c\site.png\">";
                $link .= "</a>";
            }
            $user = array($p->idnumber, $p->firstname, $p->lastname, $p->department, $p->institution, $link);
            $this->u[] = $user;
            $this->userNC[] = $user;
        }

        $this->ycr[0]->courseList = $params->courseList;
        $this->filter = 1;
        return 0;
    }

    /* Department completion results, includes all course reports of every user
     * Should be:
     * Course: X
     * Completed:
     * idnumber|name|lastname|enddate|institution
     * ---------
     * Not Completed:
     * idnumber|name|lastname|institution
     * -----------
     */// 

    function departmentCompetency($params) {
        global $DB;

        $sql = "SELECT id, username, email, firstname, lastname, city, country,
                    lastaccess, confirmed, mnethostid, suspended , department
                    FROM mdl_user
                    WHERE deleted <> 1 AND id <> 1 AND suspended <> 1";
        //Filter the users by department
        $users = $DB->get_records_sql($sql);
        foreach ($users as $u) {
            if ($u->department != $params->deptList) {
                unset($users[$u->id]);
            }
        }

        //Make a list of all the courses taken by all the users
        $listofcourses = array();
        foreach ($users as $u) {
            $enrolled = enrol_get_all_users_courses($u->id);
            if (!is_null($enrolled)) {
                foreach ($enrolled as $e) {
                    if (is_null($listofcourses[$e->id])) {
                        $listofcourses[$e->id] = $e;
                    }
                }
            }
        }
        //Get the results of each course
        foreach ($listofcourses as $course) {
            $_course_participants = get_course_participants($course->id);
            $participants = array();
            foreach ($_course_participants as $cp) {
                if ($cp->department == $params->deptList) {
                    $participants[$cp->id] = $cp;
                }
            }
            $sql = null;
            $res = null;
            $sql = "SELECT @item := id FROM moodle.mdl_grade_items where courseid = $course->id and itemtype = \"mod\" and itemmodule = \"quiz\"";
            $res = $DB->get_record_sql($sql);
            $sql = "SELECT cc.userid, u.firstname, u.lastname, u.idnumber, cc.timecompleted, u.department, u.institution, gg.finalgrade, gi.id itemid,
		qa.uniqueid qattempt, cc.course courseid, gi.iteminstance
                    FROM mdl_course_completions cc
                    LEFT JOIN mdl_user u ON u.id = cc.userid
                    LEFT JOIN mdl_grade_items gi ON gi.courseid = cc.course AND gi.itemtype=\"mod\" AND gi.itemmodule=\"quiz\"
                    LEFT JOIN mdl_grade_grades gg ON gg.itemid = @item AND cc.userid = gg.userid
                    LEFT JOIN mdl_quiz_attempts qa ON qa.userid = u.id AND gi.iteminstance = qa.quiz
                    WHERE cc.course = $course->id
                    AND timecompleted > 0";
            $res = $DB->get_records_sql($sql);

            $userCompleted = array();
            $userNotCompleted = array();
            $completed = array();


            if (isset($res)) {
                $this->courseref[] = $course->id;
            }

            foreach ($res as $r) {
                if (isset($users[$r->userid])) {
                    $completed[$r->userid] = $r->userid;
                    $attempt = $res[$r->userid]->qattempt;
                    $linkpar['attempt'] = $attempt;
                    $link = "";
                    if (isset($attempt)) {
                        $link = "<a href=\"";
                        $link .= new moodle_url('/mod/quiz/review.php', $linkpar);
                        $link .= "\">";
                        $link .= "<img src=\"..\..\pix\c\site.png\">";
                        $link .= "</a>";
                    }
                    $user = new stdClass;
                    $user->idnumber = $r->idnumber;
                    $user->firstname = $r->firstname;
                    $user->lastname = $r->lastname;
                    $user->enddate = date('M-Y', $r->timecompleted);
                    $user->department = $r->department;
                    $user->institution = $r->institution;
                    $user->link = $link;
                    $userCompleted[] = $user;
                    unset($participants[$r->userid]);
                }
            }
            //Participant now contains the ones that haven't completed the course
            //Now we have to transfer to the $u
            $sql = "SELECT cc.userid, u.firstname, u.lastname, u.idnumber, cc.timecompleted, u.department, u.institution, gg.finalgrade, gi.id itemid,
		qa.uniqueid qattempt, cc.course courseid, gi.iteminstance
                FROM mdl_course_completions cc
                LEFT JOIN mdl_user u ON u.id = cc.userid
		LEFT JOIN mdl_grade_items gi ON gi.courseid = cc.course AND gi.itemtype=\"mod\" AND gi.itemmodule=\"quiz\"
		LEFT JOIN mdl_grade_grades gg ON gg.itemid = @item AND cc.userid = gg.userid
		LEFT JOIN mdl_quiz_attempts qa ON qa.userid = u.id AND gi.iteminstance = qa.quiz
                WHERE cc.course = $course->id";
            $res = $DB->get_records_sql($sql);
            foreach ($participants as $p) {
                if (isset($users[$p->id])) {
                    $attempt = $res[$p->id]->qattempt;
                    $linkpar['attempt'] = $attempt;
                    $link = "";
                    if (isset($attempt)) {
                        $link = "<a href=\"";
                        $link .= new moodle_url('/mod/quiz/review.php', $linkpar);
                        $link .= "\">";
                        $link .= "<img src=\"..\..\pix\c\site.png\">";
                        $link .= "</a>";
                    }
                    $userNC = array($p->idnumber, $p->firstname, $p->lastname, $p->department, $p->institution, $link);
                    $userNotCompleted[] = $userNC;
                }
            }
            $this->deptUserCompleted[$course->id] = $userCompleted;
            $this->deptUserNotCompleted[$course->id] = $userNotCompleted;
        }
        
        //Filter to one course only
        if ($params->courseCheck == 1) {
            
        }
        $this->filter = 3;
        return 0;
    }

    //Show competency matrix for user X
    function userCompetency($params) {
        global $DB;

        $sql = "SELECT u.firstname, u.lastname, u.idnumber, u.id userid FROM mdl_user u WHERE u.id = $params->userList";
        $useractual = new stdClass;
        $res = $DB->get_record_sql($sql);
        $useractual->userid = $res->userid;
        $useractual->firstname = $res->firstname;
        $useractual->lastname = $res->lastname;
        $useractual->idnumber = $res->idnumber;
        $sql = "SELECT cc.course as id, u.firstname, u.lastname, u.idnumber, cc.timecompleted, c.fullname, u.id userid, c.category, gi.iteminstance, gi.id itemid, qa.uniqueid qattempt
            FROM mdl_course_completions cc
            LEFT JOIN mdl_user u ON u.id = cc.userid
            LEFT JOIN mdl_course c ON c.id = cc.course
            LEFT JOIN mdl_grade_items gi ON gi.courseid = cc.course AND gi.itemtype = \"mod\" AND gi.itemmodule = \"quiz\"
            LEFT JOIN mdl_quiz_attempts qa ON qa.userid = u.id AND gi.iteminstance = qa.quiz
            WHERE cc.userid = $params->userList AND timecompleted > 0;";
        $res = $DB->get_records_sql($sql);

        if ($this->DEBUG) {
            print_object($sql);
            print_object($res);
        }

        $user = new stdClass;
        $user = array($useractual->idnumber, $useractual->firstname, $useractual->lastname);
        foreach ($res as $r) {
            $category = $DB->get_record('course_categories', array('id' => $r->category));
            $y = new stdClass;
            $y->fullname = $r->fullname;
            $y->timecompleted = date("M-Y", $r->timecompleted);
            $y->category = $category->name;
            $attempt = $r->qattempt;
            $linkpar['attempt'] = $attempt;
            $link = "";
            if (isset($attempt)) {
                $link = "<a href=\"";
                $link .= new moodle_url('/mod/quiz/review.php', $linkpar);
                $link .= "\">";
                $link .= "<img src=\"..\..\pix\c\site.png\">";
                $link .= "</a>";
            }
            $y->link = $link;
            $this->courseC[] = $y;
        }
        //Now get the courses that the user hasn't completed
        $courses = enrol_get_all_users_courses($params->userList, true);

        foreach ($courses as $c) {
            foreach ($res as $cc) {
                if ($cc->id == $c->id) {
                    $key = $cc->id;
                    unset($courses[$key]);
                }
            }
        }
        $courses_imploded = "";
        foreach ($courses as $c) {
            $courses_imploded .= $c->id . ",";
        }
        $courses_imploded .= "0";

        $sql = "SELECT cc.course as id, u.firstname, u.lastname, u.idnumber, cc.timecompleted, c.fullname, u.id userid, c.category, gi.iteminstance, gi.id itemid, qa.uniqueid qattempt
                FROM mdl_course_completions cc
                LEFT JOIN mdl_user u ON u.id = cc.userid
                LEFT JOIN mdl_course c ON c.id = cc.course
		LEFT JOIN mdl_grade_items gi ON gi.courseid = cc.course AND gi.itemtype=\"mod\" AND gi.itemmodule=\"quiz\"
		LEFT JOIN mdl_quiz_attempts qa ON qa.userid = u.id AND gi.iteminstance = qa.quiz
                WHERE cc.userid = $params->userList
		AND cc.course IN ($courses_imploded);";
        $res = $DB->get_records_sql($sql);

        foreach ($courses as $c) {
            $category = $DB->get_record('course_categories', array('id' => $c->category));
            $y = new stdClass;
            $y->fullname = $c->fullname;
            $y->shortname = $c->shortname;
            $y->category = $category->name;
            $attempt = $res[$c->id]->qattempt;
            $linkpar['attempt'] = $attempt;
            $link = "";
            if (isset($attempt)) {
                $link = "<a href=\"";
                $link .= new moodle_url('/mod/quiz/review.php', $linkpar);
                $link .= "\">";
                $link .= "<img src=\"..\..\pix\c\site.png\">";
                $link .= "</a>";
            }
            $y->link = $link;
            $this->courseNC[] = $y;
        }

        $this->u[] = $user;
        $this->filter = 2;
        return 0;
    }

    //Gladys report to STPS
    function yearlyReport($params) {

        $qry = "SELECT
        cch.userid,
        cch.course,
        u.id,
        u.idnumber,
        u.url,
        COALESCE(cfo1.value, 0) AS objective,
        COALESCE(cfo2.value, 0) AS hours,
        c.fullname,
        cch.timecompleted,
        from_unixtime(cch.timecompleted, '%y') as ano,
        from_unixtime(cch.timecompleted, '%M') as mes
        FROM
        mdl_course_completion_history as cch
        LEFT JOIN
        mdl_user as u
        ON u.id = cch.userid
        LEFT JOIN
        mdl_course as c
        ON c.id = cch.course
        LEFT JOIN
        mdl_course_format_options cfo1 ON c.id = cfo1.courseid AND cfo1.name = \"objective\"
LEFT JOIN
    mdl_course_format_options cfo2 ON c.id = cfo2.courseid AND cfo2.name = \"hours\"
    WHERE timecompleted > 0
";

        if ($this->DEBUG) {
            echo "params values listed: " . "<br/>";
            echo "Timestart: " . $params->timeStart . "<br/>";
            echo "Timeend: " . $params->timeEnd . "<br/>";
            $one = $params->timeEnd;
            $two = $params->timeStart;
            echo "Start != End: " . ($params->timeEnd != $params->timeStart);
            echo "<br/>";
            echo "Start == End: " . ($params->timeEnd == $params->timeStart);
            echo "<br/>";
        }

        if ($params->timeLimit == 1 && ($params->timeEnd == $params->timeStart)) {
            //Same date therefore same month and year so show month filtering
            $qry .= " AND from_unixtime(cch.timecompleted, '%y') = from_unixtime(" . $params->timeStart . ",'%y')";
            $qry .= " AND from_unixtime(cch.timecompleted, '%M') = from_unixtime(" . $params->timeStart . ",'%M')";
            if ($this->DEBUG) {
                echo '$qry value when dates are equal = ' . $qry;
                echo "<br/>";
            }
        }

        /*
         * Experimental:: Find all the reports between the 2 dates selected
         */
        if ($params->timeLimit == 1 && ($params->timeEnd != $params->timeStart)) {
            //Different dates, therefore we assume we want to recover yearly reports
            $start = $params->timeStart;
            $end = $params->timeEnd;
            $qry .= " AND cch.timecompleted >= " . $start;
            $qry .= " AND cch.timecompleted <= " . $end;
            if ($this->DEBUG) {
                echo '$qry value when dates are different = ' . $qry;
                echo "<br/>";
            }
        }

        $qry .= " ORDER BY from_unixtime(timecompleted, '%Y-%m') , course;";
        $this->getRecordsFromSql($qry);

        if ($this->DEBUG) {
            $loopcount = 0;
        }
        foreach ($this->rows as $r) {
            if ($this->DEBUG) {
                echo "Main \$loopcount : " . $loopcount++ . "<br/>";
            }
            $y = new stdClass;
            $y->cid = "";
            $y->course = $r->course;
            $y->coursename = $r->coursename;
            $y->ano = $r->ano;
            $y->mes = $r->mes;
            $y->timestamp = date("M/Y", $r->timestamp);
            $y->objective = $r->objective;
            $y->hours = $r->hours;

            if ($this->DEBUG) {
                echo "Current row to insert" . "<br/>";
                print_object($r);
            }
            $exists = false;

            foreach ($this->ycr as $record) {
                //find if we already have, inserting the pair course:date
                if ($this->DEBUG) {
                    echo "\$record to check: " . "<br/>";
                    print_object($record);
                    echo "Looking for pairs course:date" . "<br/>";
                }
                if ($record->ano == $y->ano && $record->mes == $y->mes && $record->course == $y->course) {
                    $exists = true;
                } else {
                    $exists = false;
                }
            }

            if ($exists) {
                //get index and insert to actual
                $index = 0;

                foreach ($this->ycr as $record) {
                    if ($record->ano == $y->ano && $record->mes == $y->mes && $record->course == $y->course) {
                        break;
                    } else {
                        $index++;
                    }
                }

                $us = new stdClass;
                $us->cid = $index;
                //$us->userid = $r->uid;
                $us->idnumber = $r->idnumber;
                $us->url = $r->url;

                if ($this->DEBUG) {
                    echo "Adding user: " . "<br/>";
                    print_object($us);
                }
                $this->u[] = $us;
            } else {
                //create new one
                if ($this->DEBUG) {
                    echo "Could not find pair course:date" . "<br/>";
                    echo "Creating new record of ycr" . "<br/>";
                }
                $this->ycr[] = $y;

                $index;
                $found = false;
                foreach ($this->ycr as $key => $value) {
                    if ($value->cid == $y->cid && $value->course == $y->course && $value->ano == $y->ano && $value->mes == $y->mes) {
                        $found = true;
                        $index = $key;
                        break;
                    }
                }
                if ($this->DEBUG) {
                    print_object("New record of ycr is : " . $index);
                }
                $us = new stdClass;
                $us->cid = $index;
                //$us->userid = $r->uid;
                $us->idnumber = $r->idnumber;
                $us->url = $r->url;

                if ($this->DEBUG) {
                    echo "Adding user: " . "<br/>";
                    print_object($us);
                }
                $this->u[] = $us;
                $this->ycr[$index]->cid = $index;
            }
            if ($this->DEBUG) {
                echo "END OF LOOP" . "<br/>";
                echo "========================================" . "<br/>";
            }
        }
        if ($this->DEBUG) {
            print_object($this->ycr);
            echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
            print_object($this->u);
        }


        //Gladys report to only report certain course
        if ($params->courseCheck == 1) {
            
        }
        //Gladys report to only report a certain department
        if ($params->deptCheck) {
            
        }

        //Fix index values to a friendlier version

        foreach ($this->ycr as $key => $i) {
            $i->cid = $i->cid + 1;
        }

        foreach ($this->u as $key => $i) {
            $i->cid = $i->cid + 1;
        }

        $this->filter = 0;
        return 0;
    }

}

?>
