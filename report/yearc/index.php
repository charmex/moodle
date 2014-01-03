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
        courseHeader($mform->ycr[0]->courseList);
        $table = buildTable('course1', "FilterOneUserCompleted", $mform->userC);
        $table2 = buildTable('course2', "FilterOneUserNotCompleted", $mform->userNC);
        drawTable("Usuarios que completaron el curso", $table, "yearc_table");
        drawTable("Usuarios que no han completado el curso", $table2, "yearc_table");
    } else if ($mform->filter == 2) {
        //Competency matrix for X user
        $mform->display();
        $table = buildTable('user1', "FilterTwoUsers", $mform->u);
        $table2 = buildTable('user2', "FilterTwoCoursesCompleted", $mform->courseC);
        $table3 = buildTable('user2', "FilterTwoCoursesNotCompleted", $mform->courseNC);
        drawTable("Usuario:", $table, "yearc_table");
        drawTable("Lista de cursos completados:", $table2, "yearc_table");
        drawTable("Lista de cursos no completados:", $table3, "yearc_table");
        echo $mform->gradinginfo;
    } else if ($mform->filter == 3) {
        //Department competency
        $mform->display();
        $courseids = $mform->courseref;
        foreach ($courseids as $cids) {
            echo html_writer::start_div("bgpt");
            courseHeader($cids);
            echo html_writer::end_div();
            $table = buildTable('course1', "FilterThreeDeptCompleted", $mform->deptUserCompleted[$cids]);
            $table2 = buildTable('course2', "FilterOneUserNotCompleted", $mform->deptUserNotCompleted[$cids]);
            drawTable("Usuarios que completaron el curso: ", $table, "yearc_table");
            drawTable("Usuarios que no han completado el curso: ", $table2, "yearc_table");
        }
    } else {
        $mform->display();
//    print_object($mform->ycr);
//    $mform->addElement('header', 'preview', 'Preview');
        $table = new html_table('tableData');
        $table->attributes = $attrib;
        $table->head = array(get_string('consecutivecourse', 'report_yearc'), get_string('course'), get_string('course_name', 'report_yearc'), get_string('year'), get_string('month'), get_string('end_date', 'report_yearc'), get_string('objective', 'report_yearc'), get_string('hours', 'report_yearc'));
        $table->data = $mform->ycr;
        $table2 = new html_table('tableDataa');
        $table2->attributes = $attrib;
        $table2->head = array(get_string('consecutivecourse', 'report_yearc'), get_string('employeenumber', 'report_yearc'), get_string('webpage', 'report_yearc'));
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

function courseHeader($cid) {
//    $courseid = $mform->ycr[0]->courseList;
    $courseid = $cid;
    $linkC = "";
    $linkpar['id'] = $courseid;
    if (isset($courseid)) {
        $linkC = "<a href=\"";
        $linkC .= new moodle_url('/grade/report/grader/index.php', $linkpar);
        $linkC .= "\">";
        $linkC .= "<img src=\"..\..\pix\c\site.png\">";
        $linkC .= "</a>";
    }
    $_course = get_course($courseid);
    echo "\n " . "<h1>" . $_course->fullname . "</h1>";
    echo "<h2>Enlace a calificaciones del curso: " . $linkC . "</h2>";
}

function drawTable($header, $table, $title) {
    $divattrib = array("style" => "overflow-x:scroll; width:100%");
    echo "<h2>" . $header . "</h2>";
    echo html_writer::div(html_writer::table($table, $title), "scroll-wrap", $divattrib);
}

function buildTable($filter, $name, $data) {
    $attrib = array("id" => "yearc_table");
    $head;
    switch ($filter) {
        case 'course1':
            $head = array('# de empleado', 'Nombre', 'Apellido', 'Fecha de termino', 'Departamento', 'Puesto', 'Examen');
            break;
        case 'course2':
            $head = array('# de empleado', 'Nombre', 'Apellido', 'Departamento', 'Puesto', 'Examen');
            break;
        case 'user1':
            $head = array('# de empleado', 'Nombre', 'Apellido');
            break;
        case 'user2':
            $head = array('Nombre de curso', 'Nombre corto', 'Categoria', 'Examen');
            break;
    }

    $table = new html_table($name);
    $table->attributes = $attrib;
    $table->head = $head;
    $table->data = $data;
    return $table;
}

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