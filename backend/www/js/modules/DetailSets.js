/**
 * List all detail sets with underlying details
 */

dojo.provide("modules.DetailSets");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("modules.DetailSets", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detailsets.html"),
    postMixInProperties: function() {
        dojo.xhrGet({
            url: '/detail/',
            content: {
                module: 'Details',
                service: 'GetSets',
            },
            handleAs: 'json',
            sync: true,
            load: dojo.hitch(this, function(resp,ioArgs) {
                this.data = resp;
            }),
        });
    },
});

