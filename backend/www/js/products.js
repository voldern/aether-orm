dojo.require('modules.Duplicate');
dojo.addOnLoad(function() {
    var dup = new modules.Duplicate('/products/?module=ProductAdd&service=duplicateCheck');
    dup.attachCheck();
});
