<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
if ($hassidepre && !$hassidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($hassidepost && !$hassidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$hassidepost && !$hassidepre) {
    $bodyclasses[] = 'content-only';
}

if ($hascustommenu) {
    $bodyclasses[] = 'has-custom-menu';
}

$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';
if (empty($PAGE->layout_options['nocourseheaderfooter'])) {
    $courseheader = $OUTPUT->course_header();
    $coursecontentheader = $OUTPUT->course_content_header();
    if (empty($PAGE->layout_options['nocoursefooter'])) {
        $coursecontentfooter = $OUTPUT->course_content_footer();
        $coursefooter = $OUTPUT->course_footer();
    }
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
  <title><?php echo $PAGE->title; ?></title>
  <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
  <?php echo $OUTPUT->standard_head_html() ?>
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php if ($hasheading || $hasnavbar || !empty($courseheader) || !empty($coursefooter)) { ?>

<div id="page-wrapper">
  <div id="page" class="clearfix">

    <div id="page-header" class="clearfix">
      <?php if ($PAGE->heading) { ?>
        <div id="headertop" class="clearfix">
            <div id="headermain">
                <a href="http://intranet/portal/" title="Inicio"><img src="http://intranet/portal/sites/default/files/theme_images/logo-intranet.png" alt="Inicio" id="logo-image"></a>
            </div>
            <div id="headerposts">
                <a href="http://intranet/portal/node/add/proy-ti" title="Proyecto TI"><img src="http://indwebsb/portal/sites/default/files/theme_images/notaproyti1.png" alt="Proyectos TI" id="pti_image"></a>
                <a href="http://intranet/portal/node/add/computo-distribuido"><img src="http://indwebsb/portal/sites/default/files/theme_images/notacdist5.png" alt="Computo Distribuido" title="Computo Distribuido"></a>
                <a href="http://intranet/portal/#innova-form"><img src="http://indwebsb/portal/sites/default/files/theme_images/post-it-mini-c4.png" alt="Cuentanos tu idea" title="Cuentanos tu idea"></a>
            </div>
        </div>
        
        <div id="headerbot" class="clearfix">
        <div id="links">
            <ul class="headerlinks">
                <li class="headerout"><a href="http://intranet/portal/" title="Página principal" class="active">Inicio</a></li>
                <li class="headerout"><a href="http://intranet/portal/aviso_privacidad" title="Aviso de privacidad">Aviso de privacidad</a></li>
                <li id="headeractual"><a href="http://intranet:8080/moodle" title="Moodle">Portal capacitación</a></li>
            </ul>
        </div>
        <div class="headermenu">
          <?php echo $OUTPUT->login_info();
          if (!empty($PAGE->layout_options['langmenu'])) {
            echo $OUTPUT->lang_menu();
          }
          echo $PAGE->headingmenu; ?>
        </div>
      <?php } ?>
      <?php if ($hascustommenu) { ?>
      <div id="custommenu"><?php echo $custommenu; ?></div>
      <?php } ?>
    </div>
    </div>
 <div class="myclear"></div>

      <?php if (!empty($courseheader)) { ?>
        <div id="course-header"><?php echo $courseheader; ?></div>
      <?php } ?>

      <?php if ($hasnavbar) { ?>
        <div class="navbar clearfix">
          <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
          <div class="navbutton"> <?php echo $PAGE->button; ?></div>
        </div>
      <?php } ?>

<?php } ?>

    <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">

                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">
                            <?php echo $coursecontentheader; ?>
                            <?php echo $OUTPUT->main_content() ?>
                            <?php echo $coursecontentfooter; ?>
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

    <div class="myclear"></div>
    <?php if (!empty($coursefooter)) { ?>
        <div id="course-footer"><?php echo $coursefooter; ?></div>
    <?php } ?>
<?php if ($hasfooter) { ?>

    <div id="page-footer" class="clearfix">
        <!--
        Custom footer should delete this if page broken
        -->
        <br/>Copyright © 2009 Portal web de la intranet de Industrias Indelpro S.A. de C.V.<br/>
        <span><a href="/portal/aviso_privacidad">Políticas de privacidad</a></span>
        <br/>
        <span>Soporte Portal Intranet: <a href="/portal/?q=contact">formulario de contácto</a> ó ext. <strong>1679</strong></span>
        </br>
      <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?></p>
      <?php echo $OUTPUT->login_info(); ?>
    </div>

<?php }

if ($hasheading || $hasnavbar || !empty($courseheader) || !empty($coursefooter)) { ?>
   <div class="myclear"></div>
  </div> <!-- END #page -->

</div> <!-- END #page-wrapper -->

<?php } ?>

<div id="page-footer-bottom">

<?php if ($hasfooter) {

  echo $OUTPUT->home_link();
  echo $OUTPUT->standard_footer_html();

} ?>

</div>


<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>