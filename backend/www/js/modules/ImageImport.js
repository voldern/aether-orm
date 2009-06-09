/**
 * Make it possible to search for a image from old systems and import it
 */

dojo.provide('modules.ImageImport');
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");

dojo.declare('modules.ImageImport',
             [dijit._Widget, dojox.dtl._DomTemplated], {
                 // Use a file based html template
                 templatePath: dojo.moduleUrl('modules.templates', 'image_import.html'),
             });
