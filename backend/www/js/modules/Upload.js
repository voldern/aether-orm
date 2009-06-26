/**
 * Dojo module for handling multiple uploads with progress reporting
 */

dojo.registerModulePath("upload", "../../../upload");

dojo.provide("modules.Upload");
dojo.require('upload.swfupload');
dojo.require('upload.swfuploadqueue');
dojo.require('dijit._Widget');
dojo.require("dojox.dtl._DomTemplated");
dojo.require("dijit.ProgressBar");

dojo.declare("modules.Upload", 
    [dijit._Widget, dojox.dtl._DomTemplated], {
    templatePath: dojo.moduleUrl("modules.templates", "upload.html"),
    constructor: function(args, node) {
        dojo.mixin(this, args);
    },
    uploadUrl: "/upload/",
    swfuploadLoadedCb: function() {
        this.uploadProgressText.textContent = "Last opp filer";
        dojo.publish("upload.swf_loaded", []);
    },
    dialogStartCb: function() {
        dojo.publish("upload.open_dialog", []);
        this.totalSizeQueued = 0;
        this.totalSizeComplete = 0;
    },
    fileQueuedCb: function(file) {
        dojo.publish("upload.queued", [file]);
        this.totalSizeQueued += file.size;
    },
    fileQueueErrorCb: function(file, errorCode, errorMessage) {
        dojo.publish("upload.queue_error", [file, errorMessage]);
    },
    dialogCompleteCb: function(numFilesSelected, numFilesQueued, numFilesInQueue) {
        dojo.publish("upload.starting", []);
        if (numFilesSelected > 0) 
            this.uploadProgressText.textContent = "(0%) Starter opplasting..";
        else
            this.uploadProgressText.textContent = "Ingen filer valgt";
        this.swfu.startUpload();
    },
    uploadStartCb: function(file) {
        dojo.publish("upload.start", [file]);
    },
    uploadProgressCb: function(file, bytesLoaded, bytesTotal) {
        dojo.publish("upload.progress", [file, bytesLoaded, bytesTotal]);
        this.totalSizeComplete += bytesLoaded;

        var progress = this.totalSizeComplete / this.totalSizeQueued;
        this.updateProgress(file, progress);
    },
    uploadErrorCb: function(file, errorCode, errorMessage) {
        dojo.publish("upload.error", [file, errorCode, errorMessage]);
    },
    uploadSuccessCb: function(file, serverData, response) {
        dojo.publish("upload.success", [file, response]);
    },
    uploadCompleteCb: function(file) {
        dojo.publish("upload.finished", [file]);
    },
    debugCb: function(message) {
        dojo.publish("upload.debug", [message]);
    },
    updateProgress: function(file, progress) {
        this.uploadProgressBar.style.width = 
            Math.round(progress * this.swfu.customSettings.progressWidth)+"px";
        this.uploadProgressText.textContent = "(" + Math.round(progress * 100) + "%) " + file.name;
    },
    postMixInProperties: function() {
        this.totalSizeQueued = 0;
    },
    postCreate: function() {
        this.swfu = new SWFUpload({
            upload_url: this.uploadUrl,
            flash_url: "/js/upload/swfupload.swf",
            file_size_limit: "1500 MB",
            file_types: "*.*",
            file_types_description: "Alle filer",
            swfupload_loaded_handler: dojo.hitch(this, this.swfuploadLoadedCb),
            file_dialog_start_handler: dojo.hitch(this, this.dialogStartCb),
            file_queued_handler: dojo.hitch(this, this.fileQueuedCb),
            file_queue_error_handler: dojo.hitch(this, this.fileQueueErrorCb),
            file_dialog_complete_handler: dojo.hitch(this, this.dialogCompleteCb),
            upload_start_handler: dojo.hitch(this, this.uploadStartCb),
            upload_progress_handler: dojo.hitch(this, this.uploadProgressCb),
            upload_error_handler: dojo.hitch(this, this.uploadErrorCb),
            upload_success_handler: dojo.hitch(this, this.uploadSuccessCb),
            upload_complete_handler: dojo.hitch(this, this.uploadCompleteCb),
            debug_handler: dojo.hitch(this, this.debugCb),
            custom_settings: {
                progressBar: "progressBar",
                progressWidth: 400
            },
            button_placeholder_id: this.uploadButton.id,
            button_image_url: 'images/upload-button.png',
            button_width: "65",
            button_height: "29",
            button_text: '<span class="upload">Finn filer</span>',
            button_text_style: '.upload { font-size: 16; }',
            button_text_left_padding: 3,
            button_text_top_padding: 3
        });
        this.uploadProgress.style.width = this.swfu.customSettings.progressWidth+"px";
    },
});
