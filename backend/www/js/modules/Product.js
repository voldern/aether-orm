/**
 * Dojo module for handling product with details
 */

dojo.provide("modules.Product");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("modules.Product", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "product.html"),
    postMixInProperties: function() {
        dojo.xhrGet({
            url: '/products/',
            content: {
                module: 'Manifestation',
                service: 'GetByWork',
                id: this.id,
            },
            handleAs: 'json',
            sync: true,
            load: dojo.hitch(this, function(resp,ioArgs) {
                this.data = resp;
            }),
        });
    },
});
