{if isset($error)}
    <div class="notice error">
    {if $error == 'pwd_min_one_letter'}
        Passordet må bestå av minst en bokstav.
    {elseif $error == 'pwd_min_one_number'}
        Passordet må bestå av minst ett tall.
    {elseif $error == 'pwd_min_seven_chars'}
        Passordet må bestå av minst sju tegn.
    {else}
        En ukjent feil oppsto. Kunne ikke endre passordet.
    {/if}
</div>
{/if}

<form action="{$aether.options.loginURL}?action=setPassword&authId={$authId}&referer=http://{$aether.domain}{$aether.base}password" method="post">
    <label>New password: <input type="password" name="password" /></label>
    <label>Repeate password: <input type="password" name="password2" /></label>
    <button type="submit">Change</button>
</form>
