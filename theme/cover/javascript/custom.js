$(document).ready(function(){
//onclick show hide
$('#custommenu .yui3-menu-content li').first().addClass('firstmenu');
$("#updown").click(function() {

if ($('#menu-wrap').hasClass('nowfixed')) {
	$("html, body").animate({ scrollTop: 0 }, "fast");
}

	 if ($('#header-wrapper').hasClass('headerclosed')) {
	  $('#header-wrapper, #menu-wrap').removeClass("headerclosed");
	  var slids = "panelopen";
      M.util.set_user_preference('theme_cover_chosen_colpos', slids);
     } else {
       $('#header-wrapper, #menu-wrap').addClass("headerclosed");
       var slids = "headerclosed";
       M.util.set_user_preference('theme_cover_chosen_colpos', slids);
     }

 });

            var stickyPanelOptions = {
                topPadding: 0,
                afterDetachCSSClass: "nowfixed",
                savePanelSpace:false
            };
            $("#menu-wrap").stickyPanel(stickyPanelOptions);
        });