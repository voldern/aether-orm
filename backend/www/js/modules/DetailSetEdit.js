/**
 * List all detail sets with underlying details
 */

dojo.provide("modules.DetailSetEdit");
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");
dojo.require("modules.DetailSetDetails");

dojo.declare("modules.DetailSetEdit", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    /**
     * Use a file based html template
     */
    templatePath: dojo.moduleUrl("modules.templates", "detail_set_edit.html"),
    /**
     * This gets called once the widget is loaded with its configuration
     * but before rendering so the data set in here will be available
     */
    postMixInProperties: function() {
        if (this.set_id == 'new') {
            this.data = {
                title: " ",
                details: {
                    records: []
                },
            };
        }
        else {
            dojo.xhrGet({
                url: '/detail/',
                content: {
                    module: 'Details',
                    service: 'GetSet',
                    id: this.set_id,
                },
                handleAs: 'json',
                sync: true,
                load: dojo.hitch(this, function(resp,ioArgs) {
                    this.data = resp;
                }),
            });
        }
        this.data.types = ['text','bool','date','numeric'];
    },
    postCreate: function() {
        this.updateDetails();
        auto.findNodes(dojo.byId('edit_detail_set').parentNode);
        auto.attachEvents();

        // Add details
        dojo.query('#add_detail').connect('onclick', dojo.hitch(this, function(e) {
            // add detail
            dojo.stopEvent(e);
            dojo.xhrGet({
                url: e.currentTarget.getAttribute('href'),
                load: dojo.hitch(this, function(response, ioArgs) {
                    this.postMixInProperties();
                    this.updateDetails();
                })
            });
            // render details
        }));
    },
    updateDetails: function() {
        // Show spinner for a little time
        var refNode = dojo.query('#details_placeholder')[0];
        // Set up replacement node
        var replaceNode = document.createElement('div');
        var spinner = dojo.create("img", 
            {src: "/images/spin-large.gif"});
        dojo.place(spinner, replaceNode);
        replaceNode = spinner;

        // Check if widget exists
        widget = dijit.byId('set_details');
        if (widget)
            widget.destroy();

        dojo.place(replaceNode, refNode);

        // Create widget
        widget = new modules.DetailSetDetails(
            {data: this.data.details, id:'set_details'},replaceNode);
    },
});


