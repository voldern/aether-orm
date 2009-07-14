<fieldset>
<legend>Edit images <img alt="[X]" id="closeEdit" /></legend>
<ul>
{foreach $images as $image}
    <li class="clearfix">
        <div class="imageContainer">
            <img class="scaled" src="http://img.gfx.no{$image->getContainerUrl(200, 200)}" />
            <img style="display: none;" class="original" src="http://img.gfx.no{$image->original}" />
        </div>
        <div class="imageProperties">
        <form class="autosaveForm" method="get" action="?module=ImageEditor&service=saveImage">
            <input type="hidden" name="imageId" value="{$image->id}" />
            <label for="imageTitle">Tittel: </label>
            <input class="autosave string" id="imageTitle" name="title" value="{$image->title}" />
            <br />
            <label for="imageCaption">Bildetekst: </label>
            <input class="autosave string" id="imageCaption" name="caption" value="{$image->caption}" />
            <br />
            <label for="imagePhotographer">Kreditering: </label>
            <input class="autosave string" id="imagePhotographer" name="photographer" value="{$image->photographer}" />
            <br />
        </form>
        </div>
    </li>
{/foreach}
</ul>
</fieldset>