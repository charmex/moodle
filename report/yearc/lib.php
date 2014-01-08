<?php

defined('MOODLE_INTERNAL') || die;

function report_yearc_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/yearc:view', $context)) {
        $url = new moodle_url('/report/yearc/index.php');
        $navigation->add(get_string('pluginname', 'report_yearc'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

function report_yearc_extend_navigation_module($navigation, $cm) {
    if (has_capability('report/yearc:view', context_course::instance($cm->course))) {
        $url = new moodle_url('/report/yearc/index.php');
        $navigation->add(get_string('yearc'), $url, navigation_node::TYPE_SETTING, null, 'yearcreport');
    }
}

function report_yearc_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                => get_string('page-x', 'pagetype'),
        'report-*'         => get_string('page-report-x', 'pagetype'),
        'report-yearc-*'     => get_string('page-report-yearc-x',  'report_yearc'),
        'report-yearc-index' => get_string('page-report-yearc-index',  'report_yearc')
    );
    return $array;
}

function report_yearc_can_access_user_report($user, $course) {
    global $USER;

    $coursecontext = context_course::instance($course->id);
    $personalcontext = context_user::instance($user->id);

    $today = false;
    $all = false;

    if (has_capability('report/yearc:view', $coursecontext)) {
        $today = true;
    }
    if (has_capability('report/yearc:view', $coursecontext)) {
        $all = true;
    }

    if ($today and $all) {
        return array(true, true);
    }

    if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        if ($course->showreports and (is_viewing($coursecontext, $user) or is_enrolled($coursecontext, $user))) {
            return array(true, true);
        }

    } else if ($user->id == $USER->id) {
        if ($course->showreports and (is_viewing($coursecontext, $USER) or is_enrolled($coursecontext, $USER))) {
            return array(true, true);
        }
    }

    return array($all, $today);
}

function report_yearc_extend_navigation_user($navigation, $user, $course) {
    list($all, $today) = report_yearc_can_access_user_report($user, $course);

    if ($today) {
        $url = new moodle_url('/report/yearc/index.php');
        $navigation->add(get_string('pluginname','report_yearc'), $url);
    }
}