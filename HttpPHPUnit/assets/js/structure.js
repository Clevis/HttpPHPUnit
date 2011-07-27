
var structure = $('#structure');
var open = $('#structure .node.open, #structure > ul');
$('> ul', open.parent()).show();
open.parents('ul').show();
structure.find('.node').disableTextSelect(); // zabrání označení položky při dvojkliku
structure.treeview();
