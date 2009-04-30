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
    attachEvents: function() {
        for (var field in this.fields) {
            dojo.forEach(this.fields[field], dojo.hitch(this, function(node) {
                dojo.connect(node, "onchange", dojo.hitch(this, function() {
                    this.ev = dojo.connect(node, "onblur", dojo.hitch(this, "save"));
                }));
            }));
        }
    },
    save: function(evt) {
        var tNode = evt.target,
        form = tNode.parentNode,
        load = dojo.place(dojo.create("img", { src: "http://static.tek.no/images/main/spinner.gif" }), tNode, "after");
        while (form.tagName != "FORM")
            form = form.parentNode;

        dojo.xhr(dojo.attr(form, "method"), {
            handleAs: "json",
            form: form,
            handle: function(response) {
                console.log(form);
                console.log(response);
                dojo.destroy(load);
            }
        }, (dojo.attr(form, "method") == "post") ? true : false);
        dojo.disconnect(this.ev);
    },
});
