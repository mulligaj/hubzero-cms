/**
 * Display transform for pf
**/
String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};

function pf_make_url_hashed(hashed){
	//via http://stackoverflow.com/questions/1844491/intercepting-call-to-the-back-button-in-my-ajax-application-i-dont-want-it-to
	window.location.hash = '#'+hashed;
}

function assure_closed_menus(){
	jQuery('.dropdown li > *').on('click', function(){
		jQuery('.dropdown.open').removeClass('open');
	});
}

//via http://stackoverflow.com/questions/1662308/javascript-substr-limit-by-word-not-char
function trim_words(theString, numWords) {
	expString = theString.split(/\s+/,numWords);
	theNewString=expString.join(" ");
	return theNewString;
}

function assure_next_obj(tabindex, obj, advance){
	var lastobj = jQuery('article:last-child');
	var lastindex = lastobj.attr('tabindex');
	// If we've hidden a next object, the tabs won't adjust, so check and fix.
	if ((0 == obj.length) || obj.is(':hidden')){
		if (1 == advance){
			tabindex = tabindex+1;
		} else {
			tabindex = tabindex-1;
		}
		obj = jQuery('article[tabindex="'+tabindex+'"]');
	}
	if ((0 == obj.length  || obj.is(':hidden')) && (0 <= tabindex) && (lastindex > tabindex)){
			obj = assure_next_obj(tabindex, obj, advance);
	}
	if (obj.is(':hidden')) {
		return false;
	}
	return obj;
}

function PFBootstrapInits() {

	jQuery('.nom-to-archive').tooltip({
		placement : 'top',
		trigger: 'hover',
		title: 'Item'

	});
	jQuery('.nom-to-draft').tooltip({
		placement : 'top',
		trigger: 'hover',
		title: 'Item'

	});
	jQuery('.nominate-now').tooltip({
		placement : 'top',
		trigger: 'hover',
		title: 'Nominate'

	});
	jQuery('.star-item').tooltip({
		placement : 'top',
		trigger: 'hover',
		title: 'Star'

	});
	jQuery('.itemInfobutton').tooltip({
		placement : 'top',
		trigger: 'hover',
		title: 'Star'

	});
	jQuery('.itemCommentModal').tooltip({
		placement : 'top',
		trigger: 'hover',
		title: 'Comment'
	});
	jQuery('.nom-count').tooltip({
		placement : 'top',
		trigger: 'hover',
		title: 'Nomination Count'
	});
	jQuery('.pf-amplify').tooltip({
		placement : 'top',
		trigger: 'hover',
		title: 'Amplify'
	});

	jQuery('.itemInfobutton').popover({
		html : true,
		title : '',
		container : '.icon-info-sign',
		content : function(){
			var idCode = jQuery(this).attr('data-target');
			var contentOutput = '<div class="feed-item-info-box">';
			contentOutput += jQuery('#info-box-'+idCode).html();
			contentOutput += '</div>';
			console.log('Popover assembled');
			return contentOutput;
		}
	})
	.on("click", function(){
		jQuery('.popover').addClass(jQuery(this).data("class")); //Add class .dynamic-class to < div>
	});

	jQuery(".modal.pfmodal").on('hide', function(evt){
		jQuery(".itemInfobutton").popover('hide');
	});
	jQuery(".modal.pfmodal").on('show', function(evt){
		jQuery(".itemInfobutton").popover('hide');
	});

	jQuery('.info-box-popover').on('click', function(e) {
		e.stopPropagation();
	});

	jQuery('.itemInfobutton').on('click', function(e) {
		e.stopPropagation();
	});

	jQuery(document).on('click', function (e) {
		// Do whatever you want; the event that'd fire if the "special" element has been clicked on has been cancelled.
		jQuery(".itemInfobutton").popover('hide');
	});

	//attach_menu_on_scroll_past();
	//assure_closed_menus();
}

