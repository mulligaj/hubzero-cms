/**
 * @package     hubzero-cms
 * @file        components/com_answers/assets/js/vote.jquery.js
 * @copyright   Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license     http://opensource.org/licenses/MIT MIT
 */

//----------------------------------------------------------
// Thumbs voting
//----------------------------------------------------------
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

jQuery(document).ready(function(jq){
	var $ = jq;

	$('.comments').on('click', '.vote-button', function (e) {
		if ($(this).attr('href')) {
			e.preventDefault();

			var item = $(this);

			$.get(item.attr('href').nohtml(), {}, function(data) {
				if (data) {
					item.closest('.voting').html(data);
					$('.tooltip').hide();
				}
			});
		}
	});
});

