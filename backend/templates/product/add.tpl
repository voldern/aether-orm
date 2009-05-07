<div class="grid one">
<form action="/products" method="get">
    <fieldset>
        <legend>Add product</legend>
        {if $error eq true}
        <label class="notify">"{$title}" was not created due to some error</label>
        {/if}
        <label for="product_name">Name</label>
        <input id="product_name" type="text" name="product_name" class="duplicateCheck" />
        <br />
        <label for="variants">Antall varianter</label>
        <input id="variants" type="text" name="variants" size="4" />
        <br />
        <button type="submit">Save</button>
    </fieldset>
</form>
</div>
<div class="clear"></div>
