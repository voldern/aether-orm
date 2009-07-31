/**
 * List all detail sets
 */

dojo.provide("modules.DetailConnectionsEditor");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

/**
 * Editor to connect current loaded resource (detail/set/template)
 * to other resources (detail/set/template)
 * This editor is rendered twice so it needs to be supplied
 * what type of resource it should operate on as a css class
 * for the html constructor.
 */
dojo.declare("modules.DetailConnectionsEditor", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detail_connection_editor.html"),
    type: 'set',
    reloader: false,
    update: function(cls,id) {
        this.data = {};
        /* When update is called without any arguments use
         * the previously set ones
         */
        if (!cls) {
            cls = this._cls;
            id = this._id;
        }
        else {
            /* Store class and id */
            this._cls = cls;
            this._id = id;
            /* 
             * Define supported types of data to list for this widget
             * Based on class (left/right) we will render what is left in types
             * array after removing the type of resource the connections
             * can be against (typically viewing a detail it will be set and
             * template)
             */
            var types = ['set','detail','template'];
            types.removeValue(cls);
            if (this.class == 'left')
                this.type = types[0];
            else
                this.type = types[1];
        }
        /**
         * Define what content to send to xhrGet for data
         * retrieval
         */
        var content = {
            module: 'Details',
            type: cls,
            id: id,
        };
        this.data.type = cls;
        this.data.type_to = this.type;
        this.data.id = id;
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
        /**
         * Pull in data and rerender this list
         */
        dojo.xhrGet({
            url: '/details',
            content: content,
            handleAs: 'json',
            load: dojo.hitch(this, function(r,ioArgs) {
                this.data.records = r.records;
                this.render();
                auto.findNodes(this.domNode);
                auto.attachEvents();
                /*
                 * Attach event to a button that reloads the list
                 */
                if (this.reloader == false) {
                    this.reloader = dojo.query('button.reloader', this.domNode).connect(
                        'onclick', dojo.hitch(this, function(evt) {
                            dojo.stopEvent(evt);
                            this.update();
                        })
                    );
                }
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
    /**
     * Attach events to rendered content
     * Reload button will call update()
     * and selecting a row will call update() after
     * storing that connection (so sort is correct)
     */
    postCreate: function() {
        dojo.subscribe('/details/edit', this, "update");
    },
});



