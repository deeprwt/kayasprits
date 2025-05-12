

////////////F12 disable code////////////////////////
    document.onkeypress = function (event) {
        event = (event || window.event);
        if (event.keyCode == 123) {
           //alert('No F-12');
            return false;
        }
    }
    document.onmousedown = function (event) {
        event = (event || window.event);
        if (event.keyCode == 123) {
            //alert('No F-keys');
            return false;
        }
    }
document.onkeydown = function (event) {
        event = (event || window.event);
        if (event.keyCode == 123) {
            //alert('No F-keys');
            return false;
        }
    }
/////////////////////end///////////////////////

var message="Sorry, right-click has been disabled";
///////////////////////////////////
function clickIE() {if (document.all) {(message);return false;}}
function clickNS(e) {if
(document.layers||(document.getElementById&&!document.all)) {
if (e.which==2||e.which==3) {(message);return false;}}}
if (document.layers)
{document.captureEvents(Event.MOUSEDOWN);document.onmousedown=clickNS;}
else{document.onmouseup=clickNS;document.oncontextmenu=clickIE;}
document.oncontextmenu=new Function("return false")
//
function disableCtrlKeyCombination(e)
{
//list all CTRL + key combinations you want to disable
var forbiddenKeys = new Array('a', 'n', 'c', 'x', 'v', 'j' , 'w' , 'u');
var key;
var isCtrl;
if(window.event)
{
key = window.event.keyCode;     //IE
if(window.event.ctrlKey)
isCtrl = true;
else
isCtrl = false;
}
else
{
key = e.which;     //firefox
if(e.ctrlKey)
isCtrl = true;
else
isCtrl = false;
}
//if ctrl is pressed check if other key is in forbidenKeys array
if(isCtrl)
{
for(i=0; i<forbiddenKeys.length; i++)
{
//case-insensitive comparation
if(forbiddenKeys[i].toLowerCase() == String.fromCharCode(key).toLowerCase())
{
alert('Key combination CTRL + '+String.fromCharCode(key) +' has been disabled.');
return false;
}
}
}
return true;
}

//RIGHT CLICK DISABLE CODE

$(document).ready(function(){
	$('.team ul.tabs li').click(function(){
		var tab_id = $(this).attr('rel');
		$('.team ul.tabs li').removeClass('current');
		$('.team .profile').removeClass('current');
		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
	})
})

$(document).ready(function(){
	$('.events ul.tabs li').click(function(){
		var tab_id = $(this).attr('rel');
		$('.events ul.tabs li').removeClass('current');
		$('.events .profile').removeClass('current');
		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
	})
})

<!--TAB CONTENT END-->

jQuery(document).ready(function(){
jQuery('.skillbar').each(function(){
	jQuery(this).find('.skillbar-bar').animate({
		width:jQuery(this).attr('data-percent')
	},6000);
});
});
	
<!--SKILLS BAR END-->

$(document).ready(function() {
  var owl = $(".testimonials #owl-demo");
  owl.owlCarousel({
	  itemsCustom : [
		[0, 1],
		[480, 2],
		[600, 2],
		[767, 2],
		[768, 2],
		[1024, 3],
		[1400, 3],
		[1600, 3]
	  ],
	  navigation : false,
	  autoPlay: 6000,
  });
});

$(document).ready(function() {
  var owl = $(".partner #owl-demo");
  owl.owlCarousel({
	  itemsCustom : [
		[0, 1],
		[480, 2],
		[600, 3],
		[767, 3],
		[768, 4],
		[1024, 6],
		[1400, 6],
		[1600, 6]
	  ],
	  navigation : false,
	  autoPlay: 6000,
  });
});

<!--OWL CAROUSEL END-->

$(function() {

	$('#da-slider').cslider({
		autoplay	: true,
		bgincrement	: 450
	});

});

<!--SLIDER END-->

$(window).scroll(function(){
	if ($(window).scrollTop() >= 1) {
	   $('header').addClass('fixed-header');
	}
	else {
	   $('header').removeClass('fixed-header');
	}
});


<!--STICKY HEADER END-->

$(document).ready(function(){
		$("area[rel^='prettyPhoto']").prettyPhoto();
		
		$(".gallery:first a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'normal',slideshow:3000, autoplay_slideshow: false});
		$(".gallery:gt(0) a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:10000, hideflash: true});

		$("#custom_content a[rel^='prettyPhoto']:first").prettyPhoto({
			custom_markup: '<div id="map_canvas" style="width:260px; height:265px"></div>',
			changepicturecallback: function(){ initialize(); }
		});

		$("#custom_content a[rel^='prettyPhoto']:last").prettyPhoto({
			custom_markup: '<div id="bsap_1259344" class="bsarocks bsap_d49a0984d0f377271ccbf01a33f2b6d6"></div><div id="bsap_1237859" class="bsarocks bsap_d49a0984d0f377271ccbf01a33f2b6d6" style="height:260px"></div><div id="bsap_1251710" class="bsarocks bsap_d49a0984d0f377271ccbf01a33f2b6d6"></div>',
			changepicturecallback: function(){ _bsap.exec(); }
		});
	});

<!--LIGHT BOX END-->

$(".progress-bar").loading();

<!--PROFRESS BAR END-->