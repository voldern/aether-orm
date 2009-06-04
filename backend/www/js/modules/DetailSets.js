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
    renderSetEdit: function(evt) {
        dojo.stopEvent(evt);
        // Create edit set widget
        var link = dijit.byId(evt.target);
        // Check if widget exists
        widget = dijit.byId('set_edit');
        if (widget)
            widget.destroy();
        // Whether or not to load existing
        if (link.parentNode.nodeName == 'DIV') {
            var id = 'new';
        }
        else {
            var id = link.parentNode.getAttribute('id').substr(4);
        }
        var refNode = dojo.query('div.grid.one')[0];
        // Set up replacement node
        var replaceNode = document.createElement('div');
        dojo.place(replaceNode, refNode);

        // Create widget
        widget = new modules.DetailSetEdit(
            {set_id:id, id:'set_edit'},replaceNode);
    },
    postCreate: function() {
        console.log("Created");
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

