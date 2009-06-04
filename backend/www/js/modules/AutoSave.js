/**
 * Dojo module for attaching autosaving
 * Use:
 * dojo.require("modules.AutoSave");
 * dojo.addOnLoad(function() {
 *  var auto = new modules.AutoSave;
 *  auto.findNodes();
 *  auto.attachEvents();
 * });
 * Example markup:
 * <form class="autosaveForm">
 *   <input type="text" class="string autosave" name="foo" />
 * </form>
 * The three classes in use here is needed.
 * There are three types of type classes supporter by autosavefields:
 * * string
 * * int
 * * bool
 *
 */

dojo.provide("modules.AutoSave");

dojo.declare("modules.AutoSave", null, {
    context: dojo.doc,
    fields: {
        ints: [],
        bool: [],
        string: [],
    },
    constructor: function() {
    },
    ev: null,

    // Find all nodes with autosave class inside a form with autosaveForm class
    findNodes: function() {
        dojo.query("form.autosaveForm")
            .forEach(dojo.hitch(this, function(form) {
                dojo.query(".autosave", form)
                    .forEach(dojo.hitch(this, function(elem) {
                        for (var field in this.fields) {
                            if (dojo.hasClass(elem, field)) {
                                this.fields[field].push(elem);
                            }
                        }
                    }));
        }));
        dojo.query(".autosave.always")
            .forEach(function(elem) {
                for (var field in this.fields) {
                    if (dojo.hasClass(elem, field)) {
                        this.fields[field].push(elem);
                    }
                }
            });
    },

    // Attach saving method on all elements with found in this.findNodes. But only save if element has changed value
    attachEvents: function() {
        for (var field in this.fields) {
            dojo.forEach(this.fields[field], dojo.hitch(this, function(node) {
                dojo.connect(node, "onchange", dojo.hitch(this, function(evt) {
                    if (node.type == "checkbox" || 
                            (node.type == "radio" && node.checked === true) ||
                            node.tagName == "SELECT") {
                                this.save(evt);
                    }
                    else {
                        this.ev = dojo.connect(node, "onblur", dojo.hitch(this, "save"));
                    }
                }));
            }));
        }
    },

    // Save method, xhrGet/Post based on the parent form action attr
    save: function(evt) {
        var tNode = evt.target,
        load = dojo.place(dojo.create("img", { src: "/images/spin.gif" }), tNode, "after");

        // Traverse to find the correct parentNode
        if (dojo.hasClass(tNode, "always")) {
            var content = {};
            dojo.setObject(tNode.name, tNode.value, content);
            dojo.xhrPost({
                handleAs: "json",
                url: tNode.href,
                content: content,
                handle: function(response) {
                    dojo.destroy(load);
                }
            });
        }
        else {
            var form = tNode.parentNode;
            while (form.tagName != "FORM")
                form = form.parentNode;

            dojo.xhr(dojo.attr(form, "method"), {
                url: form.action,
                handleAs: "json",
                form: form,
                handle: function(response) {
                    dojo.destroy(load);
                }
            }, (dojo.attr(form, "method") == "post"));
        }
        if (this.ev)
            dojo.disconnect(this.ev);
    },
});