function detect_view_change(){

	jQuery('.pf_container').on('click', '.pf-top-menu-selection.display-state', function(evt){
		var element = jQuery(this);
		var go_layout = element.attr('id');
		console.log(go_layout);
		//alert(modalIDString);
		jQuery.post(ajaxurl, {
				action: 'pf_ajax_retain_display_setting',
				pf_read_state: go_layout

			},
			function(response) {

			});
	});

	var is_pf_open = false;

	jQuery('.pressforward #wpbody').on('click', '.list .amplify-group .pf-amplify', function(evt){
		var element = jQuery(this);
		//console.log(element);
		var parent_e = element.parents('article');
		var parent_h = parent_e.height();
		//console.log(parent_h);
		if (element.hasClass('amplify-down')){
			element.removeClass('amplify-down');
			jQuery(parent_e).removeClass('show-overflow');
			jQuery(parent_e).css('height','');
		} else {
			element.addClass('amplify-down');
			jQuery(parent_e).addClass('show-overflow');
			jQuery(parent_e).height(parent_h);
			is_pf_open = true;
		}
	});

	jQuery('.pressforward #wpbody').on('click', '.list div:not(.amplify-group.open)', function(evt){
		var element_p = jQuery('.amplify-group.open');
		//console.log(element_p);
		if (is_pf_open){
			//console.log(element_p.length);
			var element = element_p.find('.pf-amplify');
			var parent_e = element.parents('article');
			var parent_h = parent_e.height();
			//console.log(parent_h);
			element.removeClass('amplify-down');
			jQuery(parent_e).removeClass('show-overflow');
			jQuery(parent_e).css('height', '');
		}
	});

	jQuery('.pressforward #wpbody').on('click', '.grid .amplify-group .pf-amplify', function(evt){
		var element = jQuery(this);
		//console.log(element);
		var parent_e = element.parents('article');
		var parent_h = parent_e.height();
		var parent_head = parent_e.find('header');
		//console.log(parent_h);
		if (element.hasClass('amplify-down')){
			parent_e.removeClass('amplify-down');
		} else {
			parent_e.addClass('amplify-down');
			is_pf_open = true;
		}
	});

	jQuery('.pressforward #wpbody').on('click', '.grid div:not(.amplify-group.open)', function(evt){
		var element_p = jQuery('.amplify-group.open');
		//console.log(element_p);
		if (is_pf_open){
			//console.log(element_p.length);
			var element = element_p.find('.pf-amplify');
			var parent_e = element.parents('article');
			var parent_h = parent_e.height();
			//console.log(parent_h);
			parent_e.removeClass('amplify-down');
		}
	});
}

console.log('Waiting for load.');
jQuery(window).load(function() {
	// executes when complete page is fully loaded, including all frames, objects and images

	jQuery('.pf-loader').delay(300).fadeOut( "slow", function() {
		console.log('Load complete.');
		jQuery('.pf_container').fadeIn("slow");
		if (window.location.hash.indexOf("#") < 0){
			window.location.hash = '#ready';
		} else if ((window.location.hash.toLowerCase().indexOf("modal") >= 0)) {
			var hash = window.location.hash;
			if (!jQuery(hash).hasClass('in')){
				jQuery(hash).modal('show');
			}
		}

		jQuery(window).on('hashchange', function() {
			if (window.location.hash == '#ready') {
				jQuery('.modal').modal('hide');
			}
			if ((window.location.hash.toLowerCase().indexOf("modal") >= 0)) {
				var hash = window.location.hash;
				if (!jQuery(hash).hasClass('in')){
					jQuery(hash).modal('show');
				}
			}
		});
	});
});

// Via http://stackoverflow.com/a/1634841/2522464
function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');
    if (urlparts.length>=2) {

        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }

        url= urlparts[0]+'?'+pars.join('&');
        return url;
    } else {
        return url;
    }
}

