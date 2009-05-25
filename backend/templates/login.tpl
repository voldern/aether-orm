{if $logout === true}
<div class="notice info">
	Du er nå logget ut.
</div>
{/if}

{if isset($message) and $message == 'password_changed'}
<div class="notice info">
	Passordet ditt er nå endret.
</div>
{/if}

{if not empty($error)}
<div class="notice error">
	{if $error == 'login_required'}
    	Du må være logget inn for å se denne siden.
	{elseif $error == 'wrong_password'}
		Feil passord.
	{elseif $error == 'no_such_user'}
    	Feil brukernavn.
	{else}
		Kunne ikke logge inn, om denne feilen fortsetter vennligst
        kontakt support.
	{/if}
</div>
{/if}

<form action="{$aether.options.loginURL}" method="post">
	{if $error == 'login_required' && isset($referer)}
    	<input type="hidden" name="referer" value="http://{$aether.domain}{$aether.base}login?referer={$referer}" />
    {else}
	    <input type="hidden" name="referer" value="http://{$aether.domain}{$aether.base}login" />
    {/if}
    <label>Email: <input type="text" name="email" /></label>
    <label>Password: <input type="password" name="password" /></label>
    <button type="submit">Log in</button>
</form>
