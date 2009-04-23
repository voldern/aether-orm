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
                dojo.connect(node, "onblur", dojo.hitch(this, "save"));
            }));
        }
    },
    save: function(evt) {
        var tNode = evt.target,
        form = tNode.parentNode;

        while (form.tagName != "FORM") {
            form = form.parentNode;
        }

        dojo.xhr(dojo.attr(form, "method"), {
            handleAs: "json",
            url: dojo.attr(form, "action"),
            form: form,
            handle: function(response) {
                console.log(form);
                console.log(response);
            }
        });
    },
});
