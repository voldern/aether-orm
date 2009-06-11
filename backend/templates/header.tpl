<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="nb" xml:lang="nb">
<head>
    <title>{$title}</title>
    <link rel="stylesheet" href="/css/style.css" type="text/css" />
    <link rel="stylesheet" href="/js/dojo/dijit/themes/tundra/tundra.css" type="text/css" />
    <script type="text/javascript">
        var djConfig = {
            parseOnLoad: true,
            usePlainJson: true
        };
    </script>
    <script type="text/javascript" src="/js/dojo/1.3.1/dojo/dojo.js"></script>
    <script type="text/javascript" src="/js/dojo/plugd/plugd.js"></script>
    <script type="text/javascript" src="/js/backend.js"></script>
    {foreach from=$pageJavascripts item=js}
    <script type="text/javascript" src="{$js}"></script>
    {/foreach}
</head>
<body class="tundra">
<div id="header">
    <ul id="menu">
        <li><a href="/"{if $aether.options.selectedSection == 'home'} class="selected"{/if}>Home</a></li>
        <li><a href="/products"{if $aether.options.selectedSection == 'products'} class="selected"{/if}>Products</a></li>
        <li><a href="/manufacturers"{if $aether.options.selectedSection == 'manufacturers'} class="selected"{/if}>Manufacturers</a></li>
        <li><a href="/organizations"{if $aether.options.selectedSection == 'organizations'} class="selected"{/if}>Organizations</a></li>
        {if $loggedIn !== true}
	        <li><a href="/login"{if $aether.options.selectedSection == 'login'} class="selected"{/if}>Login</a></li>
        {else}
    	    <li><a href="/logout">Logout</a></li>
        {/if}
    </ul>
    <div class="fRight">
        <form method="get" action="">
            <input type="text" name="q" id="q" autocomplete="off" />
            <input type="submit" value="Search" />
        </form>
        <div dojoType="modules.ProductSearch" id="product_search"></div>
    </div>
</div>
<div id="content" class="clearfix">
