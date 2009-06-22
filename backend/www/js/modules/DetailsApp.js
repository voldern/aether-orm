/**
 * Main facade for detail set editing
 */

dojo.provide("modules.DetailsApp");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");
dojo.require("dojo.parser");
dojo.require("dojox.dtl.contrib.dijit");

dojo.declare("modules.DetailsApp", 
    [dijit._Widget, dojox.dtl._DomTemplated], {

    templatePath: dojo.moduleUrl("modules.templates", "details_app.html"),

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
