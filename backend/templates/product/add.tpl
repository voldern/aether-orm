<div class="grid one">
<form action="/product/add" method="get">
    <fieldset>
        <legend>Add product</legend>
        {if $ok eq true}
        <label class="notify">"{$title}" created succesfully</label>
        {/if}
        <label for="product_name">Name</label>
        <input id="product_name" type="text" name="product_name" 
            tabindex="1" />
        <br />
        <button type="submit">Save</button>
    </fieldset>
</form>
</div>
