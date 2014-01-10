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

/** Configurable Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */
require_once('../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once $CFG->dirroot . '/grade/lib.php';
require_once($CFG->dirroot . '/report/yearc/yearc_export_form.php');
require_once($CFG->dirroot . '/lib/excellib.class.php');

global $DB, $CFG;

$table = $_SESSION['tablecontents'];
$matrix = array();
$filename = 'report_yearc_' . (time()) . '.xls';

if (!empty($table->head)) {
    $countcols = count($table->head);
    $keys = array_keys($table->head);
    $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
        $matrix[0][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
}

$i = 1;
if (!empty($table->data)) {
    foreach ($table->data as $rkey => $row) {
        $j = 0;
        foreach ($row as $key => $item) {
//            $matrix[$rkey + 1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
            $matrix[$i][$j++] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
        }
        $i++;
    }
}

$downloadfilename = clean_filename($filename);
/// Creating a workbook
$workbook = new MoodleExcelWorkbook("test");
/// Sending HTTP headers
$workbook->send($downloadfilename);
/// Adding the worksheet
$myxls = $workbook->add_worksheet("reporte");

//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//header("Content-Disposition: attachment;filename=$downloadfilename");
//header('Cache-Control: max-age=0');


foreach ($matrix as $ri => $col) {
    foreach ($col as $ci => $cv) {
        $myxls->write_string($ri, $ci, $cv);
    }
}

$workbook->close();
exit;


