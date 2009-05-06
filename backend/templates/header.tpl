<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="nb" xml:lang="nb">
<head>
    <title>{$title}</title>
    <link rel="stylesheet" href="/css/style.css" type="text/css" />
    <script type="text/javascript" 
        src="http://ajax.googleapis.com/ajax/libs/dojo/1.3.0/dojo/dojo.xd.js"></script>
    <script type="text/javascript" src="/js/products.js"></script>
</head>
<body>
<div id="header">
    <ul id="menu">
        <li><a href="/"{if $aether.options.selectedSection == 'home'} class="selected"{/if}>Home</a></li>
        <li><a href="/products"{if $aether.options.selectedSection == 'products'} class="selected"{/if}>Products</a></li>
        <li><a href="/manufacturers"{if $aether.options.selectedSection == 'manufacturers'} class="selected"{/if}>Manufacturers</a></li>
        <li><a href="/organizations"{if $aether.options.selectedSection == 'organizations'} class="selected"{/if}>Organizations</a></li>
    </ul>
</div>
<div id="content" class="clearfix">
