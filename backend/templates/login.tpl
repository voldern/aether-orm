{if $logout === true}
<div class="notice info">
	Du er nå logget ut.
</div>
{/if}

{if isset($message)}
    <div class="notice info">
    {if $message == 'password_changed'}
       Passordet ditt er nå endret.
    {elseif $message == 'onetime'}
       Du skal motta et engangspassord på SMS innen fem minutter.
    {/if}
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

<div class="grid four">
<form action="{$aether.options.loginURL}" method="post" class="login">
	{if $error == 'login_required' && isset($referer)}
    	<input type="hidden" name="referer" value="http://{$aether.domain}{$aether.base}login?referer={$referer}" />
    {else}
	    <input type="hidden" name="referer" value="http://{$aether.domain}{$aether.base}login" />
    {/if}
    <p class="clearfix"><label for="email">Email:</label> <input type="text" id="email" name="email" /></p>
    <p class="clearfix"><label for="password">Password:</label> <input type="password" id="password" name="password" /></p>
    <p class="clearfix"><button class="login" type="submit">Log in</button></p>
</form>
</div>

<p>Har du glemt passordet ditt? Du kan få tilsendt et engangspassord
på sms <a href="/password/onetime">her</a>.</p>