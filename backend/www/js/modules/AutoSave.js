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
        int: [],
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
        form = tNode.parentNode,
        load = dojo.place(dojo.create("img", { src: "/images/spin.gif" }), tNode, "after");

        // Traverse to find the correct parentNode
        while (form.tagName != "FORM")
            form = form.parentNode;

        dojo.xhr(dojo.attr(form, "method"), {
            handleAs: "json",
            form: form,
            handle: function(response) {
                console.log(form);
                console.log(response);
                dojo.destroy(load);
                dojo.removeAttr(tNode, "disabled");
            }
        }, (dojo.attr(form, "method") == "post"));
        if (this.ev)
            dojo.disconnect(this.ev);
    },
});
