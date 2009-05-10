dojo.require("modules.AutoSave");
dojo.addOnLoad(function() {
    // Add manifestation
    var node = dojo.byId("add_manifestation");
    dojo.connect(node, 'onclick', dojo.hitch(this, function(evt) {
            dojo.stopEvent(evt);
            var node = evt.target;
            var url = dojo.attr(node, 'href');
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
    // Delete manifestation
    dojo.query(".delete_manifestation").connect(
        'onclick', dojo.hitch(this, function(evt) {
            dojo.stopEvent(evt);
            var node = evt.target;
            var url = dojo.attr(node, 'href');
            dojo.xhrGet({
                handleAs: 'json',
                url: url,
                handle: function(r, ioArgs) {
                    // Get by id and delete
                    var id = r['id'];
                    dojo.query("div#container_mani_"+id).destroy();
                }
            });
    }));
    var auto = new modules.AutoSave;
    auto.findNodes();
    auto.attachEvents();
    // Add manifestation
});
