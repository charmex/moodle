<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'root';
$CFG->dbpass    = '';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => 0,
);

/*
 * Production
 */
//$CFG->wwwroot   = 'http://indwebsb:8080/moodle';
//$CFG->dataroot  = 'E:\\xampp\\moodledata';
//$CFG->admin     = 'admin';

/*
 * Development
 */
$CFG->wwwroot   = 'http://localhost:8080/moodle';
$CFG->dataroot  = 'C:\\xampp\\moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

$CFG->defaultblocks_override = 'completionstatus,navigation,settings:';
$CFG->keeptempdirectoriesonbackup = true;
require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
