dojo.provide("modules.Duplicate");
dojo.declare("modules.Duplicate", null, {
    url: "",
    
    constructor: function() {
        // Look for the duplicateURL
        this.url = dojo.byId('duplicate_url').value;

        if (this.url == null) {
            console.error("Could not find duplicate_url");
        }
    },
    
    attachCheck: function() {
        if (this.url == null) {
            return;
        }
        
        dojo.query('.duplicateCheck').connect('onblur', dojo.hitch(this, 'checkDuplicates'));
    },

    checkDuplicates: function(evt) {
        value = evt.target.value; //dojo.attr(evt.target, 'value');
        console.log(this.url);

        dojo.xhrGet({
            url: this.url + "&check=" + value,
            handleAs: "json",
            timeout: 1000,
            
            load: function(response, ioArgs) {
                console.log(ioArgs);
                console.log(response);
                
                return response;
            },

            error: function(response, ioArgs) {
                console.error("HTTP status code:", ioArgs.xhr.status);
                return response;
            }
        });
    }
    
});