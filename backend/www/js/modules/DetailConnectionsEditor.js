/**
 * List all detail sets
 */

dojo.provide("modules.DetailConnectionsEditor");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("modules.DetailConnectionsEditor", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detail_connection_editor.html"),
    type: 'set',
    update: function(cls,id) {
        // Define supported types of data to list for this widget
        this.data = {};
        var types = ['set','detail','template'];
        types.removeValue(cls);
        if (this.class == 'left')
            this.type = types[0];
        else
            this.type = types[1];
        /**
         * Define what content to send to xhrGet for data
         * retrieval
         */
        var content = {
            module: 'Details',
            type: cls,
            id: id,
        };
        switch (this.type) {
            case 'set':
                this.data.headline = 'Sets';
                content.service = 'GetSetsFor';
                break;
            case 'detail':
                this.data.headline = 'Details';
                content.service = 'GetDetailsFor';
                break;
            case 'template':
                this.data.headline = 'Templates';
                content.service = 'GetTemplatesFor';
                break;
        }
        console.log(content);
        dojo.xhrGet({
            url: '/details',
            content: content,
            handleAs: 'json',
            load: dojo.hitch(this, function(r,ioArgs) {
                this.data.records = r;
                console.log(this.data);
                this.render();
            }),
        });
    },
    /**
     * This is a work around for a bug in dojox dtl with dijit
     * widgets nested
     */
    widgetsInTemplate: false,
    render: function() {
        this.inherited(arguments);
        dojo.parser.parse(this.domNode);
    },
    postCreate: function() {
        dojo.subscribe('/details/edit', this, "update");
    },
});



