<?php

/**
 * @author Arsham Skrenes (based on work by Federico Heinz)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle multiauth
 *
 * Authentication Plugin: Drupal Single Sign-on
 *
 * This module will look for a Drupal cookie that represents a valid,
 * authenticated session, and will use it to create an authenticated Moodle
 * session for the same user. The Drupal user will be synchronized with the
 * corresponding user in Moodle. If the user does not yet exist in Moodle, it
 * will be created.
 */
// This must be accessed from a Moodle page only!
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir . '/authlib.php');
require_once($CFG->libdir . '/adodb/adodb.inc.php');

// Drupal SSO authentication plugin.
class auth_plugin_drupal extends auth_plugin_base {

    public $DEBUG = false;

    // Constructor
    function auth_plugin_drupal() {
        $this->authtype = 'drupal';
        $this->config = get_config('auth/drupal');
    }

    // Function is called to validate data before it's inserted in config_plugin
    function validate_form(&$form, &$err) {
        if (empty($form->location) || (substr($form->location, 0, 1) != '/')) {
            $form->location = '/';
        }
        if (!empty($form->fidfirst) && !ctype_digit($form->fidfirst)) {
            $form->fidfirst = '';
        }
        if (!empty($form->fidlast) && !ctype_digit($form->fidlast)) {
            $form->fidlast = '';
        }
    }

    // Function to generate custom query for Drupal that will return:
    // uid, username, firstname, lastname, email
    // if $sid is provided, it will create a query to return the associated user
    function sql_drupal_users($sid = '') {
        $sql = "SELECT " .
                "t1.uid AS 'uid', " .
                "t1.name AS 'username', " .
                "'es-mx' AS 'language', " .
                "'Altamira' AS city," .
                "'Mx' AS country,";

        if (empty($this->config->fidfirst)) {
            $sql .= "'SSO' AS 'firstname', ";
        } else {
            $sql .= "COALESCE(t2.value, t1.name) AS 'firstname', ";
        }

        if (empty($this->config->fidlast)) {
            $sql .= "t1.name AS 'lastname', ";
        } else {
            $sql .= "COALESCE(t3.value, t1.name) AS 'lastname', ";
        }

        if (empty($this->config->fidwebpage)) {
            $sql .= "t1.name AS 'webpage', ";
        } else {
            $sql .= "COALESCE(t4.value, t1.name) AS 'webpage', ";
        }

        if (empty($this->config->fidinstitution)) {
            $sql .= "t1.name AS 'institution', ";
        } else {
            $sql .= "COALESCE(t5.value, t1.name) AS 'institution', ";
        }

        if (empty($this->config->fiddepartment)) {
            $sql .= "t1.name AS 'department', ";
        } else {
            $sql .= "COALESCE(t6.value, t1.name) AS 'department', ";
        }

        if (empty($this->config->fidlast)) {
            $sql .= "t1.name AS 'idnumber', ";
        } else {
            $sql .= "COALESCE(t7.value, t1.name) AS 'idnumber', ";
        }

        $sql .= "t1.mail AS 'email' FROM " .
                "{$this->config->name}.{$this->config->tblprefix}users t1 ";

        if (!empty($this->config->fidfirst)) {
            $sql .= "LEFT JOIN {$this->config->name}.{$this->config->tblprefix}" .
                    "profile_values t2 ON t1.uid = t2.uid " .
                    "AND t2.fid = {$this->config->fidfirst} ";
        }

        if (!empty($this->config->fidlast)) {
            $sql .= "LEFT JOIN {$this->config->name}.{$this->config->tblprefix}" .
                    "profile_values t3 ON t1.uid = t3.uid " .
                    "AND t3.fid = {$this->config->fidlast} ";
        }

        if (!empty($this->config->fidwebpage)) {
            $sql .= "LEFT JOIN {$this->config->name}.{$this->config->tblprefix}" .
                    "profile_values t4 ON t1.uid = t4.uid " .
                    "AND t4.fid = {$this->config->fidwebpage} ";
        }

        if (!empty($this->config->fidinstitution)) {
            $sql .= "LEFT JOIN {$this->config->name}.{$this->config->tblprefix}" .
                    "profile_values t5 ON t1.uid = t5.uid " .
                    "AND t5.fid = {$this->config->fidinstitution} ";
        }

        if (!empty($this->config->fiddepartment)) {
            $sql .= "LEFT JOIN {$this->config->name}.{$this->config->tblprefix}" .
                    "profile_values t6 ON t1.uid = t6.uid " .
                    "AND t6.fid = {$this->config->fiddepartment} ";
        }

        if (!empty($this->config->fididnumber)) {
            $sql .= "LEFT JOIN {$this->config->name}.{$this->config->tblprefix}" .
                    "profile_values t7 ON t1.uid = t7.uid " .
                    "AND t7.fid = {$this->config->fididnumber} ";
        }

        $sql .= "WHERE t1.uid > 0";

        if (!empty($sid)) {
            $sql .= " AND t1.uid IN " .
                    "(SELECT uid FROM {$this->config->name}.{$this->config->tblprefix}" .
                    "sessions WHERE sid = '$sid')";
        }
        if ($this->DEBUG) {
            print_object($sql);
            print_object($this->config);
        }
        return $sql . ';';
    }

