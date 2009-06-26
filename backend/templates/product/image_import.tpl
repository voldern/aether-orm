<div>
    <fieldset>
        <legend>Upload images</legend>
        <div dojoType="modules.Upload" uploadUrl="/products/?module=ImageImport&service=upload&eid={$eid}"></div>

        <div id="unpublishedImages"></div>
    </fieldset>
    <fieldset>
        <legend>Import image</legend>
        <form action="/products/?module=ImageImport&service=lookIn"
              method="post" id="image_import_search">
            <input id="eid" type="hidden" name="eid" value="{$eid}" />
            <p>IDer skal være separert med komma (123,452,169 etc)</p>
            <ul>
                <li>
                    <label for="products_input">Produkter</label>
                    <input type="text" name="products" id="products_input" />
                </li>
                <li>
                    <label for="articles_input">Artikler</label>
                    <input type="text" name="articles" id="articles_input" />
                </li>
                <li>
                    <input type="submit" value="Hent" id="image_import_submit" /
>
                </li>
            </ul>
        </form>
        <div id="image_import_result"></div>
    </fieldset>
</div>
