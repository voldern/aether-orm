// Register custom module path
dojo.registerModulePath("modules", "../../modules");

// Requires
dojo.require("dojo.behavior");

dojo.addOnLoad(function() {
    dojo.behavior.add({
        ".notice": function(n) {
            var notices = dojo.query("<a class=\"icon close\" href=\"#\">Close</a>")
            notices.connect("onclick", function(evt) {
                dojo.stopEvent(evt);
                dojo.destroy(this.parentNode);
            }).appendTo(n);

            // Fade out after 5 seconds
            setTimeout(function() {
                dojo.query(n)
                    .animate({ opacity: 0 }, 1000, null, function() {
                        dojo.destroy(n);
                    });
            }, 5000)
        }
    });
    dojo.behavior.apply();
});
