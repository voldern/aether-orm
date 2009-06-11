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
        this.keyBinds = {
            38: 'up',
            40: 'down',
            27: 'esc',
            13: 'enter'
        };
    },
    update: function(ev) {
        if (this.keyBinds[ev.keyCode] == undefined) {
            if (ev.target.value.length == 0) {
                this.data = {};
                this.data.return = [];
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
        else {
            if (this.keyBinds[ev.keyCode] == 'up') {
                this.stepItem(-1);
            }
            else if (this.keyBinds[ev.keyCode] == 'down') {
                this.stepItem(1);
            }
            else if (this.keyBinds[ev.keyCode] == 'enter') {
                this.selectItem();
            }
            else if (this.keyBinds[ev.keyCode] == 'esc') {
                this.data.return = [];
                this.render();
            }
        }
    },
    selectItem: function() {
        var search = dojo.byId("product_search");
        var list = dojo.query("#product_search li");
        var link = dojo.query("a", list[search.i])[0].href;
        window.location.href = link;
    },
    stepItem: function(step) {
        var search = dojo.byId("product_search");
        var list = dojo.query("#product_search li");

        if (search.i == undefined) {
            if (step > 0) 
                search.i = list.length - 1;
            else
                search.i = 0;
        }
        else {
            dojo.removeClass(list[search.i], "selected");
        }

        search.i = search.i + step;

        if (search.i == list.length)
            search.i = 0;
        else if (search.i < 0)
            search.i = list.length - 1;

        dojo.addClass(list[search.i], "selected");
    }
});