jQuery(window).on('load', function() {

	/*jQuery('#gogrid').on('click', function (evt){
		evt.preventDefault();
		jQuery("div.pf_container").removeClass('list').addClass('grid');
		jQuery('#gogrid').addClass('unset');
		jQuery('#golist').removeClass('unset');
		jQuery('.feed-item').each(function (index){
			var element = jQuery(this);
			var itemID  = element.attr('id');
			jQuery('#'+itemID+' footer .actions').appendTo('#'+itemID+' header');
		});
	});

	jQuery('#golist').on('click', function (evt){
		evt.preventDefault();
		jQuery("div.pf_container").removeClass('grid').addClass('list');
		jQuery('#golist').addClass('unset');
		jQuery('#gogrid').removeClass('unset');
		jQuery('.feed-item').each(function (index){
			var element = jQuery(this);
			var itemID  = element.attr('id');
			jQuery('#'+itemID+' header .actions').appendTo('#'+itemID+' footer');
		});
	});

	jQuery('#gomenu').click(function (evt){
		evt.preventDefault();
		jQuery('#feed-folders').hide('slide',{direction:'right', easing:'linear'},150);
	});

	jQuery('#gomenu').toggle(function (evt){
			evt.preventDefault();
			var toolswin = jQuery('#tools');
			jQuery("div.pf_container").removeClass('full');
			jQuery('#feed-folders').hide('slide',{direction:'right', easing:'linear'},150);
			jQuery(toolswin).show('slide',{direction:'right', easing:'linear'},150);
	}, function() {
			var toolswin = jQuery('#tools');
			//jQuery('#feed-folders').hide('slide',{direction:'right', easing:'linear'},150);
			jQuery(toolswin).hide('slide',{direction:'right', easing:'linear'},150);
			jQuery("div.pf_container").addClass('full');
	});*/
	jQuery('#gofolders').click(function (evt){
		evt.preventDefault();
		jQuery('#tools').hide('slide',{direction:'right', easing:'linear'},150);
	});
	jQuery('#gofolders').toggle(function (evt){
		evt.preventDefault();
		var folderswin = jQuery('#feed-folders');
		jQuery("div.pf_container").removeClass('full');

		jQuery(folderswin).show('slide',{direction:'right', easing:'linear'},150);
	}, function() {
		var folderswin = jQuery('#feed-folders');
		//jQuery('#tools').hide('slide',{direction:'right', easing:'linear'},150);
		jQuery(folderswin).hide('slide',{direction:'right', easing:'linear'},150);
		jQuery("div.pf_container").addClass('full');
	});

	jQuery('#feed-folders .folder').on('click', function (evt){
		evt.preventDefault();
		var obj = jQuery(this);
		var id = obj.attr('href');
		var url = window.location.href;//window.location.origin+window.location.pathname+'?page=pf-menu';
		url = url.replace('#','&');
		url = removeURLParameter(url, 'folder');
		url = removeURLParameter(url, 'feed');
		url = removeURLParameter(url, 'ready');
		if (url.indexOf('?') > -1){
			url += '&folder='+id;
		}else{
			url += '?folder='+id;
		}
		window.location.href = url;
	});

	jQuery('#feed-folders .feed').on('click', function (evt){
		evt.preventDefault();
		var obj = jQuery(this);
		var id = obj.children('a').attr('href');
		var url = window.location.href;//window.location.origin+window.location.pathname+'?page=pf-menu';
		url = url.replace('#','&');
		url = removeURLParameter(url, 'folder');
		url = removeURLParameter(url, 'feed');
		url = removeURLParameter(url, 'ready');
		if (url.indexOf('?') > -1){
			url += '&feed='+id;
		}else{
			url += '?feed='+id;
		}
		window.location.href = url;
	});

	if (jQuery('.list').length != 0) {
		var actionButtons = jQuery('.list article');
		jQuery.each(actionButtons, function(index, value) {
			var tID = jQuery(this).attr('id');
			jQuery('#'+tID+' header .actions').appendTo('#'+tID+' footer');
		});
		//console.log('Item Actions in foot.');
	}

	jQuery('.pf_container').on('click', '#showMyNominations', function(evt){
		evt.preventDefault();
		window.open("?page=pf-menu&by=nominated", "_self")
	});
	jQuery('.pf_container').on('click', '#showMyHidden', function(evt){
		evt.preventDefault();
		window.open("?page=pf-menu&reveal=no_hidden", "_self")
	});
	jQuery('.pf_container').on('click', '#showUnread', function(evt){
		evt.preventDefault();
		window.open("?page=pf-menu&reveal=unread", "_self")
	});
	jQuery('.pf_container.pf-all-content').on('click', '#showDrafted', function(evt){
		evt.preventDefault();
		window.open("?page=pf-menu&reveal=drafted", "_self")
	});
	jQuery('.pf_container').on('click', '#showMyStarred', function(evt){
		evt.preventDefault();
		window.open("?page=pf-menu&by=starred", "_self")
	});
	jQuery('.pf_container').on('click', '#showNormal', function(evt){
		evt.preventDefault();
		window.open("?page=pf-menu", "_self")
	});
	jQuery('.pf_container').on('click', '#showNormalNominations', function(evt){
		evt.preventDefault();
		window.open("?page=pf-review", "_self")
	});

	//update_user_option(pressforward()->form_of->user_id(), 'have_you_seen_nominate_this', false);
	jQuery('.pf_container').on('click', '.remove-nom-this-prompt', function(evt){
		evt.preventDefault();
		jQuery('article.nominate-this-preview').remove();
		jQuery.post(ajaxurl, {
				action: 'pf_ajax_user_setting',
				pf_user_setting: 'have_you_seen_nominate_this',
				setting: 'yes'

		},
		function(response) {
				var check_set = html_entity_decode(jQuery(response).find("response_data").text());
		});
		if (jQuery(this).is('[href]')){
			window.open("?page=pf-tools", "_self");
		}
	});

	//PFBootstrapInits();
	//detect_view_change();
//	commentModal();

	jQuery('.pf_container').on('click', '.itemCommentModal', function(evt){
		evt.preventDefault();

		$.fancybox(this, {
			href: $(this).attr('href').nohtml(),
			type: 'ajax',
			width: 600,
			height: 'auto',
			autoSize: false,
			fitToView: true,
			//wrapCSS: 'sbp-window',
			afterShow: function() {
				if ($('#cancel-action').length) {
					$('#cancel-action').on('click', function(e) {
						$.fancybox.close();
					});
				}
			}
		});
	});

	$('.actions .btn').tooltip({
		position: {
			my: 'center bottom',
			at: 'center top'
		},
		track: false,
		//show: false,
		hide: false,
		content: function() {
			return $(this).attr('title');
		},
		create: function(event, ui) {
			var tip = $(this),
				tipText = tip.attr('data-original-title');

			if (tipText.indexOf('::') != -1) {
				var parts = tipText.split('::');
				tip.attr('title', '<div class="tip-title">' + parts[0] + '</div><div class="tip-text">' + parts[1] + '</div>');
			} else {
				tip.attr('title', '<div class="tip-text">' + tipText + '</div>');
			}
		},
		tooltipClass: 'tooltip'
	});

	/*jQuery('.pf_container').on('click', '.itemInfobutton', function(evt){
		evt.preventDefault();

		var popup = $('#' + $(this).attr('data-target'));
		popup.show();
	});*/
});
