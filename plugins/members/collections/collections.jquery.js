/**
 * @package     hubzero-cms
 * @file        plugins/members/collections/collections.js
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

if (!jq) {
	var jq = $;
}

String.prototype.nohtml = function () {
	if (this.indexOf('?') == -1) {
		return this + '?no_html=1';
	} else {
		return this + '&no_html=1';
	}
};

var scrp = null;

jQuery(document).ready(function(jq){
	var $ = jq,
		container = $('#posts');

		// Are there any posts?
		if (container.length > 0) {
			// Set overlays for lightboxed elements
			$('a.img-link').fancybox();

			// Masonry
			container.masonry({
				itemSelector: '.post'
			});

			// Infinite scroll
			container.infinitescroll({
					navSelector  : '.list-footer',    // selector for the paged navigation
					nextSelector : '.list-footer .next a',  // selector for the NEXT link (to page 2)
					itemSelector : '#posts div.post',     // selector for all items you'll retrieve
					loading: {
						finishedMsg: 'No more pages to load.',
						img: '/6RMhx.gif'
					},
					path: function(index) {
						var path = $('.list-footer .next a').attr('href');
						limit = path.match(/limit[-=]([0-9]*)/).slice(1);
						start = path.match(/start[-=]([0-9]*)/).slice(1);
						//console.log(path.replace(/start[-=]([0-9]*)/, 'no_html=1&start=' + (limit * index - limit)));
						return path.replace(/start[-=]([0-9]*)/, 'no_html=1&start=' + (limit * index - limit));
					},
					debug: false
				},
				// Trigger Masonry as a callback
				function(newElements) {
					// Hide new items while they are loading
					var $newElems = $(newElements).css({ opacity: 0 });

					// Show elems now they're ready
					$newElems.animate({ opacity: 1 });
					container.masonry('appended', $newElems, true);
				}
			);

			// Add voting trigger
			container
				.find('a.vote')
				.on('click', function(e){
					e.preventDefault();

					var el = $(this);

					$.get(el.attr('href').nohtml(), {}, function(data){
						var like = el.attr('data-text-like'),
							unlike = el.attr('data-text-unlike');

						if (el.children('span').text() == like) {
							el.removeClass('like')
								.addClass('unlike')
								.children('span')
								.text(unlike);
						} else {
							el.removeClass('unlike')
								.addClass('like')
								.children('span')
								.text(like);
						}

						$('#b' + el.attr('data-id') + ' .likes').text(data);
					});
				});

			// Add collect trigger
			$('#page_content a.repost').fancybox({
				type: 'ajax',
				width: 500,
				height: 'auto',
				autoSize: false,
				fitToView: false,
				titleShow: false,
				tpl: {
					wrap:'<div class="fancybox-wrap"><div class="fancybox-skin"><div class="fancybox-outer"><div id="sbox-content" class="fancybox-inner"></div></div></div></div>'
				},
				beforeLoad: function() {
					$(this).attr('href', $(this).attr('href').nohtml());
				},
				afterLoad: function(current, previous) {
					scrp = current.content.match(/<script type=\"text\/javascript\">(.*)<\/script>/ig);
					current.content = current.content.replace(/<script(.*)<\/script>/ig, '');
				},
				beforeShow: function() {
					scrp = scrp[0].replace(/<script type=\"text\/javascript\">/ig, '').replace(/<\/script>/ig, '');
					eval(scrp);
				},
				afterShow: function() {
					var el = this.element;
					if ($('#hubForm')) {
						$('#hubForm').on('submit', function(e) {
							e.preventDefault();
							$.post($(this).attr('action'), $(this).serialize(), function(data) {
								$('#b' + $(el).attr('data-id') + ' .reposts').text(data);
								$.fancybox.close();
							});
						});
					}
				}
			});
			
			container.find('a.delete').fancybox({
				type: 'ajax',
				width: 300,
				height: 'auto',
				autoSize: false,
				fitToView: false,
				titleShow: false,
				tpl: {
					wrap:'<div class="fancybox-wrap"><div class="fancybox-skin"><div class="fancybox-outer"><div id="sbox-content" class="fancybox-inner"></div></div></div></div>'
				},
				beforeLoad: function() {
					$(this).attr('href', $(this).attr('href').nohtml());
				},
				afterShow: function() {
					var el = this.element;
					if ($('#hubForm').length) {
						$('#hubForm').on('submit', function(e) {
							e.preventDefault();
							$.post($(this).attr('action'), $(this).serialize(), function(data) {
								$.fancybox.close();
								if (data)
								{
									window.location = data;
								}
							});
						});
					}
				}
			});
			
			// Add collect trigger
			container.find('a.comment').fancybox({
				type: 'ajax',
				width: 500,
				height: 'auto',
				autoSize: false,
				fitToView: false,
				titleShow: false,
				tpl: {
					wrap:'<div class="fancybox-wrap"><div class="fancybox-skin"><div class="fancybox-outer"><div id="sbox-content" class="fancybox-inner"></div></div></div></div>'
				},
				beforeLoad: function() {
					$(this).attr('href', $(this).attr('href').nohtml());
				},
				afterShow: function() {
					var el = this.element;
					if ($('#comment-form').length > 0) {
						//$('#comment-form').on('submit', function(e) {
						$('#sbox-content').on('submit', '#comment-form', function(e) {
							e.preventDefault();
							$.post($(this).attr('action'), $(this).serialize(), function(data) {
								//$('#b' + $(el).attr('data-id') + ' .reposts').text(data);
								//$.fancybox.close();
								$('#sbox-content').html(data);
								$.fancybox.update();
							});
						});
					}
				}
			});
		} // if (container.length > 0)

		// Add follow/unfollow triggers
		$('#page_content a.follow, #page_content a.unfollow').on('click', function(e) {
			e.preventDefault();

			var el = $(this);

			$.getJSON(el.attr('href').nohtml(), {}, function(data) {
				if (data.success) {
					//var unfollow = $(el).attr('data-href-unfollow');
					var follow = el.attr('data-text-follow'),
						unfollow = el.attr('data-text-unfollow');

					if (el.children('span').text() == follow) {
						el.removeClass('follow')
							.addClass('unfollow')
							.attr('href', data.href)
							.children('span')
							.text(unfollow);
					} else {
						el.removeClass('unfollow')
							.addClass('follow')
							.attr('href', data.href)
							.children('span')
							.text(follow);
					}
				}
			});
		});

		$("#ajax-uploader-list").sortable({
			handle: '.asset-handle'
		});
});
