/**
 * Dojo module for handling product with details
 */

dojo.provide("modules.ProductSearch");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("modules.ProductSearch", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "product_search.html"),
    postMixInProperties: function() {
        this.inherited(arguments);
    },
    postCreate: function() {
        dojo.query("#q").connect("onkeyup", dojo.hitch(this, function(ev) { this.update(ev); }));
    },
    update: function(ev) {
        if (ev.target.value.length == 0) {
            this.data.return = []
        }
        else {
            dojo.xhrGet({
                url: '/',
                content: {
                    module: 'ProductSearch',
                    query: ev.target.value,
                    service: 'ProductTitle'
                },
                handleAs: 'json',
                sync: true,
                load: dojo.hitch(this, function(resp,ioArgs) {
                    this.data = resp;
                }),
            });
        }
        this.render();
    }
});
