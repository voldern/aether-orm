dojo.require('dijit.Tooltip');
dojo.require('dojo.parser');

dojo.provide("modules.Duplicate");
dojo.declare("modules.Duplicate", null, {
    url: "",
    
    constructor: function(url) {
        // Look for the duplicateURL
        this.url = url;
    },
    
    attachCheck: function() {
        dojo.query('.duplicateCheck').connect('onblur', dojo.hitch(this, 'checkDuplicates'));
    },

    checkDuplicates: function(evt) {
        value = evt.target.value; //dojo.attr(evt.target, 'value');

        // Remove old notice
        if (dojo.byId('duplicateCount') != null) {
            dojo.query('#duplicateCount').orphan();
            dojo.removeClass(evt.target, 'error');
        }

        dojo.xhrGet({
            url: this.url + "&check=" + value,
            handleAs: "json",
            timeout: 5000,
            
            load: function(response, ioArgs) {
                console.log(response);

                if (response.duplicateCount > 0) {
                    // Give the field the class 'error'
                    dojo.addClass(evt.target, 'error');

                    // Create textual information with dupe count
                    var text = 'Found ' + response.duplicateCount + ' duplicates';
                    dojo.place(dojo.create("div", { id: 'duplicateCount', 
                                                    class: 'tooltip error',
                                                    innerHTML: text }),
                               evt.target, 'after');

                    // Generate list of duplicates
                    var dupeList = "<ul>\n";
                    dojo.forEach(response.duplicates,
                                 function(dupe) {
                                     dupeList += '<li>' + dupe + "</li>\n";
                                 });
                    dupeList += "</ul>";

                    // Place tooltip below the text
                    var tooltip = new dijit.Tooltip({
                        connectId: ['duplicateCount'],
                        position: ['below'],
                        showDelay: 0,
                        label: dupeList
                    });
                }
                return response;
            },

            error: function(response, ioArgs) {
                console.error("HTTP status code:", ioArgs.xhr.status);
                console.log(response);
                return response;
            }
        });
    }
    
});
