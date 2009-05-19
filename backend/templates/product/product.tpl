<h1>Product</h1>
<form action="/products/{$id}/?module=Product&amp;service=Save" method="post" class="autosaveForm">
    <fieldset>
        <legend>Product blueprint</legend>
        <input id="id" type="hidden" value="{$id}" name="id" />
        <label for="blueprint">Name</label>
        <input id="blueprint" class="autosave string" type="text" value="{$title}" name="blueprint" />
    </fieldset>
    <fieldset>
        <legend>Product variations</legend>
        {foreach from=$manifestations item=m}
            <div id="container_mani_{$m->get('id')}">
                <label for="mani_{$m->get('id')}">[#{$m->get('id')}] Name</label>
                <input class="string autosave" id="mani_{$m->get('id')}" type="text" value="{$m->get('title')}"
                    name="mani[{$m->get('id')}]" />
                <a href="/products/?module=Manifestation&amp;service=Delete&amp;id={$m->get('id')}" 
                    class="delete_manifestation">X</a>
                <br />
            </div>
        {/foreach}
        <a href="/products/?module=Manifestation&amp;service=Add&amp;work_id={$id}" 
            id="add_manifestation" class="ajax_link">Add variant</a>
    </fieldset>
</form>
