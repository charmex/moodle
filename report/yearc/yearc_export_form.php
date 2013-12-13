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

//        $cats = get_categories();
//        print_object($cats);
//        $catlist = array();
//        foreach ($cats as $cat) {
//            $catlist[] = $cat->name;
//        }
//        
//        
//        $mform->addElement('select', 'catlist', 'Nombre de categoria', $catlist);



        $this->add_action_buttons(false, get_string('submit'));

        //$this->createYearlyReport();
//        print_object($_POST);
    }

    function createYearlyReport($params) {
        global $DB, $CFG;
        /*
         * $u[] Will contain user data as array, it will be handled as the user table to show.
         * $ycr[] Will contain courses datas as array, it will be handled as the course table to show.
         */


        //Gladys report to STPS
        if ($params->timeCheck == 1) {
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

//        if ($params->timelimit == 1 && ($params->timeend != $params->timestart)) {
//            //Different dates, therefore we assume we want to recover yearly reports
//            $qry .= " AND from_unixtime(cch.timecompleted, '%y') = from_unixtime(" . $params->timestart . ", '%y')";
//            if ($this->DEBUG) {
//                echo '$qry value when dates are different = ' . $qry;
//                echo "<br/>";
//            }
//        }
//            if ($params->userLimit == 1) {
//                $qry .= " AND u.id = " . $params->userList;
//            }
//
//            if ($params->courseLimit == 1) {
//                $qry .= " AND cch.course = " . $params->courseList;
//            }


            $qry .= " ORDER BY from_unixtime(timecompleted, '%Y-%m') , course;";
            $this->getRecordsFromSql($qry);

            if ($this->DEBUG) {
                $loopcount = 0;
            }
            foreach ($this->rows as $r) {
                if ($this->DEBUG) {
                    echo "Main \$loopcount : " . $loopcount++ . "<br/>";
                }
                $y = new Ycr();
                $y->ano = $r->ano;
                $y->mes = $r->mes;
                $y->course = $r->course;
                $y->coursename = $r->coursename;
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

                    $us = new User();
                    $us->userid = $r->uid;
                    $us->idnumber = $r->idnumber;
                    $us->url = $r->url;
                    $us->cid = $index;
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
                    $us = new User();
                    $us->userid = $r->uid;
                    $us->idnumber = $r->idnumber;
                    $us->url = $r->url;
                    $us->cid = $index;
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
        }
        //Course completion results, including who hasn't completed the course. This is a global report
        else if ($params->courseCheck == 1 && $params->deptCheck == 0) {
            $_course_participants = get_course_participants($params->courseList);
            $participants = array();
            foreach ($_course_participants as $cp) {
                $participants[$cp->id] = $cp;
            }
            /*
             * $sql = "SELECT cc.userid, u.firstname, u.lastname, u.idnumber, cc.timecompleted, u.department, u.institution
              FROM mdl_course_completions cc
              LEFT JOIN mdl_user u ON u.id = cc.userid
              WHERE cc.course = $params->courseList
              AND timecompleted > 0";
             */
            $sql = "SELECT @item := id FROM moodnew.mdl_grade_items where courseid = 13 and itemtype = \"mod\" and itemmodule = \"quiz\"";
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
//                $user->idnumber = $p->idnumber;
//                $user->firstname = $p->firstname;
//                $user->lastname = $p->lastname;
                $this->u[] = $user;
                $this->userNC[] = $user;
                if ($this->DEBUG) {
//                    echo 'Adding: ';
//                    print_object($user);
//                    print_object($this->u);
//                    echo '=============================';
                }
            }

            $this->ycr[0]->courseList = $params->courseList;
            $this->filter = 1;
            return;
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
        else if ($params->deptCheck == 1) {
            $sql = "SELECT id, username, email, firstname, lastname, city, country,
                    lastaccess, confirmed, mnethostid, suspended , department
                    FROM mdl_user
                    WHERE deleted <> 1 AND id <> 1";
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
                $sql = "SELECT cc.userid, u.firstname, u.lastname, u.idnumber, cc.timecompleted, u.department, u.institution
                    FROM mdl_course_completions cc
                    LEFT JOIN mdl_user u ON u.id = cc.userid
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
                        $user = new stdClass;
                        $user->idnumber = $r->idnumber;
                        $user->firstname = $r->firstname;
                        $user->lastname = $r->lastname;
                        $user->enddate = date('M-Y', $r->timecompleted);
                        $user->department = $r->department;
                        $user->institution = $r->institution;

                        $userCompleted[] = $user;
                        unset($participants[$r->userid]);
                    }
                }
                foreach ($participants as $p) {
                    if (isset($users[$p->id])) {


                        $userNC = array($p->idnumber, $p->firstname, $p->lastname, $p->department, $p->institution);
                        $userNotCompleted[] = $userNC;
                    }
                }
                $this->deptUserCompleted[$course->id] = $userCompleted;
                $this->deptUserNotCompleted[$course->id] = $userNotCompleted;
            }
            //
            //Filter to one course only
            if ($params->courseCheck == 1) {
                
            }
            $this->filter = 3;
            return;
        }
        //Show competency matrix for user X
        else if ($params->userCheck == 1) {

            //Get the courses completed by user
//            $sql = "SELECT cc.course as id, u.firstname, u.lastname, u.idnumber, cc.timecompleted, c.fullname, u.id userid, c.category
//                    FROM mdl_course_completions cc
//                    LEFT JOIN mdl_user u ON u.id = cc.userid
//                    LEFT JOIN mdl_course c ON c.id = cc.course
//                    WHERE cc.userid = $params->userList AND timecompleted > 0;
//                    ";

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
            return;
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
            $r = new Row();
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
}

class Row {

    public $uid;
    public $course;
    public $coursename;
    public $ano;
    public $mes;
    public $idnumber;
    public $url;
    public $timestamp;
    public $objective;
    public $hours;

    public function setAttr($row1, $row2, $row3, $row4, $row5) {
        $uid = $row1;
        $course = $row2;
        $ano = $row3;
        $mes = $row4;
        $idnumber = $row5;
    }

}

class User {

    public $id;
    public $cid;
    public $userid;
    public $idnumber;
    public $url;

}

class Ycr {

    public $id;
    public $cid;
    public $course;
    public $coursename;
    public $ano;
    public $mes;
    public $timestamp;
    public $objective;
    public $hours;

}

?>
