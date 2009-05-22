

<form action="{$aether.options.loginURL}?action=setPassword&authId={$authId}&referer=http://{$aether.domain}{$aether.base}password" method="post">
	<label>New password: <input type="password" name="password" /></label>
    <label>Repeate password: <input type="password" name="password2" /></label>
    <button type="submit">Change</button>
</form>