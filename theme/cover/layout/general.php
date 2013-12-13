<?php
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));
$haslogo = (!empty($PAGE->theme->settings->logo));
//get userpref for col open or closed
cover_initialise_colpos($PAGE);
$usercol = cover_get_colpos();
if($usercol == "headerclosed") {
	$thedisplay = "inherit";
	$themenu = "handleclosed";
} else {
	$thedisplay = "block";
	$themenu = "down";
}

//2.4 checks for backwards compat.
$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';
if (empty($PAGE->layout_options['nocourseheaderfooter'])) {  //Check if we're displaying course specific headers and footers
    $courseheader = method_exists($OUTPUT, "course_header") ? $OUTPUT->course_header() : NULL; //Course header - Backward compatible for <2.4
    $coursecontentheader = method_exists($OUTPUT, "course_content_header") ? $OUTPUT->course_content_header() : NULL; //Course content header - Backward compatible for <2.4
    if (empty($PAGE->layout_options['nocoursefooter'])) { //Chekc if we're displaying course footers
        $coursefooter = method_exists($OUTPUT, "course_footer") ? $OUTPUT->course_footer() : NULL; //Course footer - Backward compatible for <2.4
      $coursecontentfooter = method_exists($OUTPUT, "course_content_footer") ? $OUTPUT->course_content_footer() : NULL; //Course Content Footer - Backward compatible for <2.4
    }
}

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

if ($hascustommenu) {
    $bodyclasses[] = 'has_navbar';
}

if ($haslogo) {
	$bodyclasses[] = 'has_logo';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">
	<div id="wrapper">
	
<!-- start OF header -->
<div id="header-wrapper" style="display: <?php echo $thedisplay; ?>" class="<?php echo $usercol; ?>">		
<div id="b-wrapper">
	<div id="b-wrapper-inner">
	<!-- start of navbar -->
	<?php if ($hasnavbar) { ?>
        <div class="navbar clearfix2">
          <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
        </div>
	<?php } ?>
	<!-- end of navbar -->
	
	
	<div class="headermenu">
        		<?php
	        	    echo $OUTPUT->login_info();
    	        	echo $OUTPUT->lang_menu();
	        	    echo $PAGE->headingmenu;
		        ?>	    
	</div>
	</div>
</div>
	<div id="page-header" class="page-header-home">
			<?php if ($haslogo) {
                        echo html_writer::link(new moodle_url('/'), "<img src='".$PAGE->theme->settings->logo."' id='logo' alt='logo' />");
                    } else { ?>
			<img src="<?php echo $OUTPUT->pix_url('logo', 'theme')?>" id="logo">
			<?php } ?>
			
		<?php if ($hasnavbar) { ?>
          <div class="navbutton"> <?php echo $PAGE->button; ?></div>
		<?php } ?>
				
		</div>
</div>		
<!-- end of header -->		

<!-- start of custom menu -->
<div id="menu-wrap" class="<?php echo $usercol; ?>">	
<?php if ($hascustommenu) { ?>
<div id="custommenu">
<div id="menu-shortname"><?php echo'<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $this->page->course->id . '" class="mcourse3">';?><?php echo $this->page->course->shortname ?></a></div>
<a href="#" id="updown" class="<?php echo $themenu; ?>" title="toggle header"></a><?php echo $custommenu; ?></div>
<?php } ?>
<!-- end of menu -->	
</div>

<div id="page-content-wrapper">
<!-- start OF moodle CONTENT -->
 <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">
            
                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">
                            
                             <?php if (!empty($courseheader)) { ?>
                                 <div id="course-header"><?php echo $courseheader; ?></div>
                            <?php } ?>
                            <?php echo $coursecontentheader; ?>
                        
                            <?php echo method_exists($OUTPUT, "main_content")?$OUTPUT->main_content():core_renderer::MAIN_CONTENT_TOKEN ?>
                            
                        </div>
                    </div>
                </div>
                
                <?php if ($hassidepre) { ?>
                <div id="region-pre" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if ($hassidepost) { ?>
                <div id="region-post" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                    </div>
                </div>
                <?php } ?>
                
            </div>
        </div>
    </div>
<!-- end OF moodle CONTENT -->
<div class="clearer"></div>
</div>
<!-- end OF moodle CONTENT wrapper -->


<!-- start of footer -->
<?php if (!empty($coursefooter)) { ?>	
       <div id="course-footer"><?php echo $coursefooter; ?></div>
<?php } ?>
<div id="page-footer">
<?php
echo $OUTPUT->login_info();
echo $OUTPUT->home_link();
echo $OUTPUT->standard_footer_html();
?>
</div>
<!-- end of footer -->	

<div class="clearer"></div>

	</div><!-- end #wrapper -->
</div><!-- end #page -->	

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>