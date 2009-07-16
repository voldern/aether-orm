<div>
    <fieldset>
        <legend>Images</legend>

        <fieldset class="import">
            <legend>Import image</legend>
            <form action="/products/?module=ImageImport&service=lookIn"
                  method="post" id="image_import_search">
                <input id="eid" type="hidden" name="eid" value="{$eid}" />
                <label for="import_search">Freetext Search</label>
                <input id="import_search" type="text" name="query" value="{$query}" />
                <p>Comma seperated list of product- and articleids (123,452,169 etc)</p>
                <ul>
                    <li>
                        <label for="products_input">ProductIds</label>
                        <input type="text" name="products" id="products_input" />
                    </li>
                    <li>
                        <label for="articles_input">ArticleIds</label>
                        <input type="text" name="articles" id="articles_input" />
                    </li>
                </ul>
            </form>
            <form action="/products/?module=ImageImport&service=connect"
                  method="post" id="image_import_result">
                <div id="imageImportResult"></div>
                <input type="hidden" name="eid" value="{$eid}" />
                <input type="hidden" name="selectedIds" value="" />
                <input type="submit" value="Fetch" id="image_import_submit" />
            </form>
        </fieldset>

        <fieldset class="upload">
            <legend>Upload images</legend>
            <div dojoType="modules.Upload" uploadUrl="/products/?module=ImageImport&service=upload&eid={$eid}"></div>

        </fieldset>

        <div class="clearfix"></div>

        <h3>Unpublished Images <span id="textSelectedUnpublished"></span></h3>
        <form action="/products/?module=ImageImport&service=publish"
                method="post" id="image_publish">
            <input type="hidden" name="selectedIds" value="" />
            <input type="text" name="publish" id="datePublish" />
            <button id="buttonPublish">Publish</button>
        </form>
        <form action="/products/?module=ImageImport&service=delete"
                method="post" id="image_delete">
            <input type="hidden" name="eid" value="{$eid}" />
            <input type="hidden" name="selectedIds" value="" />
            <button id="buttonDelete">Delete</button>
        </form>
        <form action="/products/?module=ImageEditor&service=showEditor"
                method="post" id="image_edit">
            <input type="hidden" name="eid" value="{$eid}" />
            <input type="hidden" name="selectedIds" value="" />
            <button id="buttonDelete">Edit</button>
        </form>
        <div id="editImages"></div>
        <div id="unpublishedImages"></div>

        <h3>Published Images <span id="textSelectedUnpublished"></span></h3>
        <form action="/products/?module=ImageImport&service=depublish"
                method="post" id="image_depublish">
            <input type="hidden" name="selectedIds" value="" />
            <input type="hidden" name="depublish" value="1" />
            <button id="buttonPublish">Depublish</button>
        </form>
        <div id="publishedImages"></div>
    </fieldset>
</div>
