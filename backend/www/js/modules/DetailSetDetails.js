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
        // The other properties comes from DetailSetEdit
        this.data.types = ['text','bool','date','numeric'];
    },
    postCreate: function() {
        auto.findNodes(this.domNode);
        auto.attachEvents();
        // Handle deletion
        dojo.query(".delete_detail").connect('onclick', function(evt) {
            dojo.stopEvent(evt);
            dojo.xhrGet({
                url: evt.currentTarget.getAttribute('href'),
                handleAs: 'json',
                load: function(r,ioArgs) {
                    if (r.response.ok == true) {
                        // Visually destroy
                        var id = r.request.get.id;
                        var elem = dojo.query("form#detail_row_" + id)
                            .style("overflow",'hidden')
                            .animate({
                                    height:0,
                                    backgroundColor: "red"
                                }, 
                                1000, null, function() {
                                    elem.destroy(); 
                                }
                            );
                    }
                    else {
                        console.log(r.response.message);
                    }
                },
            });
        });
    }
});



