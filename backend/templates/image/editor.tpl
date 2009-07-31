<fieldset>
<legend>Edit images <img alt="[X]" id="closeEdit" /></legend>
{if $images}
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
            <label for="imageLicense">Lisens: </label>
            <select class="autosave string" id="imageLicense" name="license">
                {foreach $licenseTypes as $l}
                <option value="{$l}"{if $image->license == $l} selected="selected"{/if}>
                    {if $l == 'attribution'}Attribution{/if}
                    {if $l == 'attribution_share_alike'}Attribution Share Alike{/if}
                    {if $l == 'attribution_no_derivatives'}Attribution No Derivatives{/if}
                    {if $l == 'attribution_non-commercial'}Attribution Noncommercial{/if}
                    {if $l == 'attribution_non-commercial_share_alike'}Attribution Noncommercial Share Alike{/if}
                    {if $l == 'attribution_non-commercial_no_derivatives'}Attribution Noncommercial No Derivatives{/if}
                    {if $l == 'all_rights_reserved'}All Rights Reserved{/if}
                    {if $l == 'public_domain'}Public Domain{/if}
                </option>
                {/foreach}
            </select>
            <br />
        </form>
        </div>
    </li>
{/foreach}
</ul>
{else}
<p class="warning">Select the images you want to edit before pressing edit.</p>
{/if}
</fieldset>
