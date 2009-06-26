/**
 * Extend arrays to allow something like this:
 * >>> foo = ['a','b','c','d'];
 * ["a", "b", "c", "d"]
 * >>> foo.removeValue('c');
 * 3
 * >>> foo
 * ["a", "b", "d"]
 */
Array.prototype.removeValue = function(value) {
    var ind = this.indexOf(value);
    if (ind == -1)
        return this;
    var rest = this.slice(ind+1);
    this.length = this.length - (rest.length + 1);
    return this.push.apply(this,rest);
};
// Register custom module path
dojo.registerModulePath("modules", "../../../modules");

// Requires
dojo.require("dojo.behavior");
dojo.require("modules.ProductSearch");

dojo.addOnLoad(function() {
    // Traverse document and require widgets
    dojo.query("[dojoType]")
        .forEach(function(node) {
            dojo.require(dojo.attr(node, "dojoType"));
        });

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

    // Add focus to e-mail if login is required
    if (dojo.byId("email"))
        dojo.byId("email").focus();
    auto = new modules.AutoSave;
});
