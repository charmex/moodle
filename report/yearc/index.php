<?php

require_once('/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once $CFG->dirroot . '/grade/lib.php';
require_once('yearc_export_form.php');



$year = optional_param('year', '', PARAM_ALPHA);

$params = array();
if (!empty($year)) {
    $params['year'] = $year;
}

$PAGE->set_url('/report/yearc/index.php', $params);
$PAGE->set_pagelayout('report');

require_login();
$systemcontext = context_system::instance();
require_capability('report/yearc:view', $systemcontext);

admin_externalpage_setup('reportyearc');


echo $OUTPUT->header();

//Instantiate simplehtml_form 
$mform = new yearc_export_form();
$attrib = array("style" => "table-layout:fixed; width:740px;");
$divattrib = array("style" => "overflow-x:scroll; width:100%");
//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($data = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    //print_object($data);
    
    $mform->createYearlyReport($data);
    if ($mform->filter == 1) {
        //Course completency
        $mform->display();
        $courseid = $mform->ycr[0]->courseList;
        $_course = get_course($courseid);
        echo 'Listado de completeo del curso ' . $_course->fullname;
        $table = new html_table('FilterOneUserCompleted');
        $table->attributes = $attrib;
        $table->head = array("# de empleado", "Nombre", "Apellido", get_string('end_date', 'report_yearc'), get_string('department'), get_string('institution'), "Examen");
        $table->data = $mform->userC;
        $table2 = new html_table('FilterOneUserNotCompleted');
        $table2->attributes = $attrib;
        $table2->head = array("# de empleado", "Nombre", "Apellido", "Departamento", "Puesto", "Examen");
        $table2->data = $mform->userNC;
        echo "<br>";
        echo 'Usuarios que completaron el curso: ';
        //Write table inside a scrollable wrapper div
        echo html_writer::div(html_writer::table($table), "scroll-wrap", $divattrib);
        echo 'Usuarios que no han completado el curso: ';
        echo html_writer::div(html_writer::table($table2), "scroll-wrap", $divattrib);
    } else if ($mform->filter == 2) {
        //Competency matrix for X user
        $mform->display();
        $table = new html_table('FilterTwoUsers');
        $table->attributes = $attrib;
        $table->head = array("# de empleado", "Nombre", "Apellido");
        $table->data = $mform->u;
        $table2 = new html_table('FilterTwoCoursesCompleted');
        $table2->attributes = $attrib;
        $table2->head = array(get_string('course_name', 'report_yearc'), get_string('end_date', 'report_yearc'), get_string('category'), "Examen");
        $table2->data = $mform->courseC;
        $table3 = new html_table('FilterTwoCoursesNotCompleted');
        $table3->attributes = $attrib;
        $table3->head = array(get_string('course_name', 'report_yearc'), get_string('short_name', 'report_yearc'), get_string('category'), "Examen");
        $table3->data = $mform->courseNC;
        echo "Usuario: ";
        echo html_writer::div(html_writer::table($table), "scroll-wrap", $divattrib);
        echo "Lista de cursos completados: ";
        echo html_writer::div(html_writer::table($table2), "scroll-wrap", $divattrib);
        echo "Lista de cursos no completados: ";
        echo html_writer::div(html_writer::table($table3), "scroll-wrap", $divattrib);
        echo $mform->gradinginfo;
    } else if ($mform->filter == 3) {
        //Department competency
        $mform->display();
        $courseids = $mform->courseref;
        foreach ($courseids as $cids) {
            $_course = get_course($cids);
            echo 'Listado de completeo del curso ' . $_course->fullname;
            $table = new html_table('FilterThreeDeptCompleted');
            $table->attributes = $attrib;
            $table->head = array("# de empleado", "Nombre", "Apellido", get_string('end_date', 'report_yearc'), get_string('department'), get_string('institution'));
            $table->data = $mform->deptUserCompleted[$cids];
            $table2 = new html_table('FilterOneUserNotCompleted');
            $table2->attributes = $attrib;
            $table2->head = array("# de empleado", "Nombre", "Apellido", "Departamento", "Puesto");
            $table2->data = $mform->deptUserNotCompleted[$cids];
            echo "<br>";
            echo 'Usuarios que completaron el curso: ';
            echo html_writer::div(html_writer::table($table), "scroll-wrap", $divattrib);
            echo 'Usuarios que no han completado el curso: ';
            echo html_writer::div(html_writer::table($table2), "scroll-wrap", $divattrib);
        }
    } else {
        $mform->display();
//    print_object($mform->ycr);
//    $mform->addElement('header', 'preview', 'Preview');
        $table = new html_table('tableData');
        $table->attributes = $attrib;
        $table->head = array('id', get_string('consecutivecourse', 'report_yearc'), get_string('course'), get_string('course_name', 'report_yearc'), get_string('year'), get_string('month'), get_string('end_date', 'report_yearc'), get_string('objective', 'report_yearc'), get_string('hours', 'report_yearc'));
        $table->data = $mform->ycr;
        $table2 = new html_table('tableDataa');
        $table2->attributes = $attrib;
        $table2->head = array('id', get_string('consecutivecourse', 'report_yearc'), get_string('userid', 'report_yearc'), get_string('employeenumber', 'report_yearc'), get_string('webpage', 'report_yearc'));
        $table2->data = $mform->u;
//    $mform->addElement('html', '<div class="testHeader">');
//    $mform->addElement('html', html_writer::table($table));

        echo html_writer::div(html_writer::table($table), "scroll-wrap", $divattrib);
        echo html_writer::div(html_writer::table($table2), "scroll-wrap", $divattrib);
        $filename = outputCSV($table, $table2);
        echo html_writer::link($filename, get_string("download_c", "report_yearc"));
    }
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    //Set default data (if any)
    //displays the form
//    createYearlyReport();
    $mform->display();
}
echo $OUTPUT->footer();

function Download($path, $speed = null) {
    if (is_file($path) === true) {
        set_time_limit(0);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $size = sprintf('%u', filesize($path));
        $speed = (is_null($speed) === true) ? $size : intval($speed) * 1024;

        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . $size);
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Transfer-Encoding: binary');

        for ($i = 0; $i <= $size; $i = $i + $speed) {
            echo file_get_contents($path, false, null, $i, $speed);

            flush();
            sleep(1);
        }

        exit();
    }

    return false;
}

function outputCSV($table, $table2) {
    $filename = "yearc-" . date("M-Y") . ".csv";
    $fp = fopen($filename, 'w');

    fputcsv($fp, $table->head);
    foreach ($table->data as $fields) {
        $fields = array_values((array) $fields);
        fputcsv($fp, $fields);
    }

    $empty = array();
    fputcsv($fp, $empty);
    fputcsv($fp, $empty);


    fputcsv($fp, $table2->head);
    foreach ($table2->data as $fields) {
        $fields = array_values((array) $fields);
        fputcsv($fp, $fields);
    }

    fclose($fp);
    return $filename;
}

?>