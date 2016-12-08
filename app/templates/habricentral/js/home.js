if (jQuery) {
	jQuery(document).ready(function($){

		if ($('#hero-unit span.hint').length) {
			setTimeout(function(){
				$('#hero-unit span.hint').fadeOut(400);
			}, 3*1000);

			$('#search-block div.col')
				.hover(
					function (e) {
						$(this).find('span.hint').stop(true, true).fadeIn(400);
					},
					function (e) {
						$(this).find('span.hint').stop(true, true).fadeOut(400);
					}
				);
		}

		var section = $('div.highlight-section');
		if (section.length > 0) {
			section.find('li').hide();

			var cols = section.find('div.col'),
				delay = parseInt($('#fade_delay').val()),
				eT = 200;

			delay = (delay) ? delay : 30;

			cols.each(function (i, el) {
				var items_i = 0,
					lis = $(el).find('li');

				setTimeout(function(){
					(function cycle() {
						lis.eq(items_i)
								.fadeIn(400)
								.delay(delay * 1000)
								.fadeOut(400, cycle);

						items_i = ++items_i % lis.length;
					})();
				}, i*eT);
			});
		}
	});
}