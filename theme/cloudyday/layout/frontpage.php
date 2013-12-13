<?php$hasheading = ($PAGE->heading);$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());$hasfooter = (empty($PAGE->layout_options['nofooter']));$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);$custommenu = $OUTPUT->custom_menu();$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));$haslogo = (!empty($PAGE->theme->settings->logo));$bodyclasses = array();if ($showsidepre && !$showsidepost) {    $bodyclasses[] = 'side-pre-only';} else if ($showsidepost && !$showsidepre) {    $bodyclasses[] = 'side-post-only';} else if (!$showsidepost && !$showsidepre) {    $bodyclasses[] = 'content-only';}if ($hascustommenu) {    $bodyclasses[] = 'has_custom_menu';}echo $OUTPUT->doctype() ?><html <?php echo $OUTPUT->htmlattributes() ?>><head>    <title><?php echo $PAGE->title ?></title>
    <link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Droid+Serif' rel='stylesheet' type='text/css'>    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />    <?php echo $OUTPUT->standard_head_html() ?></head><body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>"><?php echo $OUTPUT->standard_top_of_body_html() ?><div id="container_wrapper">		<div id="page_header">	<div id="logo_wrapper">			<?php if ($haslogo) {                        echo html_writer::link(new moodle_url('/'), "<img src='".$PAGE->theme->settings->logo."' alt='logo' id='logo' />");                    } else { ?>                    <img src="<?php echo $OUTPUT->pix_url('logo', 'theme')?>" id="logo">                        <?php       } ?>						</div>
	<?php include('profileblock.php')?>
	<div class="clear"></div>
</div>
<div id="page_menu">	<div id="page_left_menu">			<div id="homeicon">			<a href="<?php echo $CFG->wwwroot; ?>"><img src="<?php echo $OUTPUT->pix_url('menu/home_icon', 'theme')?>"></a>		</div>		<div id="menuitemswrap"><div id="custommenu"><?php echo $custommenu; ?></div></div>	</div>
	<div id="page_right_menu">
		<div id="cal_link"><a href="<?php echo $CFG->wwwroot; ?>/calendar/view.php">Calendar</a></div>
	</div>
	<div class="clear"></div>
</div>
<div id="page_outercontent">	<div class="page_heading">	
		<div id="ebutton"><?php if ($hasnavbar) { echo $PAGE->button; } ?></div>			<div class="clear"></div>	</div>								<!-- start OF moodle CONTENT -->				<div id="page-content">        			<div id="region-main-box">            			<div id="region-post-box">                            				<div id="region-main-wrap">                    				<div id="region-main">                        				<div class="region-content">         								<div id="mainpadder">                            			<?php echo core_renderer::MAIN_CONTENT_TOKEN ?>                            			</div>                        				</div>                    				</div>                				</div>                                	<?php if ($hassidepre) { ?>               		<div id="region-pre" class="block-region">                    	<div class="region-content">                                                   	<?php echo $OUTPUT->blocks_for_region('side-pre') ?>                    	</div>                	</div>                	<?php } ?>                                	<?php if ($hassidepost) { ?>                 	<div id="region-post" class="block-region">                    	<div class="region-content">                                           	<?php echo $OUTPUT->blocks_for_region('side-post') ?>                    	</div>                	</div>                	<?php } ?>                            			</div>        			</div>   				 </div>    <!-- END OF CONTENT --> </div><div style="clear: both;"></div> <?php if ($hasfooter) { ?><div id="footerwrapper"><div id="footerinner">            <?php            echo $OUTPUT->login_info();            echo $OUTPUT->standard_footer_html();            ?>                                          <?php echo $PAGE->theme->settings->footnote; ?>       			</div></div>  <?php } ?> </div>   		<?php echo $OUTPUT->standard_end_of_body_html() ?></body></html>