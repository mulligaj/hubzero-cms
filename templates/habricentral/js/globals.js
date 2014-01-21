//-----------------------------------------------------------
//  Extend Element to add some of our own functionality
//-----------------------------------------------------------
Element.extend({
	show: function() {
		this.style.display = '';
	},
	hide: function() {
		this.style.display = 'none';
	}
});

//-----------------------------------------------------------
//  Create our namespace
//-----------------------------------------------------------
if (!HUB) {
	var HUB = {};
}

//-----------------------------------------------------------
//  Extend the Position object with a few helpful functions
//-----------------------------------------------------------
HUB.Position = {	
	findPosX: function(obj) {
		var curleft = 0;
		if (obj.offsetParent) {
			while (obj.offsetParent) {
				curleft += obj.offsetLeft;
				obj = obj.offsetParent;
			}
		} else if (obj.x) {
			curleft += obj.x;
		}
		return curleft;
	},
	
	findPosY: function(obj) {
		var curtop = 0;
		if (obj.offsetParent) {
			while (obj.offsetParent) {
				curtop += obj.offsetTop;
				obj = obj.offsetParent;
			}
		} else if (obj.y) {
			curtop += obj.y;
		}
		return curtop;
	}
};

//-----------------------------------------------------------
//  Various functions - encapsulated in HUB namespace
//-----------------------------------------------------------
HUB.Modules = {};
HUB.Components = {};
HUB.Base = {
	templatepath: '',
	
	//  Overlay for "loading", lightbox, etc.
	overlayer: function() {
		// The following code creates and inserts HTML into the document:
		// <div id="initializing" style="display:none;">
		//   <img id="loading" src="templates/zepar/images/circle_animation.gif" alt="" />
		// </div>

		$A(document.getElementsByTagName("script")).each( function(s) {
			if (s.src && s.src.match(/globals\.js(\?.*)?$/)) {
				HUB.Base.templatepath = s.src.replace(/js\/globals\.js(\?.*)?$/,'');
				imgpath = HUB.Base.templatepath + 'images/anim/circling-ball-loading.gif';
			}
	    });

		var panel = new Element('div', {
			id: 'initializing',
			events: {
				'click': function(event) {
					this.setStyles({ display:'none' });
					$('sbox-overlay').setStyles({ display:'none', visibility: 'hidden', opacity: '0' });
				}
			}
		}).injectInside(document.body);
		
		var img = new Element('img', {
			id: 'loading',
			src: imgpath
		}).injectInside(panel);
		
		// Note: the rest of the code is in a separate function because it's needs to be
		// able to be called by itself (usually after loading some HTML via AJAX).
		HUB.Base.launchTool();
	},
	
	launchTool: function() {
		$$('.launchtool').each(function(trigger) {
			trigger.addEvent('click', function(e) {
				$('sbox-overlay').setStyles({
					width: window.getScrollWidth(), 
					height: window.getScrollHeight(), 
					display: 'block',
					visibility: 'visible',
					opacity: '0.7'
				});
				$('initializing').setStyles({
					top: (window.getScrollTop() + (window.getHeight() / 2) - 90), 
					display: 'block',
					zIndex: 65557
				});
			});
		});
	},

	// set focus on username field for login form
	setLoginFocus: function() {
		if ($('username')) {
			$('username').focus();
		}
	},

	// turn links with specific classes into popups
	popups: function() {
		var width = 760;
		var height = 520;
		
		$$('a').each(function(trigger) {
			if (trigger.hasClass('demo') 
			 || trigger.hasClass('popinfo') 
			 || trigger.hasClass('popup') 
			 || trigger.hasClass('breeze')) {
				trigger.addEvent('click', function(e) {
					new Event(e).stop();
					if (this.getProperty('class')) {
						var sizeString = this.getProperty('class').split(' ').pop();
						if (sizeString) {
							var sizeTokens = sizeString.split('x');
							w = parseInt(sizeTokens[0]);
							h = parseInt(sizeTokens[1]);
						}
					}
					
					if(!w) { w = width; }
					if(!h) { h = height; }
					
					window.open(this.getProperty('href'), 'popup', 'resizable=1,scrollbars=1,height='+ h + ',width=' + w); 
				});
			}
			
			if (trigger.getProperty('rel') && trigger.getProperty('rel').indexOf('external') !=- 1) {
				trigger.setProperty('target','_blank');
			}
		});
	},
	
	selectCodeBlock: function() {
		var codeBlock = $$('.code_block');
		if(codeBlock != '') {
			codeBlock.addEvent('click', function() {
				this.select();
			});
		}
	},

	// launch functions
	initialize: function() {
		HUB.Base.setLoginFocus();
		HUB.Base.popups();
		HUB.Base.selectCodeBlock();
		
		// Init SqueezeBox
		if (!$('sbox-window')) {
			SqueezeBoxHub.initialize({ size: {x: 760, y: 520} });
		}
		
		HUB.Base.overlayer();

		// Init Growl
		Growl.Bezel = new Gr0wl.Bezel(HUB.Base.templatepath+'images/bezel.png');
		Growl.Smoke = new Gr0wl.Smoke(HUB.Base.templatepath+'images/smoke.png');
		
		// Init tooltips
		var TTips = new Tips($$('.tooltips'));
		
		// Init fixed position DOM: tooltips
		var fTTips = new MooTips($$('.fixedToolTip'), {
			showDelay: 500,
			maxTitleChars: 100,
			fixed: true,
			offsets: {'x':20,'y':5}
		});
		
		$$(".play").addEvent("click",function(e){
			 var cover = new Element('div', {
				id: 'sbox-window-cover'
				}).injectInside( $("sbox-window") );
		});
	}
};

//----------------------------------------------------------

window.addEvent('domready', HUB.Base.initialize);
