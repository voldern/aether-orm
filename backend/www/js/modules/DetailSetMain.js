/**
 * Main facade for detail set editing
 */

dojo.provide("modules.DetailSetMain");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");
dojo.require("dojo.parser");
dojo.require("dojox.dtl.contrib.dijit");

dojo.require("modules.DetailSets");
dojo.require("dijit.Dialog");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.TextBox");

var sets;
dojo.declare("modules.DetailSetMain", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detail_set_main.html"),

    postCreate: function() {
        this.update();
        //dojo.query("#set_list").instantiate(modules.DetailSets);
        /**
         * Hook into the "add set" widget to rerender the list
         * of sets once create is done
         */
        var addSet = dijit.byId('add_set');
        addSet.execute = dojo.hitch(this, function(arg) {
            var title = arg.title;
            dojo.xhrGet({
                url: '/details',
                handleAs: 'json',
                content: {
                    module: 'Details',
                    service: 'AddSet',
                    title: title
                },
                load: dojo.hitch(this, function(r,ioArgs) {
                    if (r.response.ok == true) {
                        this.update();
                    }
                    else {
                        console.log("IT FAILED FFS");
                        console.log(r.response.message);
                    }
                })
            });
        });
    },
    update: function() {
        widget = dijit.byId('set_list');
        if (widget)
            widget.destroy();
        var div = dojo.create('div',{id:'set_list'});
        dojo.place(div, dojo.byId('set_list_container'));
        dojo.query(div).instantiate(modules.DetailSets);
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

