/**
 * List all detail sets
 */

dojo.provide("modules.DetailDataList");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("modules.DetailDataList", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detail_data_list.html"),

    postMixInProperties: function() {
        var content = {module:'Details'};
        var prefix = '';
        switch (this['class']) {
            case 'set':
                content.service = 'GetSets';
                prefix = 'set';
                break;
            case 'detail':
                content.service = 'GetDetails';
                prefix = 'detail';
                break;
            case 'template':
                content.service = 'GetTemplates';
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
    },
    postCreate: function() {
        dojo.query("li a",this.domNode).connect('onclick', 
            dojo.hitch(this, function(evt) {
                dojo.stopEvent(evt);
                arg = evt.target.getAttribute('href');
                arg = arg.substring(arg.indexOf("#")+1);
                /* [0] = prefix, [1] = $id */
                parts = arg.split("=");
                dojo.publish("/details/edit", 
                    [
                        parts[0],
                        parts[1],
                        evt.target
                    ]);
            })
        );
        /* Mark as selected in data list */
        dojo.subscribe("/details/edit", this, "setSelected");
    },

    /**
     * Mark a node as selected (clicked on)
     */
    setSelected: function(cls, id, node) {
        // Find prev selected node and remove selection
        var li = node.parentNode;
        var previous = dojo.query('li.selected', li.parentNode)
            .removeClass('selected');
        dojo.addClass(li, 'selected');
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
});

