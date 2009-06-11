/**
 * List all detail sets with underlying details
 */

dojo.provide("modules.DetailSets");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");
dojo.require("modules.DetailSetEdit");

dojo.declare("modules.DetailSets", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detailsets.html"),

    postMixInProperties: function() {
        dojo.xhrGet({
            url: '/details/',
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
    renderSetEdit: function(evt) {
        dojo.stopEvent(evt);
        // Create edit set widget
        var link = dijit.byId(evt.target);
        var id = link.parentNode.getAttribute('id').substr(4);
        widget = dijit.byId('set_edit');
        if (widget)
            widget.destroy();
        var div = dojo.create('div',{id:'set_edit'});
        dojo.place(div, dojo.query('div.grid.one')[0]);
        dojo.query(div).instantiate(
            modules.DetailSetEdit,{set_id:id});
    },
    postCreate: function() {
        dojo.query("li.detail_set a").connect('onclick', 
            dojo.hitch(this, function(evt) {
                this.renderSetEdit(evt);
            })
        );
        dojo.query("a#set_add").connect('onclick', 
            dojo.hitch(this, function(evt) {
                this.renderSetEdit(evt);
            })
        );
    },
});

