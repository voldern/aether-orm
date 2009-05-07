dojo.require("modules.AutoSave");
dojo.require('modules.Duplicate');
dojo.addOnLoad(function() {
    var auto = new modules.AutoSave;
    auto.findNodes();
    auto.attachEvents();

    var dup = new modules.Duplicate('/products/?module=ProductAdd&service=duplicateCheck');
    dup.attachCheck();
});
