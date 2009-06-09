/**
 * Main facade for detail set editing
 */

dojo.provide("modules.DetailSetMain");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");
dojo.require("dojo.parser");
dojo.require("dojox.dtl.contrib.dijit");
dojo.require("modules.DetailSets");

dojo.declare("modules.DetailSetMain", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "detail_set_main.html"),
    postMixInProperties: function() {
    },
    postCreate: function() {
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

