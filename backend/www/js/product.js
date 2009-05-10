dojo.require("modules.AutoSave");
dojo.addOnLoad(function() {
    var node = dojo.byId("add_manifestation");
    dojo.connect(node, 'onclick', dojo.hitch(this, function(evt) {
            dojo.stopEvent(evt);
            var node = evt.target;
            var url = dojo.attr(node, 'href');
            console.log(url);
            dojo.xhrGet({
                handleAs: 'json',
                url: url,
                load: function(r, ioArgs) {
                    var id = r['id'];
                    var title = r['title'];
                    var html = dojo.create(
                        '<label for="mani_'+id+'">[#'+id+'] Name</label>'+
                        '<input type="text" class="string autosave" '+
                        'id="mani_'+id+'" name="mani['+id+']" '+
                        'value="'+title+'" /><br />')
                    dojo.place(html, node, 'before');
                }
            });
    }));
    var auto = new modules.AutoSave;
    auto.findNodes();
    auto.attachEvents();
    // Add manifestation
});