    // This plugin is for SSO only; Drupal handles the login
    function user_login($username, $password) {
        return false;
    }

    // Function to enable SSO (it runs before user_login() is called)
    // If a valid Drupal session is not found, the user will be forced to the
    // login page where some other plugin will have to authenticate the user
    function loginpage_hook() {
        global $CFG, $USER, $SESSION, $DB;

        // Check if we have a Drupal session.
        $cookie = 'SESS' . md5($_SERVER['HTTP_HOST'] . rtrim($this->config->location, '/'));
        $drupal_sid = $_COOKIE[$cookie];
        if (empty($drupal_sid)) {
            return; // Drupal session does not exist; send user to login page
        }

        // Verify the authenticity of the Drupal session ID
        $drupal_user = $DB->get_record_sql($this->sql_drupal_users($drupal_sid));

        if ($drupal_user === false) {
            // the session ID is not valid
            if (isloggedin() && !isguestuser()) {
                // the user is logged-off of Drupal but still logged-in on Moodle
                // so we must now log-off the user from Moodle...
                require_logout();
            }
            return;
        }

        // The Drupal session is valid; now check if Moodle is logged in...
        if (isloggedin() && !isguestuser()) {
            return;
        }

        // Moodle is not logged in so fetch or create the corresponding user
        $user = $this->create_update_user($drupal_user);
        if (empty($user)) {
            // Something went wrong while creating the user
            print_error('auth_drupalcreateaccount', 'auth_drupal', $drupal_user->username);
            unset($drupal_user);
            return;
        }
        unset($drupal_user);

        // complete login
        $USER = get_complete_user_data('id', $user->id);
        complete_user_login($USER);
        if ($USER->id != 0) { // Only for autenticated users
            $mcae = get_auth_plugin('mcae'); //Get mcae plugin

            if (isset($SESSION->mcautoenrolled)) {
                if (!$SESSION->mcautoenrolled) {
                    $mcae->user_authenticated_hook($USER, $USER->username, ""); //Autoenrol if mcautoenrolled FALSE
                }
            } else {
                $mcae->user_authenticated_hook($USER, $USER->username, ""); //Autoenrol if mcautoenrolled NOT SET
            }
        }

        // redirect
        if (isset($SESSION->wantsurl) and
                (strpos($SESSION->wantsurl, $CFG->wwwroot) == 0)) {
            // the URL is set and within Moodle's environment
            $urltogo = $SESSION->wantsurl;
            unset($SESSION->wantsurl);
        } else {
            // no wantsurl stored or external link. Go to homepage.
            $urltogo = $CFG->wwwroot . '/';
            unset($SESSION->wantsurl);
        }
        redirect($urltogo);
    }

