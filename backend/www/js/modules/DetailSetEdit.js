/**
 * List all detail sets with underlying details
 */

dojo.provide("modules.DetailSetEdit");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");
dojo.require("modules.DetailSetDetails");

dojo.declare("modules.DetailSetEdit", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    /**
     * Use a file based html template
     */
    templatePath: dojo.moduleUrl("modules.templates", "detail_set_edit.html"),
    /**
     * This gets called once the widget is loaded with its configuration
     * but before rendering so the data set in here will be available
     */
    postMixInProperties: function() {
        if (this.set_id < 0) {
            this.data = {
                id: this.set_id,
                title: false,
                titleI18N: false,
                details: {
                    records: []
                },
            };
        }
        else {
            dojo.xhrGet({
                url: '/details/',
                content: {
                    module: 'Details',
                    service: 'GetSet',
                    id: this.set_id,
                },
                handleAs: 'json',
                sync: true,
                load: dojo.hitch(this, function(resp,ioArgs) {
                    this.data = resp;
                }),
            });
        }
        this.data.types = ['text','bool','date','numeric'];
    },
    postCreate: function() {
        this.update();
        auto.findNodes(dojo.byId('edit_detail_set').parentNode);
        auto.attachEvents();

        // Make closable
        dojo.query("button.close", this.domNode)
            .connect('onclick', dojo.hitch(this, function(e) {
                this.destroy();
            })
        );
        // Add details
        dojo.query('#add_detail').connect('onclick', dojo.hitch(this, function(e) {
            // add detail
            dojo.stopEvent(e);
            dojo.xhrGet({
                url: e.currentTarget.getAttribute('href'),
                handleAs: 'json',
                load: dojo.hitch(this, function(response, ioArgs) {
                    this.postMixInProperties();
                    this.update();
                })
            });
        }));
    },
    /*
     * Update sub widget with details
     */
    update: function() {
        widget = dijit.byId('set_details');
        if (widget)
            widget.destroy();
        var div = dojo.create('div',{id:'set_details'});
        dojo.place(div, dojo.byId('details_placeholder'));

        dojo.query(div).instantiate(
            modules.DetailSetDetails,{data:this.data.details});
    },
});


