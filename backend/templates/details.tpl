{$aether.providers.header}
<div dojoType="dijit.layout.BorderContainer"
    style="width:100%;height:550px;">
    <div dojoType="dijit.layout.TabContainer" region="left"
        style="width:19%;">
        <div dojoType="dijit.layout.ContentPane" title="Sets">
            <div dojoType="modules.DetailDataList" class="set"></div>
        </div>
        <div dojoType="dijit.layout.ContentPane" title="Details">
            <div dojoType="modules.DetailDataList" class="detail"></div>
        </div>
        <div dojoType="dijit.layout.ContentPane" title="Templates">
            <div dojoType="modules.DetailDataList" class="template"></div>
        </div>
    </div>
    <div dojoType="dijit.layout.BorderContainer" region="right"
        style="width:80%;height:511px;margin-top:29px;">
        <div dojoType="dijit.layout.ContentPane" region="top">
            <div dojoType="modules.DetailBasicEditor">
            </div>
        </div>
        <div dojoType="dijit.layout.ContentPane" region="left" style="width:49%;">
            <fieldset dojoType="modules.DetailConnectionsEditor" 
                class="left">
            </fieldset>
        </div>
        <div dojoType="dijit.layout.ContentPane" region="right" style="width:49%;">
            <fieldset dojoType="modules.DetailConnectionsEditor" 
                class="right">
            </fieldset>
        </div>
    </div>
</div>
{$aether.providers.footer}
