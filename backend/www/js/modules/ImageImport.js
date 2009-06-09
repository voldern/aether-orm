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
                 postCreate: function() {
                     dojo.query('#image_import_submit').connect(
                         'onclick', dojo.hitch(this, function(e) {
                             dojo.stopEvent(e);
                             var articles = dojo.byId('articles_input').value;
                             var products = dojo.byId('products_input').value;

                             dojo.xhrGet({
                                 url: '',
                                 content: {
                                     module: 'ImageImport',
                                     service: 'lookIn',
                                     articles: articles,
                                     products: products,
                                 },
                                 handleAs: 'json',
                                 load: dojo.hitch(this, function(resp, ioArgs) {
                                     console.log(resp);
                                     console.log(ioArgs);
                                 }),
                             });
                         })
                     );
                 },
             });