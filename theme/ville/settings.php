<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

// logo image setting
	$name = 'theme_ville/logo';
	$title = get_string('logo','theme_ville');
	$description = get_string('logodesc', 'theme_ville');
	$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
	$settings->add($setting);
	
	// link color setting
	$name = 'theme_ville/linkcolor';
	$title = get_string('linkcolor','theme_ville');
	$description = get_string('linkcolordesc', 'theme_ville');
	$default = '#006363';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);

	// link hover color setting
	$name = 'theme_ville/linkhover';
	$title = get_string('linkhover','theme_ville');
	$description = get_string('linkhoverdesc', 'theme_ville');
	$default = '#009999';
	$previewconfig = NULL;
	$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
	$settings->add($setting);
}