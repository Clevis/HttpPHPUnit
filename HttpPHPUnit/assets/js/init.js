$.extend($.fn.disableTextSelect = function () {
	return this.each(function () {
		if ($.browser.mozilla) {
			$(this).css('MozUserSelect', 'none');
		} else if($.browser.msie) {
			$(this).bind('selectstart', function () {
				return false;
			});
		} else{
			$(this).mousedown(function () {
				return false;
			});
		}
	});
});

$(function () {

	var header = $('header');
	var sentence = $('#sentence');
	header.addClass(sentence.data('state'));
	header.find('h2').text(sentence.text());

});
