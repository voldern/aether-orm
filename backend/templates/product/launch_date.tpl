<div>
    <fieldset>
        <legend>Set launch date</legend>
        <form action="/products/?module=LaunchDate&service=Save&eid={$entityId}" method="post" class="autosaveForm" id="launch_date">
            <ul>
                <li>
                    <label for="period">Period</label>
                    <select name="period" id="period" class="autosave string">
                    {foreach $data.periods as $p}
                        <option{if $p == $data.period} selected="selected"{/if}>{$p}</option>
                    {/foreach}
                    </select>

                    <label for="year">Year</label>
                    <select name="year" id="year" class="autosave string">
                    {foreach $data.years as $y}
                        <option{if $y == $data.year} selected="selected"{/if}>{$y}</option>
                    {/foreach}
                    </select>
                </li>
                <li>
                    <label for="exact_date">Date</label>
                    <input type="text" id="exact_date" name="exact_date" class="autosave string" value="{$data.exactDate}" />
                </li>
            </ul>
        </form>
        <div id="launch_date"></div>
    </fieldset>
</div>

