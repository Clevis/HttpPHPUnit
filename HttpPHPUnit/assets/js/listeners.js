$(document).ready(function () {

	$('#structure .node').dblclick(function (e) {
		location.href = $(this).data('href');
	});

	$('.message-short').click(function (e) {
		$(this).next('.message-link').trigger('click');
	});


// === Prokliknutí shiftem do editoru ==========================================

	$('#structure .node.file').click(function (e) {
		if (e.shiftKey) {
			var editor = $(this).find('.editor a');
			location.href = editor.attr('href');
			e.stopImmediatePropagation(); // zabrání "rozbalení" souboru
		}
	});

	$('.failure, .error').find('h3 > a').click(function (e) {
		if (e.shiftKey) {
			var editor = $(this).closest('h3').find('.editor a');
			location.href = editor.attr('href');
			e.preventDefault();
		}
	});

	$('#summary .details > a').click(function (e) {
		if (e.shiftKey) {
			var editor = $(this).parent().find('.editor a');
			location.href = editor.attr('href');
			e.preventDefault();
		}
	});

});
