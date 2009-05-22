dojo.provide("modules.Validate");

/**
 * Method for checking if various field types are correct
 * Extends dojo.NodeList
 * Example:
 * dojo.require("modules.Validate");
 * dojo.addOnLoad(function() {
 *     dojo.query("#intFieldToCheck").validate('int') ? console.log("YAY!"); : console.log("fail!");
 *
 * }
 *
 */
dojo.extend(dojo.NodeList, {
    validate: function(type) {
        switch (type) {
            case 'int':
                this._validateInt();
                break;
            case 'bool':
                this._validateBool();
                break;
            case 'text':
                this._validateText();
                break;
            case 'calc':
                this._validateCalc();
                break;
        }
    },
    _validateInt: function() {
        this.onkeyup(dojo.hitch(this, function(evt) {
            return this._regexCheck(/^[0-9]+$/);
        }));
    },
    _validateText: function() {
    },
    _validateBool: function() {
        this.onkeyup(dojo.hitch(this, function(evt) {
            return this._regexCheck(/(true|false)/);
        }));
    },
    _validateCalc: function() {
    },
    _regexCheck: function(regex) {
        this.forEach(function(node) {
            if (regex.test(node.value))
                return true;
            else
                return false
        });
    },
});
