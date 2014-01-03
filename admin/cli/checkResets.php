<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

//define('CLI_SCRIPT', true);    // To run from the command line. Delete it if you want to run from a browser
require('../../config.php');


/*
 * Load CLI libraries if we are doing a cli script
 */
if (CLI_SCRIPT) {
    require_once($CFG->libdir . '/clilib.php');      // cli only functions
}

global $USER, $DB, $OUTPUT;

/*
 * * We don't want to output header when it's a CLI script since there's no browser
 */
if (!CLI_SCRIPT) {
    echo $OUTPUT->header();
}

/*
 * Define variables used on the script
 */
$exitonerrors;
$log;
$consoleoutput;
$savepath;
if (CLI_SCRIPT) {
    list($options, $unrecognized) = cli_get_params(array(
        'savepath' => $CFG->dirroot . "\coursebackups\\",
        'exitonerrors' => false,
        'log' => true,
        'consoleoutput' => true,
        'help' => false,
            ), array('h' => 'help'));

    if ($unrecognized) {
        $unrecognized = implode("\n  ", $unrecognized);
        cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
    }

    if ($options['help']) {
        $help =
                "Perform a check of whether the course has to be reseted or not. Based on resettime field of a course

Options:
--savepath=STRING       Course ID for backup,
--exitonerrors=boolean  Stop the script if theres a critical error
--log=boolean           Log to a file or not
--consoleoutput=boolean Print to console any message
-h, --help              Print out this help

";

        echo $help;
        die;
    }
} else {
    $exitonerrors = false;
    $log = true;
    $consoleoutput = true;
    $savepath = $CFG->dirroot . "\coursebackups\\";
}

createFolders($savepath, $exitonerrors);

$USER = get_admin();
$currentTime = time();
$d1 = new DateTime("@$currentTime");

$sql = "SELECT 
    c.id, c.fullname, c.shortname, c.startdate, cfo.name , cfo.value
FROM
    moodle.mdl_course c
        LEFT JOIN
    moodle.mdl_course_format_options cfo ON c.id = cfo.courseid AND cfo.name = \"resettime\"
WHERE
    c.id > 1;";
$courses = $DB->get_records_sql($sql);

print_object($courses);

foreach ($courses as $c) {
    $out = "Checking id: " . $c->id . " Name: " . $c->fullname . checkModal();
    consolelog($out, $savepath, $consoleoutput, $log, $d1);
    $courseDate = $c->startdate;
    $resettime;
    if (isset($c->value)) {
        $resettime = $c->value;
    } else {
        if ($exitonerrors) {
            $out = "ERROR : Couldn't find ressettime. Exiting..." . checkModal();
            consolelog($out, $savepath, $consoleoutput, $log, $d1);
            exit(1);
        } else {
            $out = "ERROR : Couldn't find ressettime. Skipping..." . checkModal();
            consolelog($out, $savepath, $consoleoutput, $log, $d1);
        }
    }

    $d2 = new DateTime("@$courseDate");
    $dt = $d2->diff($d1);

    //we double make sure the years are correctly over 0 and the time to reset has passed
    if ($dt->y > 0) {
        if ($dt->y >= $c->resettime) {
            //We call script to reset the course
            $out = "Course with id: " . $c->id . " has completed a cycle with " . $c->value . " years" . checkModal();
            consolelog($out, $savepath, $consoleoutput, $log, $d1);
            $out = "Starting reset procedure on course " . $c->fullname . checkModal();
            consolelog($out, $savepath, $consoleoutput, $log, $d1);

            $status = "";
            $output = "";
            $savename = $c->shortname . "-" . $c->id . "-" . $d2->format('Y-m-d') . "--" . $d1->format('Y-m-d') . "---" . $d1->format('Hi') . "----auto.mbz";
            $exec = "php backup.php --course=" . $c->id . " --destination=" . $savepath . " --customfilename=" . $savename;
            exec($exec, $output, $status);
            if ($status == 1) {
                $out = "status = $status" . checkModal();
                consolelog($out, $savepath, $consoleoutput, $log, $d1);
                var_dump($output);
                $out = checkModal();
                consolelog($out, $savepath, $consoleoutput, $log, $d1);
                $out = "Course back up unsuccesfull with name: " . $savepath . $savename . checkModal();
                consolelog($out, $savepath, $consoleoutput, $log, $d1);
                continue;
            } else {
                $out = "Course backed up succesfully with name: " . $savepath . $savename . checkModal();
                consolelog($out, $savepath, $consoleoutput, $log, $d1);
                $status = "";
                $output = "";
                $exec = "php Delete_user_contents.php --course=" . $c->id;
                exec($exec, $output, $status);
                if ($status == 1) {
                    $out = "status = $status" . checkModal();
                    consolelog($out, $savepath, $consoleoutput, $log, $d1);
                    var_dump($output);
                    $out = checkModal();
                    consolelog($out, $savepath, $consoleoutput, $log, $d1);
                    continue;
                } else {
                    $out = "Course reseted succesfully" . checkModal();
                    consolelog($out, $savepath, $consoleoutput, $log, $d1);
                    $out = "Procedure completed succesfull" . checkModal();
                    consolelog($out, $savepath, $consoleoutput, $log, $d1);
                    continue;
                }
            }
        }
    }
    $out = "Skipped course with id: " . $c->id . ", " . " and name: " . $c->fullname . checkModal();
    consolelog($out, $savepath, $consoleoutput, $log, $d1);
}

