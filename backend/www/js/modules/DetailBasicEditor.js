/**
 * List all detail sets
 */

dojo.provide("modules.DetailBasicEditor");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("modules.DetailBasicEditor", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detail_basic_editor.html"),
    update: function(cls,id) {
        var content = {module:'Details',id:id};
        this.data = {};
        var prefix = '';
        switch (cls) {
            case 'set':
                content.service = 'GetSet';
                prefix = 'set';
                break;
            case 'detail':
                content.service = 'GetDetail';
                prefix = 'detail';
                break;
            case 'template':
                content.service = 'GetTemplate';
                prefix = 'template';
                break;
        }
        dojo.xhrGet({
            url: '/details/',
            content: content,
            handleAs: 'json',
            sync: true,
            load: dojo.hitch(this, function(resp,ioArgs) {
                this.data = resp;
            }),
        });
        this.data.prefix = prefix;
        this.data.types = ['text','bool','date','numeric'];
        this.render();
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


