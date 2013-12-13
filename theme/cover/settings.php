<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

// logo image setting
	$name = 'theme_cover/logo';
	$title = get_string('logo','theme_cover');
	$description = get_string('logodesc', 'theme_cover');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
	$settings->add($setting);

	// link color setting
	$name = 'theme_cover/headercolor';
	$title = get_string('headercolor','theme_cover');
	$description = get_string('headercolordesc', 'theme_cover');
	$default = '#E2472F';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);
	
	// link color setting
	$name = 'theme_cover/linkcolor';
	$title = get_string('linkcolor','theme_cover');
	$description = get_string('linkcolordesc', 'theme_cover');
	$default = '#0b4a5b';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// link hover color setting
	$name = 'theme_cover/linkhover';
	$title = get_string('linkhover','theme_cover');
	$description = get_string('linkhoverdesc', 'theme_cover');
	$default = '#666666';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);
}