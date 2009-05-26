{if isset($error)}
<div class="notice error">
   {if $error == 'wrong_length'}
   Mobilnummeret må bestå av 10 tegn inkludert landskode.
   {elseif $error == 'wrong_prefix'}
   Den eneste tillate landskoden er for tiden '47'
   {elseif $error == 'system_error'}
   En ukjent systemfeil oppsto, vennligst kontakt support@hardware.no om problemet vedvarer.
   {/if}
</div>
{/if}

<p>For å kunne bli tilsendt et engangspassordt må brukeren din være registerert
med et mobilnummer. <br />Hvis dette ikke er tilfellet ber vi deg kontakte support@hardware.no</p>

<form action"/password/onetime" method="post">
    <label>Mobilnummer: <em>(med landskode)</em> <input type="text" name="cellphone" /></label>
    <button type="submit">Send</button>
</form>