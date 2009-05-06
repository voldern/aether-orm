dojo.require("modules.AutoSave");
dojo.addOnLoad(function() {
 var auto = new modules.AutoSave;
 auto.findNodes();
 auto.attachEvents();
});
