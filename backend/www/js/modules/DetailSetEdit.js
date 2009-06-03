/**
 * List all detail sets with underlying details
 */

dojo.provide("modules.DetailSetEdit");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("modules.DetailSetEdit", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detail_set_edit.html"),
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
        console.log(this.data);
    }
});


