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
        <div dojoType="modules.Product" id="{$id}"></div>
        <a href="/products/?module=Manifestation&amp;service=Add&amp;work_id={$id}" 
            id="add_manifestation" class="ajax_link">Add variant</a>
    </fieldset>
</form>