    // function to grab Moodle user and update their fields then return the
    // account. If the account does not exist, create it.
    // Returns: the Moodle user (array) associated with drupal user argument
    function create_update_user($drupal_user) {
        global $CFG, $DB;

        $username = $drupal_user->username;
        $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
        if (empty($user)) {
            // build the new user object to be put into the Moodle database
            $user = new object();
            $user->username = $username;
            $user->firstname = $drupal_user->firstname;
            $user->lastname = $drupal_user->lastname;
            $user->institution = $drupal_user->institution;
            $user->department = $drupal_user->department;
            $user->webpage = $drupal_user->webpage;
            $user->auth = $this->authtype;
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->lang = str_replace('-', '_', $drupal_user->language);
            $user->confirmed = 1;
            $user->email = $drupal_user->email;
            $user->idnumber = $drupal_user->idnumber;
            $user->icq = $drupal_user->uid;
            $user->city = $drupal_user->city;
            $user->country = $drupal_user->country;
            $user->modified = time();

            // add the new Drupal user to Moodle
            $uid = $DB->insert_record('user', $user);
            $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
            if (!$user) {
                print_error('auth_drupalcantinsert', 'auth_db', $username);
            }
        } else {

            // Update user information
            if (strcmp($user->email . $user->firstname . $user->lastname . $user->lang . $user->institution . $user->webpage . $user->department . $user->idnumber
                            , $drupal_user->email . $drupal_user->firstname . $drupal_user->lastname . $drupal_user->institution . $drupal_user->webpage . $drupal_user->department . $drupal_user->idnumber . str_replace('-', '_', $drupal_user->language)) != 0) {
                $user->email = $drupal_user->email;
                $user->firstname = $drupal_user->firstname;
                $user->lastname = $drupal_user->lastname;
                $user->webpage = $drupal_user->webpage;
                $user->institution = $drupal_user->institution;
                $user->department = $drupal_user->department;
                $user->idnumber = $drupal_user->idnumber;
                $user->city = $drupal_user->city;
                $user->country = $drupal_user->country;
                $user->icq = $drupal_user->uid;
                $user->lang = str_replace('-', '_', $drupal_user->language);
                if (!$DB->update_record('user', $user)) {
                    print_error('auth_drupalcantupdate', 'auth_db', $username);
                }
            }
        }
        if ($this->DEBUG) {
            print_object($user);
            print_object($drupal_user);
            die();
        }

        return $user;
    }

    // function that is called upon the user logging out
    function logoutpage_hook() {
        global $CFG, $DB;

        // Check whether we still have a Drupal session.
        $cookie = 'SESS' . md5($_SERVER['HTTP_HOST'] . rtrim($this->config->location, '/'));
        $drupal_sid = $_COOKIE[$cookie];
        if (empty($drupal_sid)) {
            return; // the Drupal session has already been terminated
        }

        // remove the session ID from Drupal Database
        $result = $DB->execute(
                "DELETE FROM {$this->config->name}.{$this->config->tblprefix}sessions " .
                "WHERE sid = '$drupal_sid'");
        return $result;
    }

