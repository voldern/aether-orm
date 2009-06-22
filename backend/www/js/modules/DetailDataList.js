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
        // Attach publish event for onclick on links
        dojo.query("li a",this.domNode).connect('onclick', 
            dojo.hitch(this, function(evt) {
                dojo.stopEvent(evt);
                arg = evt.target.getAttribute('href');
                arg = arg.substring(arg.indexOf("#")+1);
                /**
                 * [0] = $prefix ('detail')
                 * [1] = $id  (42)
                 */
                parts = arg.split("=");
                dojo.publish("/details/edit", 
                    [
                        parts[0],
                        parts[1]
                    ]);
            })
        );
        // Add handler to add new
        dojo.query("li form input", this.domNode).connect('onblur',
            dojo.hitch(this, function(evt) {
                name = evt.target.value;
                if (name.length >= 2) {
                    if (!this.nameExists(name)) {
                        dojo.xhrGet({
                            url: '/details/',
                            content: {
                                url: '/details',
                                module: 'Details',
                                service: 'Create',
                                type: this.data.prefix,
                                name: name
                            },
                            handleAs: 'json',
                            sync: true,
                            load: dojo.hitch(this, function(resp,ioArgs) {
                                if (resp.response.ok == true) {
                                    this.update();
                                    dojo.publish("/details/edit", 
                                        [
                                            this.data.prefix,
                                            resp.info.id
                                        ]);
                                }
                            }),
                        });
                    }
                    else {
                        console.log("Name exists");
                    }
                }
                else {
                    console.log("Name must be longer than 2 characters");
                }
                this.nameExists(name);
            })
        );
        /* Mark as selected in data list */
        dojo.subscribe("/details/edit", this, "setSelected");
    },

    update: function() {
        this.postMixInProperties();
        this.render();
    },
    
    /**
     * Check if this set/detail/template name exists allready
     */
    nameExists: function(name) {
        return false;
    },

    /**
     * Mark a node as selected (clicked on)
     */
    setSelected: function(cls, id) {
        var node = dojo.byId('data_'+cls+'_'+id);
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

