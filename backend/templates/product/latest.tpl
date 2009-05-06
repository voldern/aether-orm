<h2>Latest added</h2>
<ul>
{foreach from=$works item=work}
    <li><a href="/products/{$work.id}" class="hideable">{$work.title}</a>
        <span>[{$work.created}]</span>
        <ul>
        {foreach from=$work.manifestations item=manifestation}
            <li>
                {$manifestation.title}
                <span>[{$work.created}]</span>
            </li>
        {/foreach}
        </ul>
    </li>
{/foreach}
</ul>