    // cron synchronization script
    // $do_updates: true to update existing accounts (and add new Drupal accounts)
    function sync_users($do_updates = false) {
        global $CFG, $DB;

        // process users in Moodle that no longer exist in Drupal
        if (!empty($this->config->removeuser)) {
            // find obsolete users
            $remove_users = $DB->get_records_sql(
                    "SELECT id, username, email, auth " .
                    "FROM {$CFG->prefix}user " .
                    "WHERE auth='{$this->authtype}' " .
                    "AND deleted=0 " .
                    "AND username NOT IN " .
                    "(SELECT name FROM " .
                    "{$this->config->name}.{$this->config->tblprefix}users)");
            if (!empty($remove_users)) {
                print_string('auth_drupaluserstoremove', 'auth_drupal', count($remove_users));
                echo "\n";

                foreach ($remove_users as $user) {
                    if ($this->config->removeuser == AUTH_REMOVEUSER_FULLDELETE) {
                        if (delete_user($user)) {
                            echo "\t";
                            print_string('auth_drupaldeleteuser', 'auth_db', array('name' => $user->username, 'id' => $user->id));
                            echo "\n";
                        } else {
                            echo "\t";
                            print_string('auth_drupaldeleteusererror', 'auth_db', $user->username);
                            echo "\n";
                        }
                    } else if ($this->config->removeuser == AUTH_REMOVEUSER_SUSPEND) {
                        $updateuser = new object();
                        $updateuser->id = $user->id;
                        $updateuser->auth = 'nologin';
                        if ($DB->update_record('user', $updateuser)) {
                            echo "\t";
                            print_string('auth_drupalsuspenduser', 'auth_db', array('name' => $user->username, 'id' => $user->id));
                            echo "\n";
                        } else {
                            echo "\t";
                            print_string('auth_drupalsuspendusererror', 'auth_db', $user->username);
                            echo "\n";
                        }
                    }
                }
            }
            unset($remove_users); // free mem!
        }

        // sync users in Drupal with users in Moodle (adding users if needed)
        if ($do_updates) {
            // Pull all the Drupal users with their information
            $users_to_sync = $DB->get_records_sql($this->sql_drupal_users());
            if (empty($users_to_sync)) {
                print_string('auth_drupalnorecords', 'auth_drupal');
                return false;
            } else {
                // sync users in Drupal with users in Moodle (adding users if needed)
                print_string('auth_drupaluserstoupdate', 'auth_drupal', count($users_to_sync));
                echo "\n";
                foreach ($users_to_sync as $user) {
                    print_string('auth_drupalupdateuser', 'auth_drupal', $user->username);
                    echo "\n";
                    $this->create_update_user($user);
                }
            }
            unset($users_to_sync); // free mem!
        }
    }

    // Function called by admin/auth.php to print a form for configuring plugin
    // @param array $page An object containing all the data for this page.
    function config_form($config, $err, $user_fields) {
        include 'config.html';
    }

    // Processes and stores configuration data for this authentication plugin.
    function process_config($config) {

// set to defaults if undefined
        if (!isset($config->location)) {
            $config->location = '/';
        }
        if (!isset($config->name)) {
            $config->name = '';
        }
        if (!isset($config->tblprefix)) {
            $config->tblprefix = 'drp_';
        }
        if (!isset($config->fidfirst)) {
            $config->fidfirst = '1';
        }
        if (!isset($config->fidlast)) {
            $config->fidlast = '2';
        }
        if (empty($config->debugauthdrupal)) {
            $config->debugauthdrupal = 0;
        }
        if (!isset($config->removeuser)) {
            $config->removeuser = AUTH_REMOVEUSER_KEEP;
        }

        // save settings
        set_config('location', $config->location, 'auth/drupal');
        set_config('name', $config->name, 'auth/drupal');
        set_config('tblprefix', $config->tblprefix, 'auth/drupal');
        set_config('fidfirst', $config->fidfirst, 'auth/drupal');
        set_config('fidlast', $config->fidlast, 'auth/drupal');
        set_config('fidwebpage', $config->fidwebpage, 'auth/drupal');
        set_config('fidinstitution', $config->fidinstitution, 'auth/drupal');
        set_config('fiddepartment', $config->fiddepartment, 'auth/drupal');
        set_config('fididnumber', $config->fididnumber, 'auth/drupal');
        set_config('debugauthdrupal', $config->debugauthdrupal, 'auth/drupal');
        set_config('removeuser', $config->removeuser, 'auth/drupal');
        return true;
    }

}