fixlog($savepath, $d1);


/*
 * We don't want to output footer when it's a CLI script since there's no browser
 */
if (!CLI_SCRIPT) {
    echo $OUTPUT->footer();
}

/*
 * This method should be appended at the end of each output to make sure we use the correct line break
 * returns \n if the script is run by CLI
 * returns <br> if the script is run by browser
 */

function checkModal() {
    return (CLI_SCRIPT ? "\n" : "<br>");
}

/*
 * @out Message to be outputted
 * @savepath The path where the file will be created
 * @d1 The current date in a DateTime object
 * @log boolean: Whether log to a file or not the output
 * @consoleoutput boolean: Whether we print or not to the console
 */

function consolelog($out, $savepath, $consoleoutput, $log, $d1) {

    if ($consoleoutput) {
        echo $out;
    }
    if ($log) {
        file_put_contents($savepath . "/log/" . $d1->format("Y-m-d-Hi"), $out, FILE_APPEND);
    }
    return 0;
}

/*
 * This function fixes the line breaks when the script is being executed in browser since we are using <br> instead of \n
 * Doesn't execute if the script is being run by CLI
 * returns 0 if everything ran correctly
 */

function fixlog($savepath, $d1) {
    if (CLI_SCRIPT) {
        return 0;
    }
    $filedir = $savepath . "/log/" . $d1->format("Y-m-d-Hi");
    $file = implode("<br>", file($filedir));
    $old = "<br>";
    $new = "\n";
    $fp = fopen($filedir, "w+");
    $str = str_replace($old, $new, $file);
    fwrite($fp, $str);
    fclose($fp);
    return 0;
}

/*
 * Makes sure the folders being used by this script exists or don't do anything at all.
 */
function createFolders($savepath, $exitonerrors = true) {
    if (!file_exists($savepath)) {
        $suc = mkdir($savepath, 0777, true);
        if (!$suc && $exitonerrors) {
            $out = "ERROR : Couldn't make folder. " . $savepath . " are you sure you have permissions?";
            consolelog($out, $savepath, true);
            exit(1);
        }
    }
    if (!file_exists($savepath . "\log\\")) {
        $suc = mkdir($savepath . "\log\\", 0777, true);
        if (!$suc) {
            $out = "ERROR : Couldn't make log folder. " . $savepath . "\log\\" . " are you sure you have permissions?";
            consolelog($out, $savepath, true);
            exit(1);
        }
    }
}

?>
