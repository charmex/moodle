<?php
//
//require('../config.php');
//require_once($CFG->libdir . '/clilib.php');
//
//createYearlyReport();
//$rows = array();
//$ycr = array();
//$u = array();
//
//function createYearlyReport() {
//    global $DB;
//    global $rows, $ycr, $u;
//
//    getRecordsFromSql(13);
//
//    foreach ($rows as $r) {
//        $y = new Ycr();
//        $y->ano = $r->ano;
//        $y->mes = $r->mes;
//        $y->course = $r->course;
//
//        $exists = false;
//
//        foreach ($ycr as $record) {
//            if ($record->ano == $y->ano && $record->mes == $y->mes && $record->course == $y->course) {
//                $exists = true;
//            } else {
//                $exists = false;
//            }
//        }
//
//        if ($exists) {
//            //get index and insert to actual
//            $index = 0;
//
//            foreach ($ycr as $record) {
//                if ($record->ano == $y->ano && $record->mes == $y->mes && $record->course == $y->course) {
//                    break;
//                } else {
//                    $index++;
//                }
//            }
//
//            $us = new User();
//            $us->userid = $r->uid;
//            $us->cid = $index;
//            $u[] = $us;
//        } else {
//            //create new one
//            echo 'Creating new record of ycr' . "\n";
//            $ycr[] = $y;
//            $index;
//            $found = false;
//            foreach ($ycr as $key => $value) {
//                if ($value->cid == $y->cid && $value->course == $y->course && $value->ano == $y->ano && $value->mes == $y->mes) {
//                    $found = true;
//                    $index = $key;
//                    break;
//                }
//            }
//            $us = new User();
//            $us->userid = $r->uid;
//            $us->cid = $index;
//            $u[] = $us;
//            $ycr[$index]->cid = $index;
//        }
//    }
//
//    print_object($ycr);
//    echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
//    print_object($u);
//
//    echo "<table>";
//    echo "<table border='1'>";
//    echo "<th>id</th>";
//    echo "<th>consecutive_id</th>";
//    echo "<th>course</th>";
//    echo "<th>year</th>";
//    echo "<th>month</th>";
//    foreach ($ycr as $key => $row) {
//        echo "<tr>";
//        foreach ($row as $key2 => $row2) {
//            echo "<td>" . $row2 . "</td>";
//        }
//        echo "</tr>";
//    }
//    echo "</table>";
//
//    echo "<table>";
//    echo "<table border='1'>";
//    echo "<table border='1'>";
//    echo "<th>id</th>";
//    echo "<th>consecutive_id</th>";
//    echo "<th>user_id</th>";
//    foreach ($u as $key => $row) {
//        echo "<tr>";
//        foreach ($row as $key2 => $row2) {
//            echo "<td>" . $row2 . "</td>";
//        }
//        echo "</tr>";
//    }
//    echo "</table>";
//}
//
//function getRecordsFromSql($anoC) {
//    global $DB;
//
//    $res;
//    if ($anoC != 0) {
//        $qry = "
//    SELECT 
//    userid,
//    course,
//    from_unixtime(timecompleted, '%y') as ano,
//    from_unixtime(timecompleted, '%M') as mes
//FROM
//    mdl_course_completion_history
//WHERE timecompleted > 0
//AND from_unixtime(timecompleted, '%y') = '" . $anoC . "'
//    ORDER BY from_unixtime(timecompleted, '%Y-%m') , course;";
//        ;
//        $res = $DB->get_records_sql($qry);
//    } else {
//        $qry = "
//    SELECT 
//    userid,
//    course,
//    from_unixtime(timecompleted, '%y') as ano,
//    from_unixtime(timecompleted, '%M') as mes
//FROM
//    mdl_course_completion_history
//WHERE timecompleted > 0 
//ORDER BY from_unixtime(timecompleted, '%Y-%m') , course;";
//        ;
//        $res = $DB->get_records_sql($qry);
//    }
//    global $rows;
////    print_object($res);
//    foreach ($res as $row) {
////        echo 'ok';
////        print_object($row):
//        $r = new Row();
//        $r->uid = $row->userid;
//        $r->course = $row->course;
//        $r->ano = $row->ano;
//        $r->mes = $row->mes;
////        print_object($r);
//        $rows[] = $r;
//    }
//}
//
//class Row {
//
//    public $uid;
//    public $course;
//    public $ano;
//    public $mes;
//
//    public function setAttr($row1, $row2, $row3, $row4) {
//        $uid = $row1;
//        $course = $row2;
//        $ano = $row3;
//        $mes = $row4;
//    }
//
//}
//
//class User {
//
//    public $id;
//    public $cid;
//    public $userid;
//
//}
//
//class Ycr {
//
//    public $id;
//    public $cid;
//    public $course;
//    public $ano;
//    public $mes;
//
//}
?>

