$(document).ready(function () {

	$('#structure .node a.name').click(function (e) {
		if (e.button == 0 && !e.shiftKey) {
			$(this).closest('li').find('> .hitarea').trigger('click');
			e.preventDefault();
		}
	});

	$('#structure .node a.name').dblclick(function (e) {
		location.href = this.href;
	});

	$('.message-short').click(function (e) {
		$(this).next('.message-link').trigger('click');
	});


// === Prokliknutí shiftem do editoru ==========================================

	$('#structure .node.file').click(function (e) {
		if (e.button == 0 && e.shiftKey) {
			var editor = $(this).find('.actions .editor');
			location.href = editor.attr('href');
			e.preventDefault();
		}
	});

	$('.failure, .error').find('h3 > a').click(function (e) {
		if (e.button == 0 && e.shiftKey) {
			var editor = $(this).closest('h3').find('.editor a');
			location.href = editor.attr('href');
			e.preventDefault();
		}
	});

	$('#summary .details > a').click(function (e) {
		if (e.button == 0 && e.shiftKey) {
			var editor = $(this).parent().find('.editor a');
			location.href = editor.attr('href');
			e.preventDefault();
		}
	});

});
