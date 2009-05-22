// Register custom module path
dojo.registerModulePath("modules", "../../modules");

// Requires
dojo.require("dojo.behavior");

dojo.addOnLoad(function() {
    dojo.behavior.add({
        ".notice": function(n) {
            dojo.query("<a class=\"icon close\" href=\"#\">Close</a>")
                .connect("onclick", function(evt) {
                    dojo.stopEvent(evt);
                    dojo.destroy(this.parentNode);
                }).appendTo(n);
        }
    });
    dojo.behavior.apply();
});
