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

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
  <title><?php echo $PAGE->title; ?></title>
  <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
  <?php echo $OUTPUT->standard_head_html() ?>
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php if ($hasheading || $hasnavbar) { ?>

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
                <li id="headeractual"><a href="http://localhost:8080/moodnew" title="Moodle">Portal capacitación</a></li>
            </ul>
        </div>
        <div class="headermenu">
          <?php echo $OUTPUT->login_info();
          if (!empty($PAGE->layout_options['langmenu'])) {
            echo $OUTPUT->lang_menu();
          }
          echo $PAGE->headingmenu; ?>
        </div>
        <?php if ($hascustommenu) { ?>
        <div id="custommenu"><?php echo $custommenu; ?></div>
        <?php } ?>
      <?php } ?>
        </div>
    </div>

<?php } ?>
<!-- Custom frontpage div between header and content: contains links to the other sites-->
      <div id="header-blocks" class="clearfix">
          <div style="height:46px; overflow:hidden">
            <a title="Buzón de transparencia" href="http://www.alfa.com.mx/CONT/formas/forma_coment_esp.html" target="_blank"> 
            <img class="mceItem" style="border: 0pt none; float: right;" src="http://intranet/portal/system/files/transparencia2.png" alt="Buzón de transparencia" height="46" width="170"> 
</a><a title="TaO" href="http://140.140.65.21/alfa" target="_blank"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/sites/default/files/btn_logo_tao.gif" alt="TaO" height="25" width="64"> 
</a><a title="Skandia" href="http://www.skandia.com.mx" target="_blank"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/sites/default/files/btn_logo_skandia.gif" alt="Skandia" height="25" width="70"> 
</a><a title="NOVA" href="http://www.nova.com.mx" target="_blank"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/sites/default/files/btn_logo_nova.gif" alt="NOVA" height="25" width="64"> 
</a><a title="Alliax" href="http://alxportal05.alfabw.alface.com.mx/irj/portal" target="_blank"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/sites/default/files/btn_logo_aliax.gif" alt="Alliax" height="25" width="64"> 
</a><a title="Lyondellbasell" href="http://www.lyondellbasell.com/Index.htm"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/system/files/btn_logo_lyondellbasell.gif" alt="Lyondellbasell" height="25" width="64"> 
</a><a title="Alfa" href="http://www.alfa.com.mx" target="_blank"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/sites/default/files/btn_logo_alfa.png" alt="Alfa" height="25" width="64"> 
</a><a title="Sise" href="http://140.140.90.90/sise" target="_blank"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/sites/default/files/btn_logo_sise.jpg" alt="Alfa" height="25" width="64"> 
</a><a title="alfalink" href="http://alfalink.corp.alfa.com.mx" target="_blank"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/sites/default/files/btn_logo_alfa_link.gif" alt="Alfa" height="25" width="108"> 
</a><a title="intranet" href="http://intranet/portal" target="_blank"> 
	<img class="mceItem" style="float: right;" src="http://intranet/portal/sites/default/files/minilogo.png" alt="Alfa" height="25" width="108"> 
</a>
          </div>
      </div>
    <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">

                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">
                            <?php echo $OUTPUT->main_content() ?>
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

    <div class="clearfix"></div>
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

if ($hasheading || $hasnavbar) { ?>

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