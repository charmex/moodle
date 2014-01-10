<?php
require_once('/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once $CFG->dirroot . '/grade/lib.php';
require_once('yearc_export_form.php');

//require_once('/export/xls/export.php');

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
$script = "<script type=\"text/javascript\">
var tableToExcel = (function() {
  var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p];
}) }
return function(table, name) {
if (!table.nodeType) table = document.getElementById(table)
var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
window.location.href = uri + base64(format(template, ctx))
}
})()
</script>";
echo $script;

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
$filename = outputCSV($table, $table2);
echo html_writer::link($filename, get_string("download_c", "report_yearc"));
//        $_SESSION['tablecontents'] = $table;
//        print_object($_SESSION['tablecontents']);
//        $url = new moodle_url("/report/yearc/export/xls/export.php");
//        echo html_writer::link($url, get_string("download_c", "report_yearc"));
//        export_reportt($table);
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
} else if ($mform->filter == 0) {
$mform->display();
$table = buildTable('yearc1', "tableData", $mform->ycr);
$table2 = buildTable('yearc2', "tableData", $mform->u);
drawTable("Cursos:", $table, "yearc_table");
drawTable("Usuarios:", $table2, "yearc_table");
$filename = outputCSV($table, $table2);
echo html_writer::link($filename, get_string("download_c", "report_yearc"));
}
} else {
// this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
// or on the first display of the form.
//Set default data (if any)
//displays the form
$mform->display();
}
echo $OUTPUT->footer();

function courseHeader($cid) {
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
case 'yearc1':
$head = array('# consecutivo de curso', 'Curso', 'Nombre de curso', 'Año', 'Mes', 'Fecha de termino', 'Objetivo de curso', 'Duracion de curso');
break;
case 'yearc2':
$head = array('# consecutivo de curso', '# de empleado', 'CURP');
break;
}

$table = new html_table($name);
$table->attributes = $attrib;
$table->head = $head;
$table->data = $data;
return $table;
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