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

var alertFallback = true;
if (typeof console === "undefined" || typeof console.log === "undefined") {
	console = {};
	console.log = function() {};
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
	// Container for jquery. 
	// Needed for noconflict mode compatibility
	jQuery: jq,

	// Set the base path to this template
	templatepath: '/templates/hubbasic2012/',

	// launch functions
	initialize: function() {
		var $ = this.jQuery, w = 760, h = 520;

		// Set focus on username field for login form
		if ($('#username').length > 0) {
			$('#username').focus();
		}

		// Set the search box's placeholder text color
		if ($('#searchword').length > 0) {
			$('#searchword')
				.css('color', '#777')
				.on('focus', function(){
					if ($(this).val() == 'Search') {
						$(this).val('').css('color', '#ddd');
					}
				})
				.on('blur', function(){
					if ($(this).val() == '' || $(this).val() == 'Search') {
						$(this).val('Search').css('color', '#777');
					}
				});
		}

		// Turn links with specific classes into popups
		$('a').each(function(i, trigger) {
			if ($(trigger).is('.demo, .popinfo, .popup, .breeze')) {
				$(trigger).on('click', function (e) {
					e.preventDefault();

					if ($(this).attr('class')) {
						var sizeString = $(this).attr('class').split(' ').pop();
						if (sizeString && sizeString.match(/\d+x\d+/gi)) {
							var sizeTokens = sizeString.split('x');
							w = parseInt(sizeTokens[0]);
							h = parseInt(sizeTokens[1]);
						}
						else if(sizeString && sizeString == 'fullxfull')
						{
							w = screen.width;
							h = screen.height;
						}
					}

					window.open($(this).attr('href'), 'popup', 'resizable=1,scrollbars=1,height='+ h + ',width=' + w);
				});
			}
			if ($(trigger).attr('rel') && $(trigger).attr('rel').indexOf('external') !=- 1) {
				$(trigger).attr('target', '_blank');
			}
		});

		// Set the overlay trigger for launch tool links
		$('.launchtool').on('click', function(e) {
			$.fancybox({
				closeBtn: false, 
				href: HUB.Base.templatepath + 'images/anim/circling-ball-loading.gif'
			});
		});

		// Set overlays for lightboxed elements
		$('a[rel=lightbox]').fancybox();

		// Init tooltips
		$('.hasTip, .tooltips').tooltip({
			position: {
				my: 'center bottom',
				at: 'center top'
			},
			// When moving between hovering over many elements quickly, the tooltip will jump around
			// because it can't start animating the fade in of the new tip until the old tip is
			// done. Solution is to disable one of the animations.
			hide: false,
			create: function(event, ui) {
				var tip = $(this),
					tipText = tip.attr('title');

				if (tipText.indexOf('::') != -1) {
					var parts = tipText.split('::');
					tip.attr('title', parts[1]);
				}
			},
			tooltipClass: 'tooltip'
		});

		// Init fixed position DOM: tooltips
		$('.fixedToolTip').tooltip({
			relative: true
		});
		
		HUB.Base.placeholderSupport();
	},

	placeholderSupport: function() {
		var $ = this.jQuery;
		
		//test for placeholder support
		var test = document.createElement('input');
		var placeholder_supported  = ('placeholder' in test);
		
		//if we dont have placeholder support mimic it with focus and blur events
		if (!placeholder_supported) {
			$('input[type=text]:not(.no-legacy-placeholder-support)').each(function(i, el) {
				var placeholderText = $(this).attr('placeholder');

				//make sure we have placeholder text
				if (placeholderText != '' && placeholderText != null) {
					//add plceholder text and class
					if ($(this).val() == '') {
						$(this).addClass('placeholder-support').val( placeholderText );
					}

					//attach event listeners to input
					$(this)
						.on('focus', function() {
							if($(this).val() == placeholderText)
							{
								$(this).removeClass('placeholder-support').val('');
							}
						})
						.on('blur', function(){
							if($(this).val() == '')
							{
								$(this).addClass('placeholder-support').val( placeholderText );
							}
						});
				}
			});

			$('form').on('submit', function(event){
				$('.placeholder-support').each(function(i, el){
					$(this).val('');
				});
			});
		}
	}
};

jQuery(document).ready(function($){
	HUB.Base.initialize();
});

