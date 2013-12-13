<?php
$string['pluginname'] = 'Drupal SSO';
$string['auth_drupaldescription'] = 'This authentication plugin enables Single Sign-on (SSO) with Drupal provided it exists on the same domain as this Moodle installation. This module will look for a Drupal cookie that represents a valid, authenticated session, and will use it to create an authenticated Moodle session for the same user. The Drupal user will be synchronized with the corresponding user in Moodle. If the user does not yet exist in Moodle, it will be created.';

$string['auth_drupallocation_key'] = 'Drupal Location';
$string['auth_drupallocation'] = 'Location of Drupal relative to this domain ('.$_SERVER['HTTP_HOST'].')';
$string['auth_drupalname_key'] = 'Database';
$string['auth_drupalname'] = 'Name of the Drupal MySQL database. The Moodle username/password will need to have SELECT and DELETE (logout requires deleting the associated session) permission on Drupal database.';
$string['auth_drupaltblprefix_key'] = 'Table Prefix';
$string['auth_drupaltblprefix'] = 'Prefix for the Drupal tables';
$string['auth_drupalfidfirst_key'] = 'First Name FID';
$string['auth_drupalfidfirst'] = 'Drupal profile module Field ID for first names';
$string['auth_drupalfidlast_key'] = 'Last Name FID';
$string['auth_drupalfidlast'] = 'Drupal profile module Field ID for last names';
$string['auth_drupalfidwebpage_key'] = 'Web page FID';
$string['auth_drupalfidwebpage'] = 'Drupal profile module Field ID for Web page';
$string['auth_drupalfidinstitution_key'] = 'Institution FID';
$string['auth_drupalfidinstitution'] = 'Drupal profile module Field ID for Institution';
$string['auth_drupalfiddepartment_key'] = 'Department FID';
$string['auth_drupalfiddepartment'] = 'Drupal profile module Field ID for Department';
$string['auth_drupalfididnumber_key'] = 'Id Number FID';
$string['auth_drupalfididnumber'] = 'Drupal profile module Field ID for Id Number';
$string['auth_drupaldebugauthdrupal'] = 'Debug ADOdb';
$string['auth_drupaldebugauthdrupalhelp'] = 'Debug ADOdb connection with Drupal database; use when getting an empty page during login. Not suitable for production.';
$string['auth_drupalremove_user_key'] = 'Removed&nbsp;Drupal&nbsp;User';
$string['auth_drupalremove_user'] = 'Specify what to do with internal user accounts during mass synchronization when users were removed from Drupal. Only suspended users are automatically revived if they reappear in Drupal.';

$string['auth_drupalnorecords'] = 'The Drupal database has no user records!';
$string['auth_drupalcreateaccount'] = 'Unable to create Moodle account for user {$a}';
$string['auth_drupaldeleteuser'] = 'Deleted user {$a->name} id {$a->id}';
$string['auth_drupaldeleteusererror'] = 'Error deleting user {$a}';
$string['auth_drupalsuspenduser'] = 'Suspended user {$a->name} id {$a->id}';
$string['auth_drupalsuspendusererror'] = 'Error suspending user {$a}';
$string['auth_drupaluserstoremove'] = 'User entries to remove: {$a}';
$string['auth_drupalcantinsert'] = 'Moodle DB error. Cannot insert user: {$a}';
$string['auth_drupalcantupdate'] = 'Moodle DB error. Cannot update user: {$a}';
$string['auth_drupaluserstoupdate'] = 'User entries to update: {$a}';
$string['auth_drupalupdateuser'] ='Updated user {$a}';
