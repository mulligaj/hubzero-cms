/**
 * @package     hubzero-cms
 * @file        templates/hubbasic/js/globals.js
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */


//-----------------------------------------------------------
//  Create our namespace
//-----------------------------------------------------------
if (!HUB) {
	var HUB = {};
}

//-----------------------------------------------------------
//  Various functions - encapsulated in HUB namespace
//-----------------------------------------------------------
if (!jq) {
	var jq = $;
	
	$.getDocHeight = function(){
	     var D = document;
	     return Math.max(Math.max(D.body.scrollHeight, D.documentElement.scrollHeight), Math.max(D.body.offsetHeight, D.documentElement.offsetHeight), Math.max(D.body.clientHeight, D.documentElement.clientHeight));
	};
} else {
	jq.getDocHeight = function(){
	     var D = document;
	     return Math.max(Math.max(D.body.scrollHeight, D.documentElement.scrollHeight), Math.max(D.body.offsetHeight, D.documentElement.offsetHeight), Math.max(D.body.clientHeight, D.documentElement.clientHeight));
	};
}

HUB.Base = {
	
	jQuery: jq,
	
	templatepath: '',

	//  Overlay for "loading", lightbox, etc.
	overlayer: function() {
		var $ = this.jQuery;
		// The following code creates and inserts HTML into the document:
		// <div id="initializing" style="display:none;">
		//   <img id="loading" src="templates/zepar/images/circle_animation.gif" alt="" />
		// </div>
		var imgpath = '';
		$('script').each(function(i, s) {
			if (s.src && s.src.match(/hub\.jquery\.js(\?.*)?$/)) {
				HUB.Base.templatepath = s.src.replace(/js\/hub\.jquery\.js(\?.*)?$/,'');
				imgpath = HUB.Base.templatepath + 'images/anim/circling-ball-loading.gif';
			}
		});
		
		$('<div id="sbox-overlay" style="position:absolute;top:0;left:0;z-index:997;display:none;"><div id="initializing" style="position:absolute;top:0;left:50%;z-index:998;display:none;"><img id="loading" src="'+imgpath+'" alt="" /></div></div>')
			.on('click', function(){
				$(this).css({ display:'none', visibility: 'hidden', opacity: '0' })
			})
			.appendTo(document.body);
		
		// Note: the rest of the code is in a separate function because it's needs to be
		// able to be called by itself (usually after loading some HTML via AJAX).
		HUB.Base.launchTool();
	},

	launchTool: function() {
		var $ = this.jQuery;
		
		$('.launchtool').each(function(i, trigger) {
			$(trigger).on('click', function(e) {
				$('#sbox-overlay').css({
					width: $(window).width(), 
					height: $.getDocHeight(), 
					display: 'block',
					visibility: 'visible',
					opacity: '0.7'
				});
				$('#initializing').css({
					top: ($(window).scrollTop() + ($(window).height() / 2) - 90), 
					display: 'block',
					zIndex: 65557
				});
			});
		});
	},

	// set focus on username field for login form
	setLoginFocus: function() {
		var $ = this.jQuery;
		
		if ($('#username')) {
			$('#username').focus();
		}
	},

	// turn links with specific classes into popups
	popups: function() {
		var w = 760, h = 520, $ = this.jQuery;
		
		$('a').each(function(i, trigger) {
			if ($(trigger).is('.demo, .popinfo, .popup, .breeze')) {
				$(trigger).click(function (e) {
					e.preventDefault();
					
					if ($(this).attr('class')) {
						var sizeString = $(this).attr('class').split(' ').pop();
						if (sizeString && sizeString.match('/\d+x\d+/')) {
							var sizeTokens = sizeString.split('x');
							w = parseInt(sizeTokens[0]);
							h = parseInt(sizeTokens[1]);
						}
					}
					
					window.open($(this).attr('href'), 'popup', 'resizable=1,scrollbars=1,height='+ h + ',width=' + w);
				});
			}
			
			if ($(trigger).attr('rel') && $(trigger).attr('rel').indexOf('external') !=- 1) {
				$(trigger).attr('target','_blank');
			}
		});
	},

	searchbox: function() {
		var $ = this.jQuery;
		
		if ($('#searchword')) {
			$('#searchword').css('color', '#999');
			$('#searchword').focus(function(){
				if ($(this).val() == 'Search') {
					$(this).val('');
					$(this).css('color', '#333');
				}
			});
			$('#searchword').blur(function(){
				if ($(this).val() == '' || $(this).val() == 'Search') {
					$(this).val('Search');
					$(this).css('color', '#999');
				}
			});
		}
	},

	// launch functions
	initialize: function() {
		var $ = this.jQuery;
		
		HUB.Base.setLoginFocus();
		HUB.Base.searchbox();
		HUB.Base.popups();
		HUB.Base.overlayer();
		
		$('a[rel=lightbox]').fancybox({
		});
		
		// Init tooltips
		$('.hasTip').tooltip({
			position:'TOP RIGHT',
			//offset: [10,-20],
			onBeforeShow: function(event, position) {
				var tip = this.getTip(),
					tipText = tip[0].innerHTML;
					
				if (tipText.indexOf(" :: ") != -1) {
					var parts = tipText.split(" :: ");
					tip[0].innerHTML = "<span class=\"tooltip-title\">"+parts[0]+"</span><span>"+parts[1]+"</span>";
				}
			}
		}).dynamic({ bottom: { direction: 'down' }, right: { direction: 'left' } });
		$('.tooltips').tooltip({
			position:'top center',
			offset: [-5,0],
			onBeforeShow: function(event, position) {
				var tip = this.getTip(),
					tipText = tip[0].innerHTML;
					
				if (tipText.indexOf(" :: ") != -1) {
					var parts = tipText.split(" :: ");
					tip[0].innerHTML = "<span class=\"tooltip-title\">"+parts[0]+"</span><span>"+parts[1]+"</span>";
				}
			}
		}).dynamic({ bottom: { direction: 'down' }, right: { direction: 'left' } });
		
		// Init fixed position DOM: tooltips
		$('.fixedToolTip').tooltip({
			relative: true
		});
		
		//
		$('a.play').fancybox({
			type: 'ajax',
			width: '100%',
			height: '100%',
			autoSize: false,
			fitToView: false,
			beforeLoad: function() {
				href = $(this).attr('href');
				if (href.indexOf('?') == -1) {
					href += '?no_html=1';
				} else {
					href += '&no_html=1';
				}
				$(this).attr('href', href);

				if (this.element.attr('class')) {
					var sizeString = this.element.attr('class').split(' ').pop();
					if (sizeString && sizeString.match(/\d+x\d+/i)) {
						var sizeTokens = sizeString.split('x');
						if (parseInt(sizeTokens[0])) {
							this.width  = parseInt(sizeTokens[0]);// - 20;
						}
						if (parseInt(sizeTokens[1])) {
							this.height = parseInt(sizeTokens[1]);// - 60;
						}
					}
				}
			},
			afterShow: function() {
				var iframe = $(".fancybox-inner").find("iframe");
				if (iframe.attr('src').indexOf('docs.google.com') != -1)
				{
					$(".fancybox-inner").prepend("<div id=\"sbox-window-cover\"></div>");
					$(".fancybox-inner").find("iframe").css("height", "99%"); //iframe being same height of lightbox causes scrollbar to appear
				}
				else if (iframe.attr('src').indexOf('/templates/habricentral/viewer/') != -1)
				{
					this.width = '100%';
					this.height = '100%';
					$.fancybox.update();
				}
			}
		});

		// change BBB text to "find this Text"
		var bbb = $('#primary-document');
		if (bbb.length)
		{
			var url = bbb.find('a').attr('href');
			if (url.match(/\/findthistext/i))
			{
				bbb.find('a').html('Find this Text');
			}
		}
	}
	
};

jQuery(document).ready(function($){
	HUB.Base.initialize();
});

