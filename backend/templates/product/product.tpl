<h1>Product</h1>
<form action="/products/{$id}/edit" method="post" class="autosaveForm">
    <fieldset>
        <legend>Product blueprint</legend>
        <label for="blueprint">Name</label>
        <input id="blueprint" type="text" value="{$title}" name="blueprint" />
    </fieldset>
    <fieldset>
        <legend>Product variations</legend>
        {foreach from=$manifestations item=m}
            <label for="mani_{$m->get('id')}">[#{$m->get('id')}] Name</label>
            <input id="mani_{$m->get('id')}" type="text" value="{$m->get('title')}"
                name="mani[{$m->get('id')}]" />
            <br />
        {/foreach}
    </fieldset>
</form>
