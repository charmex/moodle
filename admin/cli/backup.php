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
 * This script allows to do backup.
 *
 * @package    core
 * @subpackage cli
 * @copyright  2013 Lancaster University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('CLI_SCRIPT', 1);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/clilib.php');      // cli only functions
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(array(
    'course' => false,
    'destination' => '',
    'customfilename' => false,
    'help' => false,
        ), array('h' => 'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || !$options['course']) {
    $help =
            "Perform backup of the given course.

Options:
--course=INTEGER      Course ID for backup,
--destination=STRING  Path where to store backup file. If not set, the backup
                      will stored within the same course backup area.
--customfilename=STRING Custom file name for the backup file
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/cli/backup.php --course=2 --destination=/moodle/backup/
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

$admin = get_admin();
if (!$admin) {
    mtrace("Error: No admin account was found");
    die;
}

// Do we need to store backup somewhere else?
$dir = rtrim($options['destination'], '/');
if (!empty($dir)) {
    if (!file_exists($dir) || !is_dir($dir) || !is_writable($dir)) {
        mtrace("Destination directory does not exists or not writable.");
        die;
    }
}

//Is there a custom filename?
$customfilename = rtrim($options['customfilename'], '/');

cli_heading('Performing back-up...'); // TODO: localize
$bc = new backup_controller(backup::TYPE_1COURSE, $options['course'], backup::FORMAT_MOODLE, backup::INTERACTIVE_YES, backup::MODE_GENERAL, $admin->id);
// Set the default filename
$format = $bc->get_format();
$type = $bc->get_type();
$id = $bc->get_id();
$users = $bc->get_plan()->get_setting('users')->get_value();
$anonymised = $bc->get_plan()->get_setting('anonymize')->get_value();
if (!customfilename) {
    $filename = backup_plan_dbops::get_default_backup_filename($format, $type, $id, $users, $anonymised);
} else {
    $filename = $customfilename;
}
$bc->get_plan()->get_setting('filename')->set_value($filename);

// Execution
$bc->finish_ui();
$bc->execute_plan();
$results = $bc->get_results();
$file = $results['backup_destination']; // may be empty if file already moved to target location
// Do we need to store backup somewhere else?
if (!empty($dir)) {
    if ($file) {
        mtrace("Writing " . $dir . '/' . $filename);
        if ($file->copy_content_to($dir . '/' . $filename)) {
            $file->delete();
            mtrace("Backup is completed.");
        } else {
            mtrace("Destination directory does not exists or not writable. Leaving the file in the course backup area.");
        }
    }
} else {
    mtrace("Backup is done, the new file is listed in the backup area of the given course");
}
$bc->destroy();
exit(0); // 0 means success