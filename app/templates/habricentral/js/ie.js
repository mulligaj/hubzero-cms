//-----------------------------------------------------------
//  Javascript for older IE
//-----------------------------------------------------------
jQuery(document).ready(function(jq){
	var $ = jq;

	$('#nav li').each(function(i, li) {
		$(li)
			.on('mouseover', function(e) {
				$(this).addClass('sfhover');
				var uls = $(this).find('ul');
				for (var i=0; i<uls.length; i++)
				{
					$(uls[i]).css('visibility', 'visible');
				}
			})
			.on('mouseout', function(e) {
				$(this).removeClass('sfhover');
				var uls = $(this).find('ul');
				for (var i=0; i<uls.length; i++)
				{
					$(uls[i]).css('visibility', 'hidden');
				}
			});
	});
});