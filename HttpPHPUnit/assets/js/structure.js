
var structure = $('#structure');
structure.find('.node.open').parents('ul').show();
structure.find('> ul').show(); // potřeba při pouštění všech testů
structure.find('.node').disableTextSelect(); // zabrání označení položky při dvojkliku
structure.treeview();
