dojo.require("modules.AutoSave");
dojo.require("modules.Product");
dojo.require("modules.Upload");

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

    function publishAndRemoveImageDOM(ev) {
        dojo.xhrGet({
            url: '',
            content: {
                module: 'ImageImport',
                service: 'publish',
                imageId: this.imageId
            },
            handleAs: 'json',
            load: dojo.hitch(this, function(resp, ioArgs) {
                this.parentNode.removeChild(this);
                dojo.publish("imageList.updated");
            }),
        });
    }
    function updateImageList(file) {
        var eid = dojo.byId("eid").value;
        dojo.xhrGet({
            url: '',
            content: {
                module: 'ImageImport',
                service: 'lookIn',
                products: eid,
                width: 150
            },
            handleAs: 'json',
            load: function(resp, ioArgs) {
                var unpubImages = dojo.byId("unpublishedImages");
                var pubImages = dojo.byId("publishedImages");
                var eid = dojo.byId("eid").value;

                unpubImages.innerHTML = '';
                pubImages.innerHTML = '';
                var unpubOl = dojo.create("ol", {className:"imageList clearfix"});
                var pubOl = dojo.create("ol", {className:"imageList clearfix"});
                for (var i in resp.products[eid]) {
                    var li = dojo.create('li', { className: 'fLeft' });
                    li.appendChild(dojo.create("img", {src: resp.products[eid][i].url}));
                    li.appendChild(dojo.create("p", {innerHTML: resp.products[eid][i].name, className: "alignCenter"}));
                    li.selectionId = resp.products[eid][i].id;
                    li.entityId = eid;
                    if (resp.products[eid][i].published == false) {
                        unpubOl.appendChild(li);
                        dojo.connect(li, "onclick", function(evt) {
                            toggleDOM(this, [dojo.byId("image_publish"), dojo.byId("image_delete"), dojo.byId("image_edit")]);
                        });
                    }
                    else {
                        pubOl.appendChild(li);
                        dojo.connect(li, "onclick", function(evt) {
                            toggleDOM(this, [dojo.byId("image_depublish")]);
                        });
                    }
                }
                unpubImages.appendChild(unpubOl);
                pubImages.appendChild(pubOl);
            },
        });
    }
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
    dojo.query("#image_depublish").connect('onsubmit', 
        dojo.hitch(this, function(e) {
            dojo.stopEvent(e);

            dojo.xhrGet({
                url: '',
                form: dojo.byId("image_depublish"),
                content: {
                    module: 'ImageImport',
                    service: 'depublish'
                },
                handleAs: 'json',
                load: dojo.hitch(this, function(resp, ioArgs) {
                    dojo.publish("imageList.changed", []);
                }),
            });
        }
    ));
    dojo.query("#image_publish").connect('onsubmit', 
        dojo.hitch(this, function(e) {
            dojo.stopEvent(e);

            dojo.xhrGet({
                url: '',
                form: dojo.byId("image_publish"),
                content: {
                    module: 'ImageImport',
                    service: 'publish'
                },
                handleAs: 'json',
                load: dojo.hitch(this, function(resp, ioArgs) {
                    dojo.publish("imageList.changed", []);
                }),
            });
        }
    ));
    dojo.query("#image_delete").connect('onsubmit', 
        dojo.hitch(this, function(e) {
            dojo.stopEvent(e);

            dojo.xhrGet({
                url: '',
                form: dojo.byId("image_delete"),
                content: {
                    module: 'ImageImport',
                    service: 'unlink'
                },
                handleAs: 'json',
                load: dojo.hitch(this, function(resp, ioArgs) {
                    dojo.publish("imageList.changed", []);
                }),
            });
        }
    ));
    updateImageList();
    dojo.subscribe("imageList.changed", updateImageList);
    dojo.subscribe("upload.finished", updateImageList);

    /* Image editor */
    dojo.query("#image_edit").connect('onsubmit', 
        dojo.hitch(this, function(e) {
            dojo.stopEvent(e);

            dojo.xhrGet({
                url: '',
                form: dojo.byId("image_edit"),
                content: {
                    module: 'ImageEditor',
                    service: 'showEditor'
                },
                handleAs: 'text',
                load: dojo.hitch(this, function(resp, ioArgs) {
                    var editImages = dojo.byId("editImages");
                    editImages.innerHTML = resp;
                    dojo.query("#closeEdit").connect("onclick", 
                        function(e) { 
                            dojo.byId("editImages").innerHTML = '';
                        }
                    );
                    auto.findNodes(editImages);
                    auto.attachEvents();
                }),
            });
        }
    ));
});
