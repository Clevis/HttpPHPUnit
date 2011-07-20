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

structure.find('a.node.open').parents('ul').show();
structure.find('> ul').show(); // potřeba při pouštění všech testů

// Odstraní odkazy
structure.find('a.node').each(function (i, el) {
	el = $(el);
	el.closest('span').data('href', el.attr('href')).addClass('node');
	el.replaceWith(el.text());
});

structure.find('.node').disableTextSelect(); // zabrání označení položky při dvojkliku
structure.find('.node .editor').hide();

structure.treeview();


$(document).ready(function () {

// === Header ==================================================================

	var header = $('header');
	header.addClass($('.failure, .error').length ? 'failure' : 'ok');
	header.find('h2').text($('#sentence').text());

});
