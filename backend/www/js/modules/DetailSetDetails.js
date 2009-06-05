/**
 * List all detail sets with underlying details
 */

dojo.provide("modules.DetailSetDetails");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare("modules.DetailSetDetails", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    /**
     * Use a file based html template
     */
    templatePath: dojo.moduleUrl("modules.templates", "detail_set_details.html"),
    /**
     * This gets called once the widget is loaded with its configuration
     * but before rendering so the data set in here will be available
     */
    postMixInProperties: function() {
        this.data.types = ['text','bool','date','numeric'];
    },
    postCreate: function() {
        var auto = new modules.AutoSave;
        auto.findNodes();
        auto.attachEvents();
    }
});



