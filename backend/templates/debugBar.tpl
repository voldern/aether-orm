<div id="aetherDebugBar" class="nimbus" style="text-align: left; position: absolute; width: 240px; max-width: 400px; right: 16px; top: 0; border: 1px solid #f0ede7; -moz-border-radius-bottomleft: 10px; -moz-border-radius-bottomright: 10px; background: #f0ede7; z-index: 10000; font-size: 8pt; padding: 10px 2px 4px; color: #847864;">
    <h2 style="text-align: center; margin: 0; cursor: pointer; letter-spacing: 1px; font-size: 8pt; font-weight: normal; text-decoration: underline; text-transform: uppercase" title="Click to open">Debug center</h2>
<div style="display: none; padding: 2px 12px 40px 12px;">
<ul>
{foreach from=$timers key=$name item=$timer}
    <li style="width:100%; clear: both; padding-top: 15px; font-size: 9pt; letter-spacing: 1px; text-transform: uppercase; font-weight: bold; border-bottom: 1px solid #847864">{{$name}}:</li>
    <li>
    <ul>
    {foreach from=$timer key=$point item=$data}
        {{if $point != "start"}}
        <li style="clear:left; padding: 3px 0; float: left; width: 100%;">
        <span style="font-weight: bold; float:left; overflow:hidden;">{{$point}}</span>
        <span style="float:right;">{{$data.elapsed}} seconds</span>
        </li>
        {{/if}}
    {{/foreach}}
    </ul>
    </li>
{{/foreach}}
</ul>
</div>
</div>
<script type="text/javascript">
function load() {
    // Make div openable by click
    var opener = dojo.query("#aetherDebugBar > h2")[0];
    dojo.connect(opener, "onclick", aetherDebugPanelToggle);
}
function aetherDebugPanelToggle(event) {
    var div = event.target.nextSibling.nextSibling;
    var ads = dojo.query(".advert");
    if (div.style.display == "none") {
        div.style.display = "block";
        ads.forEach(function(ad) {
            ad.parentNode.style.display = "none";
        });
    }
    else {
        div.style.display = "none";
        ads.forEach(function(ad) {
            ad.parentNode.style.display = "block";
        });
    }
}
load();
</script>

