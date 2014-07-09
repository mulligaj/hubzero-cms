jQuery(function($) {
	var reQuote = function(str) {
		return str.replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\/-]', 'g'), '\\$&');
	}
	var xhr, base = '/hubgraph', terms = $('.search .terms'), linkCats = {};
	$('.complete .cat').each(function(_, cat) {
		cat = $(cat);
		linkCats[cat.attr('class').replace(/^cat\s+/, '')] = $(cat.children('ul'));
	});
	$('.bar .clear').click(function(evt) {
		evt.preventDefault();
		terms.val('');
		$('.complete').hide();
		terms.removeClass('with-autocomplete');
		terms.focus();
	});
	var autocompleter = function() {
		if (xhr) {
			xhr.abort();
		}
		if (terms.val().replace(/\s+/g, '') == '') {
			$('.complete').hide();
			$('.autocorrect-notice').show();
			terms.removeClass('with-autocomplete');
			return;
		}
		$('.complete').css('width', parseInt($('.terms').css('width')) + 28 + 'px');
		xhr = $.get(base, {
			'task': 'complete',
			'terms': terms.val()
		}, function(res) {
			$('.complete').hide();
			$('.autocorrect-notice').show();
			terms.removeClass('with-autocomplete');
			var k;
			if (!(k in res.links) && res.completions.length == 0) {
				return;
			}

			var re = new RegExp('(' + reQuote(terms.val()).split(/\s+/).join('|') + ')', 'gi');
			for (k in linkCats) {
				linkCats[k].empty().parent().hide();
			}
			for (k in res.links) {
				if (res.links[k].length) {
					if (!linkCats[k + 's']) {
						if (console.warn) {
							console.warn('no handler defined for ' + k + ' links');
						}
						continue;
					}
					linkCats[k + 's'].parent().show();
					res.links[k].forEach(function(link) {
						linkCats[k + 's'].append($('<li><a>' + link[1].replace(re, '<em>$1</em>') + '</a></li>').data('id', link[0]));
					});
				}
			}
			if (res.completions.length > 0) {
				linkCats.text.parent().show();
				res.completions.forEach(function(text) {
					linkCats.text.append($('<li>').append($('<a>').html(text.replace(re, '<em>$1</em>'))));
				});
			}
			$('.autocorrect-notice').hide();
			$('.complete').show();
			terms.addClass('with-autocomplete');
		});
	};
	terms
		.keyup(autocompleter)
		.focus();
	
	var origSort = function(a, b) {
		return $(a).data('idx') > $(b).data('idx') ? 1 : -1;
	};
	$('.facets tr').each(function(_, tr) {
		var tds = $(tr).children('td'), lis = $(tds[1]).find('li'), strings = {};
		lis.each(function(idx, li) {
			li = $(li);
			li.data('idx', idx);
			if (!strings[li.text()]) {
				strings[li.text()] = li;
			}
		});
		var inlineSearchTimeout;
		$(tds[0]).append($('<span class="inline search"></span>').append(
			$('<input />')
				.keyup(function(evt) {
					if (inlineSearchTimeout) {
						clearInterval(inlineSearchTimeout);
					}
					inlineSearchTimeout = setTimeout(function() {
						var val = $(evt.target).val();
						if (val.replace(/\s+/, '') == '') {
							for (var string in strings) {
								strings[string].children('button').text(string);
							}
							lis.show();
							lis.sort(origSort);
						}
						else {
							var re = new RegExp('(' + reQuote(val) + ')', 'gi'), reFirst = new RegExp('^' + reQuote(val), 'i');
							for (var string in strings) {
								var hlString = string.replace(re, '<span class="highlight">$1</span>');
								strings[string]
									[string == hlString ? 'hide' : 'show']()
									.children('button').html(hlString);
							}
							lis.sort(function(a, b) {
								var am = reFirst.test($(a).text()), bm = reFirst.test($(b).text());
								if (am && !bm) {
									return -1;
								}
								if (bm && !am) {
									return 1;
								}
								return origSort(a, b);
							});
						}
						$(tds[1]).find('ol').append(lis);
					}, 100);
				})
		));
	});
});
