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


// === Structure ===============================================================

var structure = $('#structure');
structure.find('.node.open').parents('ul').show();
structure.find('> ul').show(); // potřeba při pouštění všech testů
structure.find('.node').disableTextSelect(); // zabrání označení položky při dvojkliku
structure.treeview();


$(document).ready(function () {

// === Header ==================================================================

	var header = $('header');
	header.addClass($('.failure, .error').length ? 'failure' : 'ok');
	header.find('h2').text($('#sentence').text());

});
