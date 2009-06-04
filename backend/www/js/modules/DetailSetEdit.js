/**
 * List all detail sets with underlying details
 */

dojo.provide("modules.DetailSetEdit");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

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
        if (this.set_id == 'new') {
            this.data = {
                title: " ",
                details: {
                    records: []
                },
            };
        }
        else {
            dojo.xhrGet({
                url: '/detail/',
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
        var auto = new modules.AutoSave;
        auto.findNodes();
        auto.attachEvents();
    }
});


