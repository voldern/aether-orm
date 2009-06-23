dojo.require("dojo.parser");
dojo.require("modules.AutoSave");
dojo.require("modules.Product");

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
            var node = evt.target,
            url = dojo.attr(node, 'href');

            dojo.xhrGet({
                handleAs: 'json',
                url: url,
                handle: function(r, ioArgs) {
                    // Get by id and delete
                    var id = r['id'];
/*                    var dojo.fx.wipeOut({
                        node: dojo.byId("container_mani_"+id),
                        node
                    });*/
                    var elem = dojo.query("div#container_mani_"+id)
                        .style("overflow", "hidden")
                        .animate({
                                height: 0,
                                backgroundColor: "red"
                                }, 1000, null, function() { elem.destroy(); });
                }
            });
    }));
    
    /* Autosave */
    auto.findNodes();
    auto.attachEvents();

    /* Relase Date */
    dojo.query("#exact_date").connect('onfocus', function(evt) { evt.target.select(); });
    function fadePeriodCb(evt) {
        var node = evt.target;
        var period = node.parentNode.previousSibling.previousSibling;

        if (node.value.length > 0) {
            dojo.query(period) 
                .animate({ opacity: 0.2 }, 1000, null, null);
        }
        else {
            dojo.query(period)
                .animate({ opacity: 1 }, 1000, null, null);
        }
    }
    var exactDate = dojo.query("#exact_date");
    exactDate.connect('onkeyup', fadePeriodCb);
    fadePeriodCb({ target: exactDate[0] });

    /* Image Importer */
    dojo.query('#image_import_submit').connect(
        'onclick', dojo.hitch(this, function(e) {
            dojo.stopEvent(e);
            var articles = dojo.byId('articles_input').value;
            var products = dojo.byId('products_input').value;

            dojo.xhrGet({
                url: '',
                content: {
                    module: 'ImageImport',
                    service: 'lookIn',
                    articles: articles,
                    products: products,
                },
                handleAs: 'json',
                load: dojo.hitch(this, function(resp, ioArgs) {
                    console.log(resp);
                    console.log(ioArgs);
                }),
            });
        })
    );
    
});
